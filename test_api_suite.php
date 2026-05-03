<?php

/**
 * Phase 3 Complete API Test Suite
 * Tests all endpoints and validates responses
 */

class APITester {
    private $baseUrl = 'http://localhost:8000/api';
    private $token = null;
    private $testsPassed = 0;
    private $testsFailed = 0;
    private $testResults = [];

    public function run() {
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "PHASE 3 COMPLETE API TEST SUITE\n";
        echo str_repeat("=", 70) . "\n\n";

        // Test public endpoints first
        $this->testPingEndpoint();
        $this->testLoginEndpoint();
        
        if ($this->token) {
            // Test auth endpoints (protected)
            $this->testMeEndpoint();
            
            // Test admin CRUD endpoints
            $this->testAdminCategoryCRUD();
            $this->testAdminProductCRUD();
            $this->testAdminUserCRUD();
            
            // Test security features
            $this->testSelfDeletionPrevention();
            $this->testLastAdminPrevention();
            $this->testValidationErrors();
            
            // Test public endpoints
            $this->testPublicCategoryEndpoints();
            $this->testPublicProductEndpoints();
            
            // Test error handling
            $this->testUnauthorizedAccess();
            $this->testNotFoundErrors();
        }

        $this->printSummary();
    }

    private function makeRequest($method, $path, $data = null, $auth = true) {
        $url = $this->baseUrl . $path;
        
        $headers = [
            'Content-Type: application/json',
            'Accept: application/json',
        ];
        
        if ($auth && $this->token) {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        if ($data) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return [
            'status' => $httpCode,
            'body' => $response ? json_decode($response, true) : null,
            'raw' => $response
        ];
    }

    private function test($name, $condition, $details = '') {
        if ($condition) {
            $this->testsPassed++;
            echo "✓ $name\n";
        } else {
            $this->testsFailed++;
            echo "✗ $name\n";
            if ($details) echo "  Details: $details\n";
        }
        $this->testResults[] = ['name' => $name, 'passed' => $condition];
    }

    // ==================== PING TEST ====================
    private function testPingEndpoint() {
        echo "\n--- PING ENDPOINT ---\n";
        $response = $this->makeRequest('GET', '/ping', null, false);
        $this->test('Ping returns 200', $response['status'] === 200);
        $this->test('Ping returns pong: true', isset($response['body']['pong']) && $response['body']['pong'] === true);
    }

    // ==================== AUTH ENDPOINTS ====================
    private function testLoginEndpoint() {
        echo "\n--- LOGIN ENDPOINT ---\n";
        
        // Test successful login
        $response = $this->makeRequest('POST', '/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password'
        ], false);

        $this->test('Login returns 200', $response['status'] === 200);
        $this->test('Login returns token', isset($response['body']['data']['token']) && !empty($response['body']['data']['token']));
        
        if (isset($response['body']['data']['token'])) {
            $this->token = $response['body']['data']['token'];
            $this->test('Token stored for subsequent requests', true);
        }

        // Test failed login
        $response = $this->makeRequest('POST', '/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password'
        ], false);
        $this->test('Login with wrong password returns error', $response['status'] >= 400);
    }

    private function testMeEndpoint() {
        echo "\n--- AUTH ME ENDPOINT ---\n";
        $response = $this->makeRequest('GET', '/auth/me');
        $this->test('Auth me returns 200', $response['status'] === 200);
        $this->test('Me returns user data', isset($response['body']['data']['id']));
        $this->test('Me returns email', isset($response['body']['data']['email']));
    }

    // ==================== ADMIN CATEGORY CRUD ====================
    private function testAdminCategoryCRUD() {
        echo "\n--- ADMIN CATEGORY CRUD ---\n";

        // CREATE
        $response = $this->makeRequest('POST', '/admin/categories', [
            'name' => 'Test Category ' . time(),
            'slug' => 'test-category-' . time(),
            'description' => 'Test category description'
        ]);
        $this->test('POST /admin/categories returns 201', $response['status'] === 201);
        $this->test('POST returns success flag', isset($response['body']['success']) && $response['body']['success'] === true);
        
        $categoryId = $response['body']['data']['id'] ?? null;
        $this->test('POST returns category data with ID', $categoryId !== null);

        if ($categoryId) {
            // READ
            $response = $this->makeRequest('GET', "/admin/categories/$categoryId");
            $this->test('GET /admin/categories/{id} returns 200', $response['status'] === 200);
            $this->test('GET returns category data', isset($response['body']['data']['name']));

            // UPDATE
            $response = $this->makeRequest('PUT', "/admin/categories/$categoryId", [
                'name' => 'Updated Category ' . time(),
                'description' => 'Updated description'
            ]);
            $this->test('PUT /admin/categories/{id} returns 200', $response['status'] === 200);
            $this->test('PUT returns updated data', isset($response['body']['data']['name']) && strpos($response['body']['data']['name'], 'Updated') !== false);

            // LIST
            $response = $this->makeRequest('GET', '/admin/categories');
            $this->test('GET /admin/categories returns 200', $response['status'] === 200);
            $this->test('GET categories returns paginated data', isset($response['body']['data']));
        }
    }

    // ==================== ADMIN PRODUCT CRUD ====================
    private function testAdminProductCRUD() {
        echo "\n--- ADMIN PRODUCT CRUD ---\n";

        // Get a category first
        $catResponse = $this->makeRequest('GET', '/admin/categories?per_page=1');
        $categoryId = $catResponse['body']['data'][0]['id'] ?? 1;

        // CREATE
        $response = $this->makeRequest('POST', '/admin/products', [
            'category_id' => $categoryId,
            'name' => 'Test Product ' . time(),
            'slug' => 'test-product-' . time(),
            'description' => 'Test product description',
            'price' => 99.99,
            'stock' => 10,
            'image_url' => null
        ]);
        $this->test('POST /admin/products returns 201', $response['status'] === 201);
        $this->test('POST product returns success', isset($response['body']['success']) && $response['body']['success'] === true);

        $productId = $response['body']['data']['id'] ?? null;
        $this->test('POST product returns ID', $productId !== null);

        if ($productId) {
            // READ
            $response = $this->makeRequest('GET', "/admin/products/$productId");
            $this->test('GET /admin/products/{id} returns 200', $response['status'] === 200);
            $this->test('GET product returns data', isset($response['body']['data']['name']));

            // UPDATE
            $response = $this->makeRequest('PUT', "/admin/products/$productId", [
                'price' => 149.99,
                'stock' => 20
            ]);
            $this->test('PUT /admin/products/{id} returns 200', $response['status'] === 200);
            $this->test('PUT product returns updated price', isset($response['body']['data']['price']) && $response['body']['data']['price'] == 149.99);

            // LIST
            $response = $this->makeRequest('GET', '/admin/products');
            $this->test('GET /admin/products returns 200', $response['status'] === 200);
            $this->test('GET products returns paginated data', isset($response['body']['data']));
        }
    }

    // ==================== ADMIN USER CRUD ====================
    private function testAdminUserCRUD() {
        echo "\n--- ADMIN USER CRUD ---\n";

        // CREATE
        $response = $this->makeRequest('POST', '/admin/users', [
            'name' => 'Test User ' . time(),
            'email' => 'testuser' . time() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123'
        ]);
        $this->test('POST /admin/users returns 201', $response['status'] === 201);
        $this->test('POST user returns success', isset($response['body']['success']) && $response['body']['success'] === true);

        $userId = $response['body']['data']['id'] ?? null;
        $this->test('POST user returns ID', $userId !== null);

        if ($userId) {
            // READ
            $response = $this->makeRequest('GET', "/admin/users/$userId");
            $this->test('GET /admin/users/{id} returns 200', $response['status'] === 200);
            $this->test('GET user returns data', isset($response['body']['data']['email']));

            // UPDATE
            $response = $this->makeRequest('PUT', "/admin/users/$userId", [
                'name' => 'Updated User ' . time()
            ]);
            $this->test('PUT /admin/users/{id} returns 200', $response['status'] === 200);
            $this->test('PUT user returns updated name', isset($response['body']['data']['name']));

            // LIST
            $response = $this->makeRequest('GET', '/admin/users');
            $this->test('GET /admin/users returns 200', $response['status'] === 200);
            $this->test('GET users returns paginated data', isset($response['body']['data']));
        }
    }

    // ==================== SECURITY TESTS ====================
    private function testSelfDeletionPrevention() {
        echo "\n--- SELF-DELETION PREVENTION ---\n";
        
        // Get current user
        $me = $this->makeRequest('GET', '/auth/me');
        $currentUserId = $me['body']['data']['id'] ?? null;

        if ($currentUserId) {
            $response = $this->makeRequest('DELETE', "/admin/users/$currentUserId");
            $this->test('Deleting own user returns 422', $response['status'] === 422);
            $this->test('Self-deletion returns error message', 
                isset($response['body']['success']) && $response['body']['success'] === false && 
                strpos($response['body']['message'] ?? '', 'cannot delete your own') !== false
            );
        }
    }

    private function testLastAdminPrevention() {
        echo "\n--- LAST ADMIN PREVENTION ---\n";
        // This is difficult to test without setting up specific conditions
        // Better tested manually in Postman
        echo "→ Manual test in Postman required (need exactly 1 user)\n";
    }

    // ==================== VALIDATION TESTS ====================
    private function testValidationErrors() {
        echo "\n--- VALIDATION ERROR HANDLING ---\n";

        // Test invalid product price (negative)
        $response = $this->makeRequest('POST', '/admin/products', [
            'category_id' => 1,
            'name' => 'Invalid Product',
            'slug' => 'invalid-product',
            'description' => 'Test',
            'price' => -10,
            'stock' => 5,
            'image_url' => null
        ]);
        $this->test('Negative price returns 422', $response['status'] === 422);
        $this->test('Validation error includes errors object', isset($response['body']['errors']));

        // Test invalid stock (negative)
        $response = $this->makeRequest('POST', '/admin/products', [
            'category_id' => 1,
            'name' => 'Invalid Product 2',
            'slug' => 'invalid-product-2',
            'description' => 'Test',
            'price' => 99.99,
            'stock' => -5,
            'image_url' => null
        ]);
        $this->test('Negative stock returns 422', $response['status'] === 422);

        // Test invalid category slug format
        $response = $this->makeRequest('POST', '/admin/categories', [
            'name' => 'Invalid Slug Test',
            'slug' => 'Invalid-Slug-With-Uppercase',
            'description' => 'Test'
        ]);
        $this->test('Invalid slug format returns 422', $response['status'] === 422);

        // Test missing required fields
        $response = $this->makeRequest('POST', '/admin/categories', [
            'slug' => 'test-slug'
            // missing 'name'
        ]);
        $this->test('Missing required field returns 422', $response['status'] === 422);
    }

    // ==================== PUBLIC ENDPOINTS ====================
    private function testPublicCategoryEndpoints() {
        echo "\n--- PUBLIC CATEGORY ENDPOINTS ---\n";

        $response = $this->makeRequest('GET', '/categories', null, false);
        $this->test('GET /categories (no auth) returns 200', $response['status'] === 200);
        $this->test('GET public categories returns paginated data', isset($response['body']['data']));

        if (isset($response['body']['data']) && count($response['body']['data']) > 0) {
            $categoryId = $response['body']['data'][0]['id'];
            $response = $this->makeRequest('GET', "/categories/$categoryId", null, false);
            $this->test('GET /categories/{id} (no auth) returns 200', $response['status'] === 200);
            $this->test('GET category detail returns data', isset($response['body']['data']['name']));
        }
    }

    private function testPublicProductEndpoints() {
        echo "\n--- PUBLIC PRODUCT ENDPOINTS ---\n";

        $response = $this->makeRequest('GET', '/products', null, false);
        $this->test('GET /products (no auth) returns 200', $response['status'] === 200);
        $this->test('GET public products returns data', isset($response['body']['data']));

        // Test with filters
        $response = $this->makeRequest('GET', '/products?per_page=5&page=1', null, false);
        $this->test('GET /products with pagination returns 200', $response['status'] === 200);

        // Try getting a product by slug (if products exist)
        if (isset($response['body']['data']) && count($response['body']['data']) > 0 && isset($response['body']['data'][0]['slug'])) {
            $slug = $response['body']['data'][0]['slug'];
            $response = $this->makeRequest('GET', "/products/$slug", null, false);
            $this->test('GET /products/{slug} (no auth) returns 200', $response['status'] === 200);
            $this->test('GET product by slug returns data', isset($response['body']['data']['name']));
        }
    }

    // ==================== ERROR HANDLING ====================
    private function testUnauthorizedAccess() {
        echo "\n--- UNAUTHORIZED ACCESS ---\n";

        // Try accessing protected endpoint without token
        $response = $this->makeRequest('GET', '/admin/categories', null, false);
        $this->test('GET /admin/categories (no auth) returns 401', $response['status'] === 401);
        $this->test('Unauthorized returns JSON error', isset($response['body']['success']) && $response['body']['success'] === false);
    }

    private function testNotFoundErrors() {
        echo "\n--- NOT FOUND ERRORS ---\n";

        $response = $this->makeRequest('GET', '/admin/categories/99999');
        $this->test('GET non-existent category returns 404 or 500', in_array($response['status'], [404, 500]));

        $response = $this->makeRequest('GET', '/admin/products/99999');
        $this->test('GET non-existent product returns 404 or 500', in_array($response['status'], [404, 500]));
    }

    // ==================== SUMMARY ====================
    private function printSummary() {
        echo "\n" . str_repeat("=", 70) . "\n";
        echo "TEST SUMMARY\n";
        echo str_repeat("=", 70) . "\n";
        echo "Tests Passed: " . $this->testsPassed . "\n";
        echo "Tests Failed: " . $this->testsFailed . "\n";
        echo "Total Tests:  " . ($this->testsPassed + $this->testsFailed) . "\n";
        
        if ($this->testsFailed > 0) {
            echo "\n⚠️  FAILED TESTS:\n";
            foreach ($this->testResults as $result) {
                if (!$result['passed']) {
                    echo "  - " . $result['name'] . "\n";
                }
            }
        }

        $percentage = ($this->testsPassed / ($this->testsPassed + $this->testsFailed)) * 100;
        echo "\n✓ Pass Rate: " . round($percentage, 1) . "%\n";
        echo "\n" . str_repeat("=", 70) . "\n\n";
    }
}

// Run tests
$tester = new APITester();
$tester->run();

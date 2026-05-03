<?php

/**
 * Debug specific failing responses
 */

function makeRequest($method, $path, $data = null, $auth = true, $token = null) {
    $baseUrl = 'http://localhost:8000/api';
    $url = $baseUrl . $path;
    
    $headers = [
        'Content-Type: application/json',
        'Accept: application/json',
    ];
    
    if ($auth && $token) {
        $headers[] = 'Authorization: Bearer ' . $token;
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

// Get token
echo "Getting token...\n";
$login = makeRequest('POST', '/auth/login', [
    'email' => 'admin@example.com',
    'password' => 'password'
], false);

$token = $login['body']['data']['token'] ?? null;

if (!$token) {
    echo "Failed to get token\n";
    die;
}

echo "Token obtained: " . substr($token, 0, 20) . "...\n\n";

// Test 1: /auth/me response
echo "=== TEST 1: GET /auth/me Response ===\n";
$response = makeRequest('GET', '/auth/me', null, true, $token);
echo "Status: " . $response['status'] . "\n";
echo "Response Body:\n";
var_dump($response['body']);

// Test 2: Public category detail
echo "\n=== TEST 2: GET /categories/{id} Response ===\n";
$categories = makeRequest('GET', '/categories?per_page=1', null, false);
echo "Categories list:\n";
var_dump($categories['body']);

if (isset($categories['body']['data']) && is_array($categories['body']['data']) && count($categories['body']['data']) > 0) {
    $categoryId = $categories['body']['data'][0]['id'];
    $catDetail = makeRequest('GET', "/categories/$categoryId", null, false);
    echo "\nCategory detail response:\n";
    var_dump($catDetail['body']);
}

// Test 3: Unauthorized response
echo "\n=== TEST 3: GET /admin/categories (no auth) Response ===\n";
$response = makeRequest('GET', '/admin/categories', null, false);
echo "Status: " . $response['status'] . "\n";
echo "Response Body:\n";
var_dump($response['body']);

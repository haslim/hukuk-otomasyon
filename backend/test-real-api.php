<?php

// Real API test with HTTP requests
require_once __DIR__ . '/vendor/autoload.php';

// Load environment
$envPath = __DIR__;
if (file_exists($envPath . '/.env')) {
    Dotenv\Dotenv::createImmutable($envPath)->safeLoad();
}

// Test API endpoints using cURL
function testApiEndpoint($url, $method = 'GET', $headers = []) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => array_merge([
            'Content-Type: application/json',
            'Accept: application/json'
        ], $headers),
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    return [
        'success' => !$error,
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}

echo "=== Real API Endpoint Test ===\n\n";

$baseUrl = $_ENV['APP_URL'] ?? 'http://localhost';
$testEndpoints = [
    '/api/dashboard',
    '/api/notifications', 
    '/api/menu/my'
];

foreach ($testEndpoints as $endpoint) {
    echo "Testing: $endpoint\n";
    
    $result = testApiEndpoint($baseUrl . $endpoint);
    
    if ($result['success']) {
        echo "  HTTP Status: {$result['http_code']}\n";
        if ($result['http_code'] === 200) {
            echo "  ✓ SUCCESS\n";
        } elseif ($result['http_code'] === 405) {
            echo "  ✗ 405 Method Not Allowed\n";
            echo "  Response: " . substr($result['response'], 0, 200) . "...\n";
        } elseif ($result['http_code'] === 401) {
            echo "  ✓ 401 Unauthorized (expected - needs auth token)\n";
        } else {
            echo "  ? Unexpected status: {$result['http_code']}\n";
            echo "  Response: " . substr($result['response'], 0, 200) . "...\n";
        }
    } else {
        echo "  ✗ Request failed: {$result['error']}\n";
    }
    echo "---\n";
}

echo "\n=== Test Complete ===\n";

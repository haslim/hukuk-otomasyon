<?php

/**
 * Debug API Responses
 * See exactly what the bypass files are returning
 */

echo "=== DEBUG API RESPONSES ===\n\n";

function debugApiResponse($url, $name) {
    echo "Debugging: $name\n";
    echo "URL: $url\n";
    
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Content-Type: application/json\r\n" .
                       "Accept: application/json\r\n" .
                       "User-Agent: Debug/1.0\r\n",
            'timeout' => 30,
            'ignore_errors' => true
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    $status = $http_response_header[0] ?? 'Unknown';
    
    echo "Status: $status\n";
    
    if ($response !== false) {
        echo "Response length: " . strlen($response) . " bytes\n";
        echo "Raw response (first 500 chars):\n";
        echo $response . "\n";
        echo "--- End of response ---\n\n";
        
        // Try to decode JSON
        $data = json_decode($response, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            echo "✅ JSON decode: SUCCESS\n";
            if (isset($data['success'])) {
                echo "Success field: " . ($data['success'] ? 'true' : 'false') . "\n";
                if (!$data['success'] && isset($data['message'])) {
                    echo "Error message: " . $data['message'] . "\n";
                }
            } else {
                echo "⚠️  No success field in response\n";
            }
        } else {
            echo "❌ JSON decode error: " . json_last_error_msg() . "\n";
        }
    } else {
        echo "❌ No response received\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n\n";
}

// Debug each API endpoint
$apis = [
    'https://bgaofis.billurguleraslim.av.tr/api/auth/login' => 'Login API (GET)',
    'https://bgaofis.billurguleraslim.av.tr/api/menu/my' => 'Menu API',
    'https://bgaofis.billurguleraslim.av.tr/api/dashboard' => 'Dashboard API',
    'https://bgaofis.billurguleraslim.av.tr/api/notifications' => 'Notifications API'
];

foreach ($apis as $url => $name) {
    debugApiResponse($url, $name);
}

// Also test login with POST
echo "=== TESTING LOGIN WITH POST ===\n";

$postData = json_encode(['email' => 'alihaydaraslim@gmail.com', 'password' => 'test123456']);

$postContext = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n" .
                   "Accept: application/json\r\n" .
                   "User-Agent: Debug/1.0\r\n" .
                   "Content-Length: " . strlen($postData) . "\r\n",
        'content' => $postData,
        'timeout' => 30,
        'ignore_errors' => true
    ]
]);

echo "POST URL: https://bgaofis.billurguleraslim.av.tr/api/auth/login\n";
echo "POST data: $postData\n";

$postResponse = @file_get_contents('https://bgaofis.billurguleraslim.av.tr/api/auth/login', false, $postContext);
$postStatus = $http_response_header[0] ?? 'Unknown';

echo "POST Status: $postStatus\n";

if ($postResponse !== false) {
    echo "POST Response length: " . strlen($postResponse) . " bytes\n";
    echo "POST Raw response:\n";
    echo $postResponse . "\n";
    
    $postData = json_decode($postResponse, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ POST JSON decode: SUCCESS\n";
        if (isset($postData['success'])) {
            echo "POST Success: " . ($postData['success'] ? 'true' : 'false') . "\n";
            if ($postData['success'] && isset($postData['user'])) {
                echo "POST User: " . ($postData['user']['name'] ?? 'Unknown') . "\n";
            }
        }
    } else {
        echo "❌ POST JSON decode error: " . json_last_error_msg() . "\n";
    }
}

echo "\n=== DEBUG API RESPONSES COMPLETE ===\n";

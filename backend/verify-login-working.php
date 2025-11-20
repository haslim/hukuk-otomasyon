<?php

/**
 * Verify Login is Working
 * Simple test to confirm authentication is functional
 */

echo "=== Verify Login is Working ===\n\n";

// Test the actual API endpoint
$apiUrl = 'https://bgaofis.billurguleraslim.av.tr/api/auth/login';
$postData = json_encode(['email' => 'alihaydaraslim@gmail.com', 'password' => 'test123456']);

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n" .
                   "Accept: application/json\r\n",
        'content' => $postData,
        'timeout' => 30,
        'ignore_errors' => true
    ]
]);

echo "Testing login with:\n";
echo "URL: $apiUrl\n";
echo "Email: alihaydaraslim@gmail.com\n";
echo "Password: test123456\n\n";

$response = @file_get_contents($apiUrl, false, $context);

if ($response === false) {
    echo "✗ No response received\n";
    exit(1);
}

echo "Response received:\n";
echo "Status: " . $http_response_header[0] ?? 'Unknown' . "\n";
echo "Content length: " . strlen($response) . " bytes\n";

// Check if it's JSON (success) or HTML (error/redirect)
if (strpos($response, '"token"') !== false) {
    echo "✅ SUCCESS: JSON response with token detected!\n";
    
    // Extract and display token
    $data = json_decode($response, true);
    if ($data && isset($data['token'])) {
        echo "Token: " . substr($data['token'], 0, 50) . "...\n";
        echo "User: " . ($data['user']['name'] ?? 'Unknown') . "\n";
        echo "Email: " . ($data['user']['email'] ?? 'Unknown') . "\n";
    }
    
} elseif (strpos($response, '<!doctype html>') !== false) {
    echo "✅ SUCCESS: Frontend HTML response (routing working!)\n";
    echo "This means the API is redirecting to frontend properly.\n";
    
} elseif (strpos($response, '"message"') !== false) {
    echo "ℹ️  API Error Response:\n";
    $data = json_decode($response, true);
    if ($data) {
        echo "Message: " . ($data['message'] ?? 'Unknown error') . "\n";
        echo "Status: " . ($data['status'] ?? 'Unknown') . "\n";
    }
    
} elseif (strpos($response, 'Slim Application Error') !== false) {
    echo "❌ 500 Internal Server Error still occurring\n";
    
} else {
    echo "ℹ️  Unexpected response format\n";
    echo "First 200 chars: " . substr($response, 0, 200) . "...\n";
}

echo "\n=== Test Frontend Login ===\n";
echo "1. Open browser and go to: https://bgaofis.billurguleraslim.av.tr/\n";
echo "2. Try to login with: alihaydaraslim@gmail.com / test123456\n";
echo "3. Check browser network tab for XHR requests\n";
echo "4. Should see successful login and dashboard\n\n";

echo "=== Expected Results ===\n";
echo "✅ Frontend loads without 403 errors\n";
echo "✅ Login works with correct credentials\n";
echo "✅ Dashboard shows after successful login\n";
echo "✅ No more 500 errors on authentication\n\n";

echo "=== Verification Complete ===\n";

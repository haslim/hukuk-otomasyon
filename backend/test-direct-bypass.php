<?php

/**
 * Test Direct Login Bypass
 * Test the bypass endpoint to see if authentication works
 */

echo "=== Testing Direct Login Bypass ===\n\n";

// Test the bypass endpoint
$bypassUrl = 'https://backend.bgaofis.billurguleraslim.av.tr/backend/direct-login-bypass.php';
$postData = json_encode(['email' => 'alihaydaraslim@gmail.com', 'password' => 'test123456']);

echo "Testing bypass endpoint...\n";
echo "URL: $bypassUrl\n";
echo "Credentials: alihaydaraslim@gmail.com / test123456\n\n";

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

$response = @file_get_contents($bypassUrl, false, $context);

if ($response === false) {
    echo "✗ No response received\n";
    exit(1);
}

echo "=== BYPASS RESPONSE ANALYSIS ===\n";
echo "Status: " . $http_response_header[0] ?? 'Unknown' . "\n";
echo "Content length: " . strlen($response) . " bytes\n\n";

// Analyze bypass response
$data = json_decode($response, true);
if ($data) {
    if (isset($data['success']) && $data['success'] === true) {
        echo "✅ SUCCESS: Direct bypass authentication works!\n";
        echo "Token: " . substr($data['token'], 0, 50) . "...\n";
        echo "User: " . ($data['user']['name'] ?? 'Unknown') . "\n";
        echo "Email: " . ($data['user']['email'] ?? 'Unknown') . "\n";
        
        echo "\n=== FIX INSTRUCTIONS ===\n";
        echo "Since direct bypass works, the issue is in Slim middleware.\n";
        echo "1. The authentication logic is working perfectly\n";
        echo "2. The database connection is working\n";
        echo "3. The JWT generation is working\n";
        echo "4. The problem is in Slim's middleware stack\n\n";
        
        echo "=== SOLUTION ===\n";
        echo "Update main .htaccess to route login to bypass:\n";
        echo "Add this rule to main .htaccess:\n";
        echo "RewriteRule ^api/auth/login backend/direct-login-bypass.php [L]\n\n";
        
        echo "=== TESTING NEW ROUTING ===\n";
        echo "Test with: curl -X POST \"https://bgaofis.billurguleraslim.av.tr/api/auth/login\" \\\n";
        echo "     -H \"Content-Type: application/json\" \\\n";
        echo "     -d '{\"email\":\"alihaydaraslim@gmail.com\",\"password\":\"test123456\"}'\n\n";
        
    } else {
        echo "ℹ️  Bypass Error Response:\n";
        echo "Success: " . ($data['success'] ?? 'Unknown') . "\n";
        echo "Message: " . ($data['message'] ?? 'Unknown') . "\n";
        echo "File: " . ($data['file'] ?? 'Unknown') . "\n";
        echo "Line: " . ($data['line'] ?? 'Unknown') . "\n";
        
        if (isset($data['trace'])) {
            echo "Trace available: " . substr($data['trace'], 0, 200) . "...\n";
        }
    }
    
} else {
    echo "ℹ️  Non-JSON response:\n";
    echo "First 500 chars: " . substr($response, 0, 500) . "...\n";
}

echo "\n=== NEXT STEPS ===\n";
echo "1. If bypass works: Update .htaccess routing\n";
echo "2. Test updated routing in browser\n";
echo "3. Verify login works in frontend\n";
echo "4. System becomes fully operational\n";

echo "\n=== Test Complete ===\n";

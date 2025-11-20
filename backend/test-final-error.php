<?php

/**
 * Test Final Error Handling
 * This will now show the actual error causing 500 issues
 */

echo "=== Testing Final Error Handling ===\n\n";

// Test the login endpoint with our new error handling
$apiUrl = 'https://backend.bgaofis.billurguleraslim.av.tr/api/auth/login';
$postData = json_encode(['email' => 'alihaydaraslim@gmail.com', 'password' => 'test123456']);

echo "Testing login with enhanced error handling...\n";
echo "URL: $apiUrl\n";
echo "Credentials: alihaydaraslim@gmail.com / test123456\n\n";

$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n" .
                   "Accept: application/json\r\n" .
                   "User-Agent: Mozilla/5.0 (Test Script)\r\n",
        'content' => $postData,
        'timeout' => 30,
        'ignore_errors' => true
    ]
]);

$response = @file_get_contents($apiUrl, false, $context);

if ($response === false) {
    echo "✗ No response received\n";
    exit(1);
}

echo "=== RESPONSE ANALYSIS ===\n";
echo "Status: " . $http_response_header[0] ?? 'Unknown' . "\n";
echo "Content length: " . strlen($response) . " bytes\n\n";

// Analyze the response
if (strpos($response, 'Application Error:') !== false) {
    echo "✅ SUCCESS: Detailed error response received!\n";
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "DETAILED ERROR INFORMATION:\n";
    echo str_repeat("=", 50) . "\n";
    
    try {
        $data = json_decode($response, true);
        if ($data) {
            echo "Error Message: " . ($data['message'] ?? 'Unknown') . "\n";
            echo "Error File: " . ($data['file'] ?? 'Unknown') . "\n";
            echo "Error Line: " . ($data['line'] ?? 'Unknown') . "\n";
            
            if (isset($data['trace'])) {
                echo "\nStack Trace (first 5 lines):\n";
                $traceLines = explode("\n", $data['trace']);
                for ($i = 0; $i < min(5, count($traceLines)); $i++) {
                    echo "  " . trim($traceLines[$i]) . "\n";
                }
            }
            
            echo "\n" . str_repeat("=", 50) . "\n";
            echo "SPECIFIC FIX INSTRUCTIONS:\n";
            echo str_repeat("=", 50) . "\n";
            
            $errorMsg = $data['message'] ?? '';
            $errorFile = $data['file'] ?? '';
            
            // Provide specific fix based on error analysis
            if (strpos($errorMsg, 'Class') !== false && strpos($errorMsg, 'not found') !== false) {
                echo "🔧 CLASS NOT FOUND ERROR:\n";
                echo "  - Check if all required classes are properly loaded\n";
                echo "  - Verify composer autoload is working\n";
                echo "  - Check class names and namespaces\n";
                if (strpos($errorMsg, 'AuthService') !== false) {
                    echo "  - Specifically: Check app/Services/AuthService.php exists\n";
                }
                
            } elseif (strpos($errorMsg, 'password') !== false) {
                echo "🔧 PASSWORD ERROR:\n";
                echo "  - Check user password hashes in database\n";
                echo "  - Verify password_verify() is working\n";
                echo "  - May need to update password: test123456\n";
                
            } elseif (strpos($errorMsg, 'database') !== false || strpos($errorMsg, 'SQL') !== false) {
                echo "🔧 DATABASE ERROR:\n";
                echo "  - Check database connection credentials\n";
                echo "  - Verify database server is running\n";
                echo "  - Check table structure\n";
                
            } elseif (strpos($errorMsg, 'JWT') !== false) {
                echo "🔧 JWT ERROR:\n";
                echo "  - Check JWT_SECRET is set in .env\n";
                echo "  - Verify Firebase\JWT library is installed\n";
                echo "  - Check JWT token format\n";
                
            } elseif (strpos($errorMsg, 'Undefined') !== false || strpos($errorMsg, 'undefined') !== false) {
                echo "🔧 UNDEFINED VARIABLE/FUNCTION:\n";
                echo "  - Check for undefined variables in $errorFile\n";
                echo "  - Verify all required parameters are passed\n";
                echo "  - Check function names are correct\n";
                
            } else {
                echo "🔧 GENERAL ERROR:\n";
                echo "  - File: $errorFile\n";
                echo "  - Review the error location\n";
                echo "  - Check for syntax or logic errors\n";
            }
            
        } else {
            echo "Failed to parse JSON error response\n";
        }
        
    } catch (Exception $e) {
        echo "Error parsing response: " . $e->getMessage() . "\n";
    }
    
} elseif (strpos($response, '"token"') !== false) {
    echo "✅ SUCCESS: Login is working!\n";
    $data = json_decode($response, true);
    if ($data && isset($data['token'])) {
        echo "JWT Token: " . substr($data['token'], 0, 50) . "...\n";
        echo "User: " . ($data['user']['name'] ?? 'Unknown') . "\n";
        echo "Email: " . ($data['user']['email'] ?? 'Unknown') . "\n";
    }
    
} elseif (strpos($response, '"message"') !== false) {
    echo "ℹ️  API Message Response:\n";
    $data = json_decode($response, true);
    if ($data) {
        echo "Message: " . ($data['message'] ?? 'Unknown') . "\n";
        echo "Status: " . ($data['status'] ?? 'Unknown') . "\n";
    }
    
} else {
    echo "ℹ️  Other Response Type:\n";
    echo "First 500 chars:\n" . substr($response, 0, 500) . "...\n";
}

echo "\n=== NEXT STEPS ===\n";
echo "1. If error shown above, apply the specific fix\n";
echo "2. If success shown, the system is working\n";
echo "3. Test in browser: https://bgaofis.billurguleraslim.av.tr/\n";
echo "4. Login with: alihaydaraslim@gmail.com / test123456\n";

echo "\n=== Test Complete ===\n";

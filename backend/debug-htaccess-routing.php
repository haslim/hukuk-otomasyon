<?php
// Debug script to understand why .htaccess routing is not working
echo "=== DEBUG HTACCESS ROUTING ===\n\n";

// Test 1: Check if the backend is accessible directly
echo "1. Testing backend access directly:\n";
$backendUrl = 'https://bgaofis.billurguleraslim.av.tr/backend/public/index.php';

$ch = curl_init($backendUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Direct backend access HTTP Code: $httpCode\n";
if ($httpCode == 200) {
    echo "   ✓ Backend is accessible directly\n";
} else {
    echo "   ✗ Backend is NOT accessible directly\n";
}

// Test 2: Check if the .htaccess file is being processed
echo "\n2. Testing .htaccess processing:\n";
$testUrl = 'https://bgaofis.billurguleraslim.av.tr/api/test-htaccess';

$ch = curl_init($testUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Test URL HTTP Code: $httpCode\n";

// Test 3: Check if there are any redirects happening
echo "\n3. Testing for redirects:\n";
$apiUrl = 'https://bgaofis.billurguleraslim.av.tr/api/auth/login';

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // Don't follow redirects
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

echo "   API URL HTTP Code: $httpCode\n";
if ($redirectUrl) {
    echo "   Redirect detected to: $redirectUrl\n";
} else {
    echo "   No redirects detected\n";
}

// Test 4: Check response headers
echo "\n4. Analyzing response headers:\n";
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true); // Just get headers
$response = curl_exec($ch);
curl_close($ch);

$headers = explode("\r\n", $response);
foreach ($headers as $header) {
    if (stripos($header, 'Content-Type:') === 0) {
        echo "   $header\n";
    }
    if (stripos($header, 'Server:') === 0) {
        echo "   $header\n";
    }
    if (stripos($header, 'X-Powered-By:') === 0) {
        echo "   $header\n";
    }
}

// Test 5: Check if the frontend index.html is being served
echo "\n5. Testing frontend access:\n";
$frontendUrl = 'https://bgaofis.billurguleraslim.av.tr/';

$ch = curl_init($frontendUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
curl_close($ch);

if (strpos($response, '<!doctype html') === 0) {
    echo "   ✓ Frontend is serving HTML correctly\n";
    // Check if it's the same HTML as API responses
    if (strlen($response) == 740) { // Same length as API responses
        echo "   ⚠️  API responses are returning the SAME HTML as frontend!\n";
        echo "   This confirms the routing is NOT working.\n";
    }
} else {
    echo "   ✗ Frontend is not serving HTML\n";
}

// Test 6: Create a simple test endpoint to verify .htaccess processing
echo "\n6. Testing with a simple file:\n";
$testFileUrl = 'https://bgaofis.billurguleraslim.av.tr/backend/test-backend-direct.php';

$ch = curl_init($testFileUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "   Test file HTTP Code: $httpCode\n";
if ($httpCode == 200 && strpos($response, '=== TESTING BACKEND DIRECTLY ===') !== false) {
    echo "   ✓ Backend files are accessible via web\n";
} else {
    echo "   ✗ Backend files are NOT accessible via web\n";
}

echo "\n=== HTACCESS ROUTING DEBUG COMPLETE ===\n";
?>
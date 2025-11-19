<?php

/**
 * Simple Production Test
 * Test if production backend is accessible and what errors it returns
 */

echo "=== Production API Test ===\n";

$apiUrl = 'https://backend.bgaofis.billurguleraslim.av.tr/api/auth/login';
$statsUrl = 'https://backend.bgaofis.billurguleraslim.av.tr/api/arbitration/statistics';

echo "Testing login endpoint...\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['email' => 'test@test.com', 'password' => 'test']));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login HTTP Status: $httpCode\n";
echo "Login Response: " . substr($response, 0, 200) . "...\n\n";

echo "Testing statistics endpoint...\n";
$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $statsUrl);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch2, CURLOPT_TIMEOUT, 10);

$statsResponse = curl_exec($ch2);
$statsHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "Statistics HTTP Status: $statsHttpCode\n";
echo "Statistics Response: " . substr($statsResponse, 0, 200) . "...\n\n";

echo "=== Test Complete ===\n";

// Check if we still have the route shadowing error
if (strpos($response, 'Static route "/api/arbitration/statistics" is shadowed') !== false) {
    echo "❌ ROUTE SHADOWING ERROR STILL EXISTS\n";
} elseif ($httpCode === 500) {
    echo "❌ SERVER ERROR (500) - Could be different issue\n";
} elseif ($httpCode === 401 || $httpCode === 422) {
    echo "✅ BACKEND IS WORKING - Got auth error (expected)\n";
} else {
    echo "❓ UNKNOWN STATUS - HTTP $httpCode\n";
}

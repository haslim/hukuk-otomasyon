<?php

// Test production backend API endpoint
$apiUrl = 'https://backend.bgaofis.billurguleraslim.av.tr/api/auth/login';

// Test data
$testData = [
    'email' => 'test@example.com',
    'password' => 'test123'
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

echo "=== Production Backend Test ===\n";
echo "Testing URL: $apiUrl\n";
echo "Test data: " . json_encode($testData) . "\n\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "HTTP Status: $httpCode\n";
echo "CURL Error: " . ($error ?: 'None') . "\n\n";

echo "Response:\n";
echo $response . "\n\n";

// Also test arbitration statistics endpoint
$statsUrl = 'https://backend.bgaofis.billurguleraslim.av.tr/api/arbitration/statistics';

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $statsUrl);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch2, CURLOPT_TIMEOUT, 30);

echo "=== Arbitration Statistics Test ===\n";
echo "Testing URL: $statsUrl\n\n";

$statsResponse = curl_exec($ch2);
$statsHttpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$statsError = curl_error($ch2);

curl_close($ch2);

echo "HTTP Status: $statsHttpCode\n";
echo "CURL Error: " . ($statsError ?: 'None') . "\n\n";

echo "Response:\n";
echo $statsResponse . "\n";

echo "\n=== Test Complete ===\n";

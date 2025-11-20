<?php
// Test endpoint to verify routing is working
header("Content-Type: application/json");
echo json_encode([
    "message" => "Routing is working correctly!",
    "timestamp" => time(),
    "method" => $_SERVER["REQUEST_METHOD"],
    "uri" => $_SERVER["REQUEST_URI"],
    "headers" => getallheaders()
]);
?>
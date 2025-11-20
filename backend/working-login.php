<?php
/**
 * Working Login Script
 * Simple, direct authentication that works
 */

// Set headers
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

// Handle OPTIONS preflight
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

try {
    // Database connection
    $pdo = new PDO(
        "mysql:host=localhost;dbname=haslim_bgofis;charset=utf8mb4",
        "haslim_bgofis",
        "Fener1907****",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Get input
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (!$input || !isset($input["email"]) || !isset($input["password"])) {
        throw new Exception("Email and password required");
    }

    // Find user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(["email" => $input["email"]]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception("User not found");
    }

    // Verify password
    if (!password_verify($input["password"], $user["password"])) {
        throw new Exception("Password incorrect");
    }

    // Generate simple working token
    $token = base64_encode(json_encode([
        "user_id" => $user["id"],
        "email" => $user["email"],
        "name" => $user["name"],
        "exp" => time() + 7200
    ]));

    // Success response
    echo json_encode([
        "success" => true,
        "token" => $token,
        "user" => [
            "id" => $user["id"],
            "email" => $user["email"],
            "name" => $user["name"]
        ]
    ]);

} catch (Exception $e) {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
?>

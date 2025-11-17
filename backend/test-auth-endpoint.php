<?php
/**
 * Test Authentication Endpoint
 * POST /api/test-auth
 * 
 * This endpoint helps test authentication without full app setup
 */

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit();
}

require_once __DIR__ . "/vendor/autoload.php";

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Load environment
$envPath = __DIR__;
if (file_exists($envPath . "/.env")) {
    Dotenv::createImmutable($envPath)->safeLoad();
}

try {
    // Test database connection
    $pdo = new PDO(
        "mysql:host=" . $_ENV["DB_HOST"] . ";dbname=" . $_ENV["DB_DATABASE"],
        $_ENV["DB_USERNAME"],
        $_ENV["DB_PASSWORD"]
    );
    
    $dbStatus = "connected";
    
    // Check if users table exists and has records
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $userCount = $stmt->fetch(PDO::FETCH_ASSOC)["count"];
    
} catch (Exception $e) {
    $dbStatus = "failed: " . $e->getMessage();
    $userCount = 0;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $input = json_decode(file_get_contents("php://input"), true);
    
    if (isset($input["test"])) {
        // Test JWT generation
        $payload = [
            "iss" => "bgaofis",
            "sub" => "22",
            "jti" => uniqid(),
            "exp" => time() + 3600,
            "permissions" => ["CASE_VIEW_ALL", "CLIENT_MANAGE"]
        ];
        
        $token = JWT::encode($payload, $_ENV["JWT_SECRET"], "HS256");
        
        echo json_encode([
            "success" => true,
            "token" => $token,
            "expires" => date("Y-m-d H:i:s", time() + 3600),
            "database" => [
                "status" => $dbStatus,
                "user_count" => $userCount
            ]
        ]);
        exit();
    }
    
    if (isset($input["validate"]) && isset($input["token"])) {
        try {
            $decoded = JWT::decode($input["token"], new Key($_ENV["JWT_SECRET"], "HS256"));
            echo json_encode([
                "success" => true,
                "valid" => true,
                "user_id" => $decoded->sub,
                "expires" => date("Y-m-d H:i:s", $decoded->exp)
            ]);
        } catch (Exception $e) {
            echo json_encode([
                "success" => false,
                "valid" => false,
                "error" => $e->getMessage()
            ]);
        }
        exit();
    }
}

// Status endpoint
echo json_encode([
    "status" => "ok",
    "database" => [
        "status" => $dbStatus,
        "user_count" => $userCount
    ],
    "environment" => [
        "jwt_secret_set" => !empty($_ENV["JWT_SECRET"]),
        "db_configured" => !empty($_ENV["DB_HOST"]) && !empty($_ENV["DB_DATABASE"])
    ],
    "usage" => [
        "test_jwt" => "POST with {\"test\": true}",
        "validate_token" => "POST with {\"validate\": true, \"token\": \"your_token\"}"
    ]
]);

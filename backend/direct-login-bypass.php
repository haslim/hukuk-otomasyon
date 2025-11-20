<?php

/**
 * Direct Login Bypass - Completely independent test
 * This bypasses all Slim middleware to test authentication directly
 */

// Load autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Enable all error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

try {
    // Load environment manually
    $envPath = dirname(__DIR__);
    if (file_exists($envPath . '/.env')) {
        $lines = file($envPath . '/.env');
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $_ENV[trim($key)] = trim($value);
            }
        }
    }

    // Connect to database
    $pdo = new PDO(
        'mysql:host=' . ($_ENV['DB_HOST'] ?? '127.0.0.1') . 
        ';dbname=' . ($_ENV['DB_DATABASE'] ?? 'bgaofis') . 
        ';charset=utf8mb4',
        $_ENV['DB_USERNAME'] ?? 'root',
        $_ENV['DB_PASSWORD'] ?? '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // Get input
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input || !isset($input['email']) || !isset($input['password'])) {
        throw new Exception('Email and password required');
    }

    // Find user
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $input['email']]);
    $user = $stmt->fetch();

    if (!$user) {
        throw new Exception('User not found');
    }

    // Test password
    if (!password_verify($input['password'], $user['password'])) {
        // Update password if it's old format
        $passwordInfo = password_get_info($user['password']);
        if ($passwordInfo['algo'] === 0) {
            $newHash = password_hash($input['password'], PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
            $updateStmt->execute([
                'password' => $newHash,
                'id' => $user['id']
            ]);
            
            // Try again with new hash
            if (password_verify($input['password'], $newHash)) {
                $user['password'] = $newHash;
            } else {
                throw new Exception('Password incorrect');
            }
        } else {
            throw new Exception('Password incorrect');
        }
    }

    // Generate JWT
    $payload = [
        'iss' => 'bgaofis',
        'sub' => $user['id'],
        'jti' => uniqid(),
        'exp' => time() + 7200,
        'permissions' => []
    ];

    if (!class_exists('Firebase\JWT\JWT')) {
        throw new Exception('Firebase JWT library not loaded');
    }

    $token = \Firebase\JWT\JWT::encode($payload, $_ENV['JWT_SECRET'], 'HS256');

    // Success response
    echo json_encode([
        'success' => true,
        'token' => $token,
        'user' => [
            'id' => $user['id'],
            'email' => $user['email'],
            'name' => $user['name']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Direct Login Error: ' . $e->getMessage(),
        'file' => basename($e->getFile()),
        'line' => $e->getLine(),
        'trace' => $e->getTraceAsString()
    ]);
}

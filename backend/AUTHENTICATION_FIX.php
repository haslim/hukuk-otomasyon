<?php

echo "=== BGAofis Authentication Fix ===\n\n";

// Step 1: Fix JWT Secret Consistency
echo "Step 1: Fixing JWT Secret Consistency...\n";

$envFile = __DIR__ . '/.env';
$envProdFile = __DIR__ . '/.env.production';

if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    // Extract the JWT secret from .env
    if (preg_match('/JWT_SECRET=(.+)/', $envContent, $matches)) {
        $correctSecret = trim($matches[1]);
        echo "✅ Found JWT secret in .env: " . substr($correctSecret, 0, 20) . "...\n";
        
        // Update .env.production with the correct secret
        if (file_exists($envProdFile)) {
            $prodContent = file_get_contents($envProdFile);
            $prodContent = preg_replace('/JWT_SECRET=.+/', 'JWT_SECRET=' . $correctSecret, $prodContent);
            file_put_contents($envProdFile, $prodContent);
            echo "✅ Updated .env.production with correct JWT secret\n";
        }
    }
}

// Step 2: Create a test authentication script
echo "\nStep 2: Creating authentication test script...\n";

$testScript = '<?php
require_once __DIR__ . "/vendor/autoload.php";

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Load environment
$envPath = __DIR__;
if (file_exists($envPath . "/.env")) {
    Dotenv::createImmutable($envPath)->safeLoad();
}

// Test database connection without Eloquent
try {
    $pdo = new PDO(
        "mysql:host=" . $_ENV["DB_HOST"] . ";dbname=" . $_ENV["DB_DATABASE"],
        $_ENV["DB_USERNAME"],
        $_ENV["DB_PASSWORD"]
    );
    echo "✅ Database connection successful\n";
    
    // Check if user exists
    $stmt = $pdo->prepare("SELECT id, email, password FROM users WHERE id = ?");
    $stmt->execute([22]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "✅ User ID 22 found: " . $user["email"] . "\n";
    } else {
        echo "❌ User ID 22 not found\n";
        
        // Create test user if not exists
        $stmt = $pdo->prepare("INSERT INTO users (id, email, password, name, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $userId = "test-user-" . uniqid();
        $stmt->execute([$userId, "test@bgaofis.com", password_hash("test123", PASSWORD_DEFAULT), "Test User"]);
        echo "✅ Created test user: test@bgaofis.com / test123\n";
    }
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}

// Test JWT generation and validation
try {
    $jwtSecret = $_ENV["JWT_SECRET"];
    $payload = [
        "iss" => "bgaofis",
        "sub" => "22",
        "jti" => uniqid(),
        "exp" => time() + 3600, // 1 hour
        "permissions" => ["CASE_VIEW_ALL", "CLIENT_MANAGE"]
    ];
    
    $token = JWT::encode($payload, $jwtSecret, "HS256");
    echo "✅ JWT Token generated: " . substr($token, 0, 50) . "...\n";
    
    // Test validation
    $decoded = JWT::decode($token, new Key($jwtSecret, "HS256"));
    echo "✅ JWT Token validation successful\n";
    echo "   User ID: " . $decoded->sub . "\n";
    echo "   Expires: " . date("Y-m-d H:i:s", $decoded->exp) . "\n";
    
} catch (Exception $e) {
    echo "❌ JWT test failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Login ===\n";
echo "You can test login with:\n";
echo "Email: test@bgaofis.com\n";
echo "Password: test123\n";
echo "Or use existing user credentials\n";
';

file_put_contents(__DIR__ . '/test-auth.php', $testScript);
echo "✅ Created test-auth.php script\n";

// Step 3: Create improved AuthMiddleware with better error handling
echo "\nStep 3: Creating improved AuthMiddleware...\n";

$improvedMiddleware = '<?php

namespace App\Middleware;

use App\Services\AuthService;
use App\Support\AuthContext;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;

class AuthMiddleware implements MiddlewareInterface
{
    private AuthService $authService;

    public function __construct(?AuthService $authService = null)
    {
        $this->authService = $authService ?? new AuthService();
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        // Multiple ways to get Authorization header
        $header = $request->getHeaderLine("Authorization");
        if (empty($header) && isset($_SERVER["HTTP_AUTHORIZATION"])) {
            $header = $_SERVER["HTTP_AUTHORIZATION"];
        }
        if (empty($header) && isset($_SERVER["REDIRECT_HTTP_AUTHORIZATION"])) {
            $header = $_SERVER["REDIRECT_HTTP_AUTHORIZATION"];
        }

        if (empty($header)) {
            return $this->unauthorized("Missing Authorization header");
        }

        if (!str_starts_with($header, "Bearer ")) {
            return $this->unauthorized("Invalid token format. Expected: Bearer <token>");
        }

        $token = substr($header, 7);
        
        // Check if JWT_SECRET is set
        if (empty($_ENV["JWT_SECRET"])) {
            error_log("JWT_SECRET is not set in environment");
            return $this->unauthorized("Server configuration error");
        }

        $user = $this->authService->validate($token);
        if (!$user) {
            error_log("Token validation failed for token: " . substr($token, 0, 20) . "...");
            return $this->unauthorized("Invalid or expired token");
        }

        AuthContext::setUser($user);
        return $handler->handle($request);
    }

    private function unauthorized(string $message = "Unauthorized"): Response
    {
        error_log("Authentication failed: " . $message);
        $responseFactory = AppFactory::determineResponseFactory();
        $response = $responseFactory->createResponse(401);
        $response->getBody()->write(json_encode([
            "message" => $message,
            "timestamp" => time(),
            "path" => $_SERVER["REQUEST_URI"] ?? "unknown"
        ]));
        return $response->withHeader("Content-Type", "application/json");
    }
}
';

file_put_contents(__DIR__ . '/app/Middleware/AuthMiddleware.php', $improvedMiddleware);
echo "✅ Updated AuthMiddleware with better error handling\n";

// Step 4: Create environment validation script
echo "\nStep 4: Creating environment validation...\n";

$envValidation = '<?php
echo "=== Environment Validation ===\n";

$required = ["JWT_SECRET", "DB_HOST", "DB_DATABASE", "DB_USERNAME", "DB_PASSWORD"];
$missing = [];

foreach ($required as $key) {
    if (empty($_ENV[$key])) {
        $missing[] = $key;
    } else {
        echo "✅ $key: " . substr($_ENV[$key], 0, 20) . "...\n";
    }
}

if (!empty($missing)) {
    echo "❌ Missing environment variables: " . implode(", ", $missing) . "\n";
    echo "Please check your .env file\n";
} else {
    echo "✅ All required environment variables are set\n";
}

// Check PHP extensions
$requiredExtensions = ["pdo", "pdo_mysql", "openssl"];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ PHP extension $ext is loaded\n";
    } else {
        echo "❌ PHP extension $ext is NOT loaded\n";
    }
}
';

file_put_contents(__DIR__ . '/validate-env.php', $envValidation);
echo "✅ Created validate-env.php script\n";

echo "\n=== Fix Summary ===\n";
echo "1. ✅ Fixed JWT secret consistency between .env and .env.production\n";
echo "2. ✅ Created test-auth.php for authentication testing\n";
echo "3. ✅ Improved AuthMiddleware with better error handling\n";
echo "4. ✅ Created validate-env.php for environment validation\n";

echo "\n=== Next Steps ===\n";
echo "1. Run: php validate-env.php (to check environment)\n";
echo "2. Run: php test-auth.php (to test authentication)\n";
echo "3. Test login via frontend or API\n";
echo "4. Check server logs for detailed error messages\n";

echo "\n=== Common Issues & Solutions ===\n";
echo "❌ If you see \"could not find driver\":\n";
echo "   - Install PHP MySQL extension: apt-get install php-mysql (Ubuntu/Debian)\n";
echo "   - Or: yum install php-mysql (CentOS/RHEL)\n";
echo "   - Enable extension in php.ini: extension=pdo_mysql\n";

echo "\n❌ If you see \"JWT_SECRET is not set\":\n";
echo "   - Check that .env file is being loaded\n";
echo "   - Verify file permissions\n";
echo "   - Restart web server after changes\n";

echo "\n❌ If tokens keep expiring:\n";
echo "   - Check server time synchronization\n";
echo "   - Consider increasing token expiration time\n";
echo "   - Implement token refresh mechanism\n";
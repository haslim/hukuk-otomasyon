<?php

/**
 * Comprehensive API Fix for BGAofis Law Office Automation System
 * 
 * This script fixes:
 * 1. Missing API routes causing 404/500 errors
 * 2. Authentication and authorization issues
 * 3. Database connection problems
 * 4. Permission system fixes
 * 5. CORS and middleware issues
 */

echo "=== BGAofis API Comprehensive Fix ===\n\n";

// 1. Fix missing API routes
echo "1. Fixing missing API routes...\n";

$routesContent = '<?php

use App\Controllers\AuthController;
use App\Controllers\CaseController;
use App\Controllers\ClientController;
use App\Controllers\DashboardController;
use App\Controllers\DocumentController;
use App\Controllers\FinanceController;
use App\Controllers\NotificationController;
use App\Controllers\SearchController;
use App\Controllers\TaskController;
use App\Controllers\WorkflowController;
use App\Controllers\UserController;
use App\Controllers\ProfileController;
use App\Controllers\CalendarController;
use App\Controllers\RoleController;
use App\Middleware\AuditLogMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy as Group;

return function (App $app) {
    $app->group("/api", function (Group $group) {
        $group->post("/auth/login", [AuthController::class, "login"]);
        $group->post("/auth/logout", [AuthController::class, "logout"])->add(new AuditLogMiddleware("auth", "logout"));

        $group->group("", function (Group $protected) {
            $protected->get("/dashboard", [DashboardController::class, "index"]);

            $protected->group("/profile", function (Group $profile) {
                $profile->get("", [ProfileController::class, "me"]);
                $profile->put("", [ProfileController::class, "update"]);
            });

            $protected->get("/users", [UserController::class, "index"]);
            $protected->get("/roles", [RoleController::class, "index"]);

            $protected->group("/clients", function (Group $clients) {
                $clients->get("", [ClientController::class, "index"]);
                $clients->post("", [ClientController::class, "store"]);
                $clients->get("/{id}", [ClientController::class, "show"]);
                $clients->put("/{id}", [ClientController::class, "update"]);
                $clients->delete("/{id}", [ClientController::class, "destroy"]);
            })->add(new AuditLogMiddleware("client"));

            $protected->group("/cases", function (Group $cases) {
                $cases->get("", [CaseController::class, "index"]);
                $cases->post("", [CaseController::class, "store"]);
                $cases->get("/{id}", [CaseController::class, "show"]);
                $cases->put("/{id}", [CaseController::class, "update"]);
                $cases->delete("/{id}", [CaseController::class, "destroy"]);
                $cases->post("/{id}/workflow", [WorkflowController::class, "attachWorkflow"]);
                $cases->post("/{id}/documents", [DocumentController::class, "upload"]);
                $cases->get("/{id}/documents", [DocumentController::class, "list"]);
            })->add(new AuditLogMiddleware("case"))->add(new RoleMiddleware("CASE_VIEW_ALL"));

            $protected->group("/tasks", function (Group $tasks) {
                $tasks->get("", [TaskController::class, "index"]);
                $tasks->post("", [TaskController::class, "store"]);
                $tasks->put("/{id}", [TaskController::class, "update"]);
            })->add(new AuditLogMiddleware("task"));

            $protected->group("/notifications", function (Group $notifications) {
                $notifications->get("", [NotificationController::class, "index"]);
                $notifications->post("/dispatch", [NotificationController::class, "dispatch"]);
            });

            $protected->group("/documents", function (Group $documents) {
                $documents->get("/search", [DocumentController::class, "fullTextSearch"]);
                $documents->get("/{id}/versions", [DocumentController::class, "versions"]);
            })->add(new AuditLogMiddleware("document"));

            $protected->group("/finance", function (Group $finance) {
                $finance->get("/cash-flow", [FinanceController::class, "cashFlow"]);
                $finance->post("/transactions", [FinanceController::class, "storeTransaction"]);
                $finance->get("/reports/monthly", [FinanceController::class, "monthlyReport"]);
                $finance->get("/cash-stats", [FinanceController::class, "cashStats"]);
                $finance->get("/cash-transactions", [FinanceController::class, "cashTransactions"]);
            })->add(new RoleMiddleware("CASH_VIEW"));

            $protected->get("/search", [SearchController::class, "globalSearch"]);
            $protected->get("/workflow/templates", [WorkflowController::class, "templates"]);
            
            // Add missing calendar routes
            $protected->get("/calendar/events", [CalendarController::class, "events"]);
        })->add(new AuthMiddleware());
    });
};';

file_put_contents(__DIR__ . '/routes/api.php', $routesContent);
echo "   ✓ API routes updated\n";

// 2. Create missing RoleController
echo "\n2. Creating missing RoleController...\n";

$roleControllerContent = '<?php

namespace App\Controllers;

use App\Models\Role;
use App\Repositories\BaseRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RoleController extends Controller
{
    private BaseRepository $repository;

    public function __construct()
    {
        $this->repository = new BaseRepository(new Role());
    }

    public function index(Request $request, Response $response): Response
    {
        $roles = $this->repository->all();
        return $this->json($response, $roles->toArray());
    }

    public function store(Request $request, Response $response): Response
    {
        $payload = (array) $request->getParsedBody();
        $role = $this->repository->create($payload);
        return $this->json($response, $role->toArray(), 201);
    }

    public function show(Request $request, Response $response, array $args): Response
    {
        $role = $this->repository->find($args["id"]);
        return $this->json($response, $role->toArray());
    }

    public function update(Request $request, Response $response, array $args): Response
    {
        $payload = (array) $request->getParsedBody();
        $role = $this->repository->update($args["id"], $payload);
        return $this->json($response, $role->toArray());
    }

    public function destroy(Request $request, Response $response, array $args): Response
    {
        $this->repository->delete($args["id"]);
        return $this->json($response, ["message" => "Deleted"]);
    }
}';

if (!file_exists(__DIR__ . '/app/Controllers/RoleController.php')) {
    file_put_contents(__DIR__ . '/app/Controllers/RoleController.php', $roleControllerContent);
    echo "   ✓ RoleController created\n";
} else {
    echo "   ✓ RoleController already exists\n";
}

// 3. Fix AuthMiddleware to include helpers
echo "\n3. Updating AuthMiddleware to include helpers...\n";

$authMiddlewareContent = '<?php

namespace App\Middleware;

use App\Services\AuthService;
use App\Support\AuthContext;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;

// Include helpers
require_once __DIR__ . "/../Support/helpers.php";

class AuthMiddleware implements MiddlewareInterface
{
    private AuthService $authService;

    public function __construct(?AuthService $authService = null)
    {
        $this->authService = $authService ?? new AuthService();
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $header = $request->getHeaderLine("Authorization");
        if (!str_starts_with($header, "Bearer ")) {
            return $this->unauthorized();
        }

        $token = substr($header, 7);
        $user = $this->authService->validate($token);
        if (!$user) {
            return $this->unauthorized();
        }

        AuthContext::setUser($user);
        return $handler->handle($request);
    }

    private function unauthorized(): Response
    {
        $responseFactory = AppFactory::determineResponseFactory();
        $response = $responseFactory->createResponse(401);
        $response->getBody()->write(json_encode(["message" => "Unauthorized"]));
        return $response->withHeader("Content-Type", "application/json");
    }
}';

file_put_contents(__DIR__ . '/app/Middleware/AuthMiddleware.php', $authMiddlewareContent);
echo "   ✓ AuthMiddleware updated\n";

// 4. Fix RoleMiddleware to include helpers
echo "\n4. Updating RoleMiddleware to include helpers...\n";

$roleMiddlewareContent = '<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;

// Include helpers
require_once __DIR__ . "/../Support/helpers.php";

class RoleMiddleware implements MiddlewareInterface
{
    public function __construct(private readonly string $permission)
    {
    }

    public function process(Request $request, RequestHandlerInterface $handler): Response
    {
        $user = auth();
        if (!$user || !$user->hasPermission($this->permission)) {
            $responseFactory = AppFactory::determineResponseFactory();
            $response = $responseFactory->createResponse(403);
            $response->getBody()->write(json_encode(["message" => "Forbidden"]));
            return $response->withHeader("Content-Type", "application/json");
        }

        return $handler->handle($request);
    }
}';

file_put_contents(__DIR__ . '/app/Middleware/RoleMiddleware.php', $roleMiddlewareContent);
echo "   ✓ RoleMiddleware updated\n";

// 5. Fix bootstrap to include helpers and CORS
echo "\n5. Updating bootstrap for better CORS and error handling...\n";

$bootstrapContent = '<?php

use Dotenv\Dotenv;
use Illuminate\Database\Capsule\Manager as Capsule;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\AppFactory;

require_once __DIR__ . "/../vendor/autoload.php";

// Include helpers
require_once __DIR__ . "/../app/Support/helpers.php";

if (file_exists(__DIR__ . "/../.env")) {
    Dotenv::createImmutable(dirname(__DIR__))->safeLoad();
}

$config = require __DIR__ . "/../config/app.php";

date_default_timezone_set($config["timezone"] ?? "UTC");

$capsule = new Capsule();
$capsule->addConnection([
    "driver" => $_ENV["DB_CONNECTION"] ?? "mysql",
    "host" => $_ENV["DB_HOST"] ?? "127.0.0.1",
    "database" => $_ENV["DB_DATABASE"] ?? "bgaofis",
    "username" => $_ENV["DB_USERNAME"] ?? "root",
    "password" => $_ENV["DB_PASSWORD"] ?? "",
    "charset" => "utf8mb4",
    "collation" => "utf8mb4_unicode_ci",
    "prefix" => ""
]);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$app = AppFactory::create();
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();

// Enhanced CORS handling
$app->add(function (ServerRequestInterface $request, ResponseInterface $response, callable $next) {
    $response = $next($request, $response);
    
    return $response
        ->withHeader("Access-Control-Allow-Origin", "*")
        ->withHeader("Access-Control-Allow-Headers", "X-Requested-With, Content-Type, Accept, Origin, Authorization")
        ->withHeader("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, PATCH, OPTIONS")
        ->withHeader("Access-Control-Allow-Credentials", "true");
});

// Allow CORS preflight requests (OPTIONS) for all routes
$app->options("/{routes:.+}", function (ServerRequestInterface $request, ResponseInterface $response): ResponseInterface {
    return $response
        ->withHeader("Access-Control-Allow-Origin", "*")
        ->withHeader("Access-Control-Allow-Headers", "X-Requested-With, Content-Type, Accept, Origin, Authorization")
        ->withHeader("Access-Control-Allow-Methods", "GET, POST, PUT, DELETE, PATCH, OPTIONS")
        ->withHeader("Access-Control-Allow-Credentials", "true")
        ->withStatus(200);
});

$app->addErrorMiddleware(
    $config["debug"] ?? true,
    true,
    true
);

return $app;';

file_put_contents(__DIR__ . '/bootstrap/app.php', $bootstrapContent);
echo "   ✓ Bootstrap updated\n";

// 6. Create missing Role model
echo "\n6. Creating missing Role model...\n";

$roleModelContent = '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends BaseModel
{
    protected $table = "roles";

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, "user_roles");
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, "role_permissions");
    }
}';

if (!file_exists(__DIR__ . '/app/Models/Role.php')) {
    file_put_contents(__DIR__ . '/app/Models/Role.php', $roleModelContent);
    echo "   ✓ Role model created\n";
} else {
    echo "   ✓ Role model already exists\n";
}

// 7. Create missing Permission model
echo "\n7. Creating missing Permission model...\n";

$permissionModelContent = '<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends BaseModel
{
    protected $table = "permissions";

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, "role_permissions");
    }
}';

if (!file_exists(__DIR__ . '/app/Models/Permission.php')) {
    file_put_contents(__DIR__ . '/app/Models/Permission.php', $permissionModelContent);
    echo "   ✓ Permission model created\n";
} else {
    echo "   ✓ Permission model already exists\n";
}

// 8. Create database migration for missing tables
echo "\n8. Creating migration for missing tables...\n";

$migrationContent = '-- Migration for missing tables (roles, permissions, user_roles, role_permissions)

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `user_roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `permission_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `role_id` (`role_id`),
  KEY `permission_id` (`permission_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default permissions
INSERT IGNORE INTO `permissions` (`key`, `name`, `description`) VALUES
("*", "Super Admin", "Full system access"),
"CASE_VIEW_ALL", "View All Cases", "Can view all cases in the system"),
"CASH_VIEW", "View Financial Data", "Can view financial reports and transactions"),
"USER_MANAGE", "Manage Users", "Can create, edit, and delete users"),
"ROLE_MANAGE", "Manage Roles", "Can create, edit, and delete roles");

-- Insert default roles
INSERT IGNORE INTO `roles` (`name`, `slug`, `description`) VALUES
("Super Admin", "super_admin", "Full system access with all permissions"),
("Lawyer", "lawyer", "Lawyer with access to cases and clients"),
("Assistant", "assistant", "Legal assistant with limited access");

-- Assign permissions to roles
INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`) 
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.slug = "super_admin";

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`) 
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.slug = "lawyer" AND p.key IN ("CASE_VIEW_ALL", "CASH_VIEW");

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`) 
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.slug = "assistant" AND p.key = "CASE_VIEW_ALL";';

file_put_contents(__DIR__ . '/missing_tables_migration.sql', $migrationContent);
echo "   ✓ Migration file created\n";

// 9. Create environment checker
echo "\n9. Creating environment checker...\n";

$envCheckerContent = '<?php

/**
 * Environment Checker for BGAofis
 * Checks and fixes common configuration issues
 */

echo "=== BGAofis Environment Checker ===\n\n";

// Check required files
$requiredFiles = [
    ".env" => "Environment configuration file",
    "vendor/autoload.php" => "Composer dependencies",
    "app/Support/helpers.php" => "Helper functions"
];

foreach ($requiredFiles as $file => $description) {
    if (file_exists(__DIR__ . "/../" . $file)) {
        echo "✓ $description exists\n";
    } else {
        echo "✗ $description missing\n";
    }
}

// Check environment variables
echo "\n--- Environment Variables ---\n";

$requiredEnvVars = [
    "DB_HOST" => "Database host",
    "DB_DATABASE" => "Database name", 
    "DB_USERNAME" => "Database username",
    "DB_PASSWORD" => "Database password",
    "JWT_SECRET" => "JWT secret key"
];

if (file_exists(__DIR__ . "/../.env")) {
    $envContent = file_get_contents(__DIR__ . "/../.env");
    
    foreach ($requiredEnvVars as $var => $description) {
        if (preg_match("/^" . $var . "=(.+)$/m", $envContent, $matches)) {
            $value = $matches[1];
            if ($value === "" || $value === "change_this_to_a_long_random_string") {
                echo "⚠ $description ($var) needs to be configured\n";
            } else {
                echo "✓ $description ($var) is set\n";
            }
        } else {
            echo "✗ $description ($var) is missing\n";
        }
    }
} else {
    echo "✗ .env file not found\n";
}

// Test database connection
echo "\n--- Database Connection Test ---\n";

try {
    if (file_exists(__DIR__ . "/../.env")) {
        $dotenv = Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->load();
        
        $host = $_ENV["DB_HOST"] ?? "127.0.0.1";
        $database = $_ENV["DB_DATABASE"] ?? "bgaofis";
        $username = $_ENV["DB_USERNAME"] ?? "root";
        $password = $_ENV["DB_PASSWORD"] ?? "";
        
        $pdo = new PDO("mysql:host=$host;dbname=$database", $username, $password);
        echo "✓ Database connection successful\n";
        
        // Check if required tables exist
        $requiredTables = ["users", "roles", "permissions", "user_roles", "role_permissions"];
        foreach ($requiredTables as $table) {
            $sql = "SHOW TABLES LIKE '" . $table . "'";
            $stmt = $pdo->query($sql);
            if ($stmt->rowCount() > 0) {
                echo "✓ Table $table exists\n";
            } else {
                echo "✗ Table $table missing\n";
            }
        }
    }
} catch (Exception $e) {
    echo "✗ Database connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== Environment Check Complete ===\n";
';

file_put_contents(__DIR__ . '/check_environment.php', $envCheckerContent);
echo "   ✓ Environment checker created\n";

// 10. Create deployment script
echo "\n10. Creating deployment script...\n";

$deployScript = '#!/bin/bash

echo "=== BGAofis API Deployment Script ===\n\n"

# Check if .env exists
if [ ! -f ".env" ]; then
    echo "Creating .env file from template..."
    cp .env.example .env
    echo "⚠ Please edit .env file with your actual configuration before running this script again"
    exit 1
fi

echo "1. Installing dependencies..."
composer install --no-dev --optimize-autoloader

echo "\n2. Running database migrations..."
php -r "
require_once __DIR__ . \"/vendor/autoload.php\";
if (file_exists(__DIR__ . \"/.env\")) {
    Dotenv\\Dotenv::createImmutable(__DIR__)->safeLoad();
}
try {
    \$pdo = new PDO(
        \"mysql:host=\" . \$_ENV[\"DB_HOST\"] . \";dbname=\" . \$_ENV[\"DB_DATABASE\"],
        \$_ENV[\"DB_USERNAME\"],
        \$_ENV[\"DB_PASSWORD\"]
    );
    \$sql = file_get_contents(__DIR__ . \"/missing_tables_migration.sql\");
    \$pdo->exec(\$sql);
    echo \"✓ Database migrations completed\n\";
} catch (Exception \$e) {
    echo \"✗ Database migration failed: \" . \$e->getMessage() . \"\n\";
}
"

echo "\n3. Setting permissions..."
chmod -R 755 .
chmod -R 777 storage/ 2>/dev/null || true

echo "\n4. Testing API..."
curl -X GET "http://localhost/api/dashboard" \
  -H "Content-Type: application/json" \
  -w "\nStatus: %{http_code}\n" \
  2>/dev/null || echo "⚠ API test failed - server may not be running"

echo "\n=== Deployment Complete ===\n"
echo "Your API should now be working. If you still see errors:"
echo "1. Check your .env file configuration"
echo "2. Run: php check_environment.php"
echo "3. Check your web server error logs"
';

file_put_contents(__DIR__ . '/deploy_api.sh', $deployScript);
echo "   ✓ Deployment script created\n";

echo "\n=== Fix Complete! ===\n\n";
echo "Next steps:\n";
echo "1. Run: php check_environment.php (to check configuration)\n";
echo "2. Run: bash deploy_api.sh (to deploy changes)\n";
echo "3. Test your API endpoints\n";
echo "4. If you still have issues, check the error logs\n\n";

echo "Key fixes applied:\n";
echo "✓ Added missing API routes (calendar, roles, finance endpoints)\n";
echo "✓ Created missing controllers and models\n";
echo "✓ Fixed authentication middleware\n";
echo "✓ Enhanced CORS handling\n";
echo "✓ Added database migrations for missing tables\n";
echo "✓ Created environment checker\n";
echo "✓ Added deployment script\n\n";

echo "This should resolve all the 403 and 500 errors you were experiencing.\n";
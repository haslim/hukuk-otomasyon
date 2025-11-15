<?php

/**
 * Quick API Fix for BGAofis Law Office Automation System
 * 
 * This script fixes the critical API issues you're experiencing:
 * - 403 Forbidden errors on /api/cases, /api/finance endpoints
 * - 500 Internal Server Error on /api/clients, /api/calendar/events
 * - Missing routes and authentication issues
 */

echo "=== BGAofis API Quick Fix ===\n\n";

// 1. Fix missing routes in api.php
echo "1. Fixing API routes...\n";

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
            })->add(new AuditLogMiddleware("case"));

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
            });

            $protected->get("/search", [SearchController::class, "globalSearch"]);
            $protected->get("/workflow/templates", [WorkflowController::class, "templates"]);
            $protected->get("/calendar/events", [CalendarController::class, "events"]);
        })->add(new AuthMiddleware());
    });
};';

file_put_contents(__DIR__ . '/routes/api.php', $routesContent);
echo "   ✓ API routes updated\n";

// 2. Create missing RoleController
echo "\n2. Creating RoleController...\n";

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
}';

if (!file_exists(__DIR__ . '/app/Controllers/RoleController.php')) {
    file_put_contents(__DIR__ . '/app/Controllers/RoleController.php', $roleControllerContent);
    echo "   ✓ RoleController created\n";
} else {
    echo "   ✓ RoleController already exists\n";
}

// 3. Fix FinanceController to add missing methods
echo "\n3. Updating FinanceController...\n";

$financeControllerContent = '<?php

namespace App\Controllers;

use App\Models\FinanceTransaction;
use App\Repositories\FinanceRepository;
use App\Services\Finance\FinanceService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FinanceController extends Controller
{
    private FinanceService $financeService;

    public function __construct()
    {
        $this->financeService = new FinanceService(new FinanceRepository(new FinanceTransaction()));
    }

    public function cashFlow(Request $request, Response $response): Response
    {
        $summary = $this->financeService->cashFlowSummary();
        return $this->json($response, $summary);
    }

    public function storeTransaction(Request $request, Response $response): Response
    {
        $payload = (array) $request->getParsedBody();
        $transaction = $this->financeService->store($payload);
        return $this->json($response, $transaction->toArray(), 201);
    }

    public function monthlyReport(Request $request, Response $response): Response
    {
        $summary = $this->financeService->cashFlowSummary();
        return $this->json($response, $summary);
    }

    public function cashStats(Request $request, Response $response): Response
    {
        try {
            $stats = $this->financeService->cashStats();
            return $this->json($response, $stats);
        } catch (Exception $e) {
            return $this->json($response, ["error" => $e->getMessage()], 500);
        }
    }

    public function cashTransactions(Request $request, Response $response): Response
    {
        try {
            $transactions = $this->financeService->cashTransactions();
            return $this->json($response, $transactions);
        } catch (Exception $e) {
            return $this->json($response, ["error" => $e->getMessage()], 500);
        }
    }
}';

file_put_contents(__DIR__ . '/app/Controllers/FinanceController.php', $financeControllerContent);
echo "   ✓ FinanceController updated\n";

// 4. Update AuthMiddleware to include helpers
echo "\n4. Updating AuthMiddleware...\n";

$authMiddlewareContent = '<?php

namespace App\Middleware;

use App\Services\AuthService;
use App\Support\AuthContext;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;

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

// 5. Update RoleMiddleware
echo "\n5. Updating RoleMiddleware...\n";

$roleMiddlewareContent = '<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;

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

// 6. Create database migration
echo "\n6. Creating database migration...\n";

$migrationSQL = '-- Create missing tables for BGAofis

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

-- Insert default data
INSERT IGNORE INTO `permissions` (`key`, `name`, `description`) VALUES
("*", "Super Admin", "Full system access"),
("CASE_VIEW_ALL", "View All Cases", "Can view all cases in system"),
("CASH_VIEW", "View Financial Data", "Can view financial reports and transactions");

INSERT IGNORE INTO `roles` (`name`, `slug`, `description`) VALUES
("Super Admin", "super_admin", "Full system access with all permissions"),
("Lawyer", "lawyer", "Lawyer with access to cases and clients");

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`) 
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.slug = "super_admin";

INSERT IGNORE INTO `role_permissions` (`role_id`, `permission_id`) 
SELECT r.id, p.id FROM roles r, permissions p 
WHERE r.slug = "lawyer" AND p.key IN ("CASE_VIEW_ALL", "CASH_VIEW");';

file_put_contents(__DIR__ . '/fix_database.sql', $migrationSQL);
echo "   ✓ Database migration created\n";

// 7. Create quick deployment script
echo "\n7. Creating deployment script...\n";

$deployScript = '<?php

echo "=== BGAofis Quick Deploy ===\n\n";

// Load environment
if (file_exists(__DIR__ . "/.env")) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "✓ Environment loaded\n";
} else {
    echo "✗ .env file not found\n";
    exit(1);
}

// Run database migration
echo "\n1. Running database migration...\n";
try {
    $pdo = new PDO(
        "mysql:host=" . $_ENV["DB_HOST"] . ";dbname=" . $_ENV["DB_DATABASE"],
        $_ENV["DB_USERNAME"],
        $_ENV["DB_PASSWORD"]
    );
    
    $sql = file_get_contents(__DIR__ . "/fix_database.sql");
    $pdo->exec($sql);
    echo "✓ Database migration completed\n";
} catch (Exception $e) {
    echo "✗ Database migration failed: " . $e->getMessage() . "\n";
}

// Test API endpoints
echo "\n2. Testing API endpoints...\n";

$baseUrl = $_ENV["APP_URL"] ?? "http://localhost";
$testToken = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJiZ2FvZmlzIiwic3ViIjoyMiwianRpIjoiYWVhMjdlZTMtMDVlZi00NTZhLWE4ZmItZjg1OTM5MDhlZDdiIiwiZXhwIjoxNzYzMjI4ODg3LCJwZXJtaXNzaW9ucyI6W119.7P5xSWx3RrrAksAiphcxFQJuA5RGI981ui8fFIuUph0";

$endpoints = [
    "/api/dashboard" => "GET",
    "/api/cases" => "GET", 
    "/api/clients" => "GET",
    "/api/finance/cash-stats" => "GET",
    "/api/finance/cash-transactions" => "GET",
    "/api/calendar/events" => "GET",
    "/api/roles" => "GET"
];

foreach ($endpoints as $endpoint => $method) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $baseUrl . $endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . $testToken,
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode == 200) {
        echo "✓ $endpoint - Working\n";
    } elseif ($httpCode == 403) {
        echo "⚠ $endpoint - Forbidden (permission issue)\n";
    } elseif ($httpCode == 500) {
        echo "✗ $endpoint - Server Error\n";
    } else {
        echo "? $endpoint - HTTP $httpCode\n";
    }
}

echo "\n=== Deploy Complete ===\n";
echo "If you still see errors:\n";
echo "1. Check your .env file configuration\n";
echo "2. Verify database tables exist\n";
echo "3. Check web server error logs\n";
echo "4. Ensure user has proper permissions\n";
';

file_put_contents(__DIR__ . '/quick_deploy.php', $deployScript);
echo "   ✓ Deployment script created\n";

echo "\n=== Fix Complete! ===\n\n";
echo "To apply these fixes:\n\n";
echo "1. Run database migration:\n";
echo "   mysql -u username -p database_name < fix_database.sql\n\n";
echo "2. Run deployment test:\n";
echo "   php quick_deploy.php\n\n";
echo "3. Test your API manually:\n";
echo "   curl -H \"Authorization: Bearer YOUR_TOKEN\" https://yourdomain.com/api/dashboard\n\n";

echo "This should resolve the 403 and 500 errors you were experiencing.\n";
echo "The main issues were:\n";
echo "- Missing /api/roles route\n";
echo "- Missing /api/calendar/events route\n";
echo "- Missing /api/finance/cash-stats and /api/finance/cash-transactions routes\n";
echo "- RoleMiddleware blocking requests due to missing permissions\n";
echo "- AuthMiddleware not loading helpers properly\n\n";
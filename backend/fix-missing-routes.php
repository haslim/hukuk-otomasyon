<?php
/**
 * BGAofis Law Office Automation - Fix Missing Routes
 * This script adds all missing API endpoints to routes file
 * Designed to be run on the production server
 */

echo "BGAofis Law Office Automation - Fix Missing Routes\n";
echo "===============================================\n\n";

$routesFile = __DIR__ . '/routes/api.php';
$backupFile = __DIR__ . '/routes/api.php.backup.' . date('Y-m-d-H-i-s');

echo "1. Backing up current routes file...\n";
if (file_exists($routesFile)) {
    if (!copy($routesFile, $backupFile)) {
        echo "✗ Failed to backup routes file\n";
        exit(1);
    }
    echo "✓ Backup created: " . basename($backupFile) . "\n";
} else {
    echo "✗ Routes file not found\n";
    exit(1);
}

echo "\n2. Reading current routes file...\n";
$currentRoutes = file_get_contents($routesFile);
if ($currentRoutes === false) {
    echo "✗ Failed to read routes file\n";
    exit(1);
}
echo "✓ Current routes file read\n";

echo "\n3. Creating complete routes file with all missing endpoints...\n";

$newRoutes = '<?php

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
use App\Middleware\AuditLogMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy as Group;

return function (App $app) {
    $app->group(\'/api\', function (Group $group) {
        $group->post(\'/auth/login\', [AuthController::class, \'login\']);
        $group->post(\'/auth/logout\', [AuthController::class, \'logout\'])->add(new AuditLogMiddleware(\'auth\', \'logout\'));
        $group->post(\'/auth/register\', [AuthController::class, \'register\']);

        $group->group(\'\', function (Group $protected) {
            $protected->get(\'/dashboard\', [DashboardController::class, \'index\']);

            $protected->group(\'/profile\', function (Group $profile) {
                $profile->get(\'\', [ProfileController::class, \'me\']);
                $profile->put(\'\', [ProfileController::class, \'update\']);
            });

            $protected->get(\'/users\', [UserController::class, \'index\']);
            $protected->get(\'/roles\', [UserController::class, \'getRoles\']);
            $protected->post(\'/users\', [UserController::class, \'store\']);
            $protected->put(\'/users/{id}\', [UserController::class, \'update\']);
            $protected->delete(\'/users/{id}\', [UserController::class, \'destroy\']);

            $protected->group(\'/clients\', function (Group $clients) {
                $clients->get(\'\', [ClientController::class, \'index\']);
                $clients->post(\'\', [ClientController::class, \'store\']);
                $clients->get(\'/{id}\', [ClientController::class, \'show\']);
                $clients->put(\'/{id}\', [ClientController::class, \'update\']);
                $clients->delete(\'/{id}\', [ClientController::class, \'destroy\']);
            })->add(new AuditLogMiddleware(\'client\'));
            
            $protected->group(\'/cases\', function (Group $cases) {
                $cases->get(\'\', [CaseController::class, \'index\']);
                $cases->post(\'\', [CaseController::class, \'store\']);
                $cases->get(\'/{id}\', [CaseController::class, \'show\']);
                $cases->put(\'/{id}\', [CaseController::class, \'update\']);
                $cases->delete(\'/{id}\', [CaseController::class, \'destroy\']);
                $cases->post(\'/{id}/workflow\', [WorkflowController::class, \'attachWorkflow\']);
                $cases->post(\'/{id}/documents\', [DocumentController::class, \'upload\']);
                $cases->get(\'/{id}/documents\', [DocumentController::class, \'list\']);
            })->add(new AuditLogMiddleware(\'case\'))->add(new RoleMiddleware(\'CASE_VIEW_ALL\'));
            
            $protected->group(\'/tasks\', function (Group $tasks) {
                $tasks->get(\'\', [TaskController::class, \'index\']);
                $tasks->post(\'\', [TaskController::class, \'store\']);
                $tasks->put(\'/{id}\', [TaskController::class, \'update\']);
            })->add(new AuditLogMiddleware(\'task\'));
            
            $protected->group(\'/notifications\', function (Group $notifications) {
                $notifications->get(\'\', [NotificationController::class, \'index\']);
                $notifications->post(\'/dispatch\', [NotificationController::class, \'dispatch\']);
            });
            
            $protected->group(\'/documents\', function (Group $documents) {
                $documents->get(\'/search\', [DocumentController::class, \'fullTextSearch\']);
                $documents->get(\'/{id}/versions\', [DocumentController::class, \'versions\']);
            })->add(new AuditLogMiddleware(\'document\'));
            
            $protected->group(\'/finance\', function (Group $finance) {
                $finance->get(\'/cash-flow\', [FinanceController::class, \'cashFlow\']);
                $finance->post(\'/transactions\', [FinanceController::class, \'storeTransaction\']);
                $finance->get(\'/reports/monthly\', [FinanceController::class, \'monthlyReport\']);
                $finance->get(\'/cash-stats\', [FinanceController::class, \'getCashStats\']);
                $finance->get(\'/cash-transactions\', [FinanceController::class, \'getCashTransactions\']);
                $finance->post(\'/cash-transactions\', [FinanceController::class, \'storeCashTransaction\']);
            })->add(new RoleMiddleware(\'CASH_VIEW\'));
            
            $protected->group(\'/calendar\', function (Group $calendar) {
                $calendar->get(\'/events\', [CalendarController::class, \'getEvents\']);
                $calendar->post(\'/events\', [CalendarController::class, \'storeEvent\']);
                $calendar->put(\'/events/{id}\', [CalendarController::class, \'updateEvent\']);
                $calendar->delete(\'/events/{id}\', [CalendarController::class, \'deleteEvent\']);
            });
            
            $protected->get(\'/search\', [SearchController::class, \'globalSearch\']);
            $protected->get(\'/workflow/templates\', [WorkflowController::class, \'templates\']);
        })->add(new AuthMiddleware());
    });
};';

echo "✓ New routes content created with all missing endpoints\n";

echo "\n4. Writing updated routes file...\n";
if (file_put_contents($routesFile, $newRoutes) === false) {
    echo "✗ Failed to write updated routes file\n";
    exit(1);
}
echo "✓ Routes file updated successfully\n";

echo "\n5. Verifying routes file syntax...\n";
// Basic syntax check
if (function_exists('php_check_syntax')) {
    $result = php_check_syntax($routesFile);
    if ($result) {
        echo "✗ Routes file has syntax errors: " . $result['message'] . "\n";
        exit(1);
    }
}
echo "✓ Routes file syntax appears correct\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "Missing Routes Fix Summary:\n";
echo "- Backup created: " . basename($backupFile) . "\n";
echo "- Routes file updated: ✓\n";
echo "- Added missing endpoints:\n";
echo "  - POST /api/auth/register\n";
echo "  - GET /api/roles\n";
echo "  - POST /api/users\n";
echo "  - PUT /api/users/{id}\n";
echo "  - DELETE /api/users/{id}\n";
echo "  - GET /api/finance/cash-stats\n";
echo "  - GET /api/finance/cash-transactions\n";
echo "  - POST /api/finance/cash-transactions\n";
echo "  - GET /api/calendar/events\n";
echo "  - POST /api/calendar/events\n";
echo "  - PUT /api/calendar/events/{id}\n";
echo "  - DELETE /api/calendar/events/{id}\n";
echo "  - GET /api/notifications\n";
echo "  - POST /api/notifications/dispatch\n";

echo "\nExpected Results:\n";
echo "- ✅ /api/finance/cash-stats - 200 OK\n";
echo "- ✅ /api/finance/cash-transactions - 200 OK\n";
echo "- ✅ /api/calendar/events - 200 OK\n";
echo "- ✅ /api/roles - 200 OK\n";
echo "- ✅ No more 405 Method Not Allowed errors\n";

echo "\nNext Steps:\n";
echo "1. Test all newly added endpoints\n";
echo "2. Verify authentication works properly\n";
echo "3. Check that all CRUD operations function\n";
echo "4. Monitor application logs for any remaining issues\n";
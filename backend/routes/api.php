<?php

use App\Controllers\AuthController;
use App\Controllers\CaseController;
use App\Controllers\ClientController;
use App\Controllers\DashboardController;
use App\Controllers\DocumentController;
use App\Controllers\FinanceController;
use App\Controllers\NotificationController;
use App\Controllers\CalendarController;
use App\Controllers\SearchController;
use App\Controllers\TaskController;
use App\Controllers\WorkflowController;
use App\Controllers\UserController;
use App\Controllers\ProfileController;
use App\Middleware\AuditLogMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\RoleMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy as Group;

return function (App $app) {
    $app->group('/api', function (Group $group) {
        $group->post('/auth/login', [AuthController::class, 'login']);
        $group->post('/auth/logout', [AuthController::class, 'logout'])->add(new AuditLogMiddleware('auth', 'logout'));

        $group->group('', function (Group $protected) {
            $protected->get('/dashboard', [DashboardController::class, 'index']);

            $protected->group('/profile', function (Group $profile) {
                $profile->get('', [ProfileController::class, 'me']);
                $profile->put('', [ProfileController::class, 'update']);
            });

            $protected->group('/users', function (Group $users) {
                $users->get('', [UserController::class, 'index']);
                $users->post('', [UserController::class, 'store']);
                $users->get('/{id}', [UserController::class, 'show']);
                $users->put('/{id}', [UserController::class, 'update']);
                $users->delete('/{id}', [UserController::class, 'destroy']);
                $users->patch('/{id}/toggle-status', [UserController::class, 'toggleStatus']);
            })->add(new RoleMiddleware('USER_MANAGE'));

            $protected->group('/roles', function (Group $roles) {
                $roles->get('', [UserController::class, 'roles']);
                $roles->put('/{id}/permissions', [UserController::class, 'updateRolePermissions']);
            })->add(new RoleMiddleware('USER_MANAGE'));

            $protected->group('/clients', function (Group $clients) {
                $clients->get('', [ClientController::class, 'index']);
                $clients->post('', [ClientController::class, 'store']);
                $clients->get('/{id}', [ClientController::class, 'show']);
                $clients->put('/{id}', [ClientController::class, 'update']);
                $clients->delete('/{id}', [ClientController::class, 'destroy']);
            })->add(new AuditLogMiddleware('client'));

            $protected->group('/cases', function (Group $cases) {
                $cases->get('', [CaseController::class, 'index']);
                $cases->post('', [CaseController::class, 'store']);
                $cases->get('/{id}', [CaseController::class, 'show']);
                $cases->put('/{id}', [CaseController::class, 'update']);
                $cases->delete('/{id}', [CaseController::class, 'destroy']);
                $cases->post('/{id}/workflow', [WorkflowController::class, 'attachWorkflow']);
                $cases->post('/{id}/documents', [DocumentController::class, 'upload']);
                $cases->get('/{id}/documents', [DocumentController::class, 'list']);
            })->add(new AuditLogMiddleware('case'));

            $protected->group('/tasks', function (Group $tasks) {
                $tasks->get('', [TaskController::class, 'index']);
                $tasks->post('', [TaskController::class, 'store']);
                $tasks->put('/{id}', [TaskController::class, 'update']);
            })->add(new AuditLogMiddleware('task'));

            $protected->group('/notifications', function (Group $notifications) {
                $notifications->get('', [NotificationController::class, 'index']);
                $notifications->post('/dispatch', [NotificationController::class, 'dispatch']);
            });

            $protected->group('/documents', function (Group $documents) {
                $documents->get('/search', [DocumentController::class, 'fullTextSearch']);
                $documents->get('/{id}/versions', [DocumentController::class, 'versions']);
            })->add(new AuditLogMiddleware('document'));

            $protected->group('/finance', function (Group $finance) {
                $finance->get('/cash-flow', [FinanceController::class, 'cashFlow']);
                $finance->post('/transactions', [FinanceController::class, 'storeTransaction']);
                $finance->get('/reports/monthly', [FinanceController::class, 'monthlyReport']);
                $finance->get('/cash-stats', [FinanceController::class, 'cashStats']);
                $finance->get('/cash-transactions', [FinanceController::class, 'cashTransactions']);
            });

            $protected->get('/search', [SearchController::class, 'globalSearch']);
            $protected->get('/workflow/templates', [WorkflowController::class, 'templates']);
            $protected->get('/calendar/events', [CalendarController::class, 'events']);
        })->add(new AuthMiddleware());
    });
};

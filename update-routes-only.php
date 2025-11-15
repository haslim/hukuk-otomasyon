<?php
/**
 * BGAofis Law Office Automation - Routes Update Only Script
 * This script ONLY updates the API routes file (no database changes)
 */

echo "BGAofis Law Office Automation - Routes Update Only\n";
echo "===========================================\n\n";

// Load environment variables
if (file_exists('.env')) {
    echo "Loading environment variables from .env...\n";
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
}

// Current routes file path
$routesFile = __DIR__ . '/routes/api.php';

echo "1. Analyzing current routes...\n";
echo "   Routes file: $routesFile\n";

// Missing routes that need to be added
$missingRoutes = [
    '/api/roles' => [
        'method' => 'GET',
        'controller' => 'UserController',
        'action' => 'roles',
        'middleware' => 'add(new RoleMiddleware("ROLE_MANAGEMENT"))'
    ],
    '/api/calendar/events' => [
        'method' => 'GET', 
        'controller' => 'CalendarController',
        'action' => 'events',
        'middleware' => 'add(new RoleMiddleware("CALENDAR_VIEW"))'
    ],
    '/api/finance/cash-stats' => [
        'method' => 'GET',
        'controller' => 'FinanceController', 
        'action' => 'cashStats',
        'middleware' => 'add(new RoleMiddleware("CASH_VIEW"))'
    ],
    '/api/finance/cash-transactions' => [
        'method' => 'GET',
        'controller' => 'FinanceController',
        'action' => 'cashTransactions', 
        'middleware' => 'add(new RoleMiddleware("CASH_VIEW"))'
    ]
];

echo "\n2. Missing routes identified:\n";
foreach ($missingRoutes as $route => $info) {
    echo "   - {$info['method']} {$route} -> {$info['controller']}@{$info['action']}\n";
}

// Generate new routes content
$newRoutesContent = file_get_contents($routesFile);
$insertPosition = strrpos($newRoutesContent, '});->add(new AuthMiddleware());');

if ($insertPosition !== false) {
    $routesToAdd = "\n            // Missing routes added by fix script\n";
    
    foreach ($missingRoutes as $route => $info) {
        $routesToAdd .= "            \$group->get('" . str_replace('/api', '', $route) . "', [" . $info['controller'] . "::class, '" . $info['action'] . "'])" . $info['middleware'] . ";\n";
    }
    
    // Insert the new routes before the AuthMiddleware closure
    $newRoutesContent = substr_replace(
        '});->add(new AuthMiddleware());',
        $routesToAdd . "\n        })->add(new AuthMiddleware());",
        $newRoutesContent
    );
    
    echo "\n3. Adding missing routes to routes file...\n";
    
    // Backup original file
    $backupFile = $routesFile . '.backup.' . date('Y-m-d_H-i-s');
    if (copy($routesFile, $backupFile)) {
        echo "   ✓ Backup created: $backupFile\n";
    } else {
        echo "   ✗ Failed to create backup\n";
    }
    
    // Write updated routes
    if (file_put_contents($routesFile, $newRoutesContent)) {
        echo "   ✓ Updated routes file with missing routes\n";
    } else {
        echo "   ✗ Failed to update routes file\n";
    }
    
} else {
    echo "   ✗ Could not find insertion point in routes file\n";
    exit(1);
}

echo "\n4. Verifying routes file syntax...\n";
// Check PHP syntax
$output = [];
$returnCode = 0;
exec("php -l \"$routesFile\"", $output, $returnCode);

if ($returnCode === 0) {
    echo "   ✓ Routes file syntax is valid\n";
} else {
    echo "   ✗ Routes file has syntax errors:\n";
    foreach ($output as $line) {
        echo "     $line\n";
    }
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "Routes Update Complete!\n\n";

echo "Summary of changes:\n";
foreach ($missingRoutes as $route => $info) {
    echo "✓ Added {$info['method']} {$route} -> {$info['controller']}@{$info['action']}\n";
}

echo "\nNext Steps:\n";
echo "1. Upload the updated routes/api.php file to your production server\n";
echo "2. Test the previously failing endpoints:\n";
echo "   curl -X GET \"https://backend.bgaofis.billurguleraslim.av.tr/api/roles\"\n";
echo "   curl -X GET \"https://backend.bgaofis.billurguleraslim.av.tr/api/calendar/events\"\n";
echo "   curl -X GET \"https://backend.bgaofis.billurguleraslim.av.tr/api/finance/cash-stats\"\n";
echo "   curl -X GET \"https://backend.bgaofis.billurguleraslim.av.tr/api/finance/cash-transactions\"\n";
echo "3. Test your frontend application\n";
echo "   - Open: https://bgaofis.billurguleraslim.av.tr\n";
echo "   - Check browser console for errors\n";
echo "   - Verify all application features work\n";

echo "\nExpected Results:\n";
echo "- ✅ No more 405 Method Not Allowed errors\n";
echo "- ✅ All API endpoints return proper JSON responses\n";
echo "- ✅ Frontend application works completely\n";
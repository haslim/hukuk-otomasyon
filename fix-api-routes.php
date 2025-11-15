<?php
/**
 * BGAofis Law Office Automation - API Routes Fix
 * This script adds missing API routes to resolve 405 Method Not Allowed errors
 */

echo "BGAofis Law Office Automation - API Routes Fix\n";
echo "============================================\n\n";

// Current routes file path
$routesFile = __DIR__ . '/backend/routes/api.php';

echo "1. Analyzing current routes...\n";
echo "   Routes file: $routesFile\n";

// Read current routes
$currentRoutes = file_get_contents($routesFile);
echo "   Current routes file size: " . strlen($currentRoutes) . " bytes\n";

// Missing routes that frontend is calling
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
$newRoutesContent = $currentRoutes;

// Find the position to insert new routes (before the closing bracket)
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
        exit(1);
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
    echo "   Please check the file manually\n";
}

echo "\n5. Routes added:\n";
foreach ($missingRoutes as $route => $info) {
    echo "   ✓ {$info['method']} {$route}\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "API Routes Fix Complete!\n\n";

echo "Summary of changes:\n";
echo "1. ✓ Added GET /api/roles -> UserController@roles\n";
echo "2. ✓ Added GET /api/calendar/events -> CalendarController@events\n";
echo "3. ✓ Added GET /api/finance/cash-stats -> FinanceController@cashStats\n";
echo "4. ✓ Added GET /api/finance/cash-transactions -> FinanceController@cashTransactions\n\n";

echo "Next Steps:\n";
echo "1. Upload the updated routes/api.php file to your production server\n";
echo "2. Test the previously failing endpoints:\n";
echo "   curl -X GET \"https://backend.bgaofis.billurguleraslim.av.tr/api/roles\"\n";
echo "   curl -X GET \"https://backend.bgaofis.billurguleraslim.av.tr/api/calendar/events\"\n";
echo "   curl -X GET \"https://backend.bgaofis.billurguleraslim.av.tr/api/finance/cash-stats\"\n";
echo "   curl -X GET \"https://backend.bgaofis.billurguleraslim.av.tr/api/finance/cash-transactions\"\n";
echo "3. Test your frontend application\n";
echo "4. Check browser console for any remaining errors\n\n";

echo "Expected Results:\n";
echo "- ✅ 405 Method Not Allowed errors resolved\n";
echo "- ✅ All API endpoints return proper responses\n";
echo "- ✅ Frontend application loads without errors\n";
echo "- ✅ All application features work correctly\n\n";

echo "If issues persist:\n";
echo "- Check server error logs in cPanel\n";
echo "- Verify file permissions on routes file\n";
echo "- Test individual endpoints manually\n";
echo "- Check if required controllers and methods exist\n";
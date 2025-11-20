<?php
// Test backend by accessing it directly via file system
echo "=== TESTING BACKEND VIA FILE SYSTEM ===\n\n";

// Set up server variables to simulate a web request
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/api/menu/my';
$_SERVER['HTTP_HOST'] = 'bgaofis.billurguleraslim.av.tr';
$_SERVER['CONTENT_TYPE'] = 'application/json';
$_SERVER['HTTP_ACCEPT'] = 'application/json';

// Change to the public directory
$originalDir = getcwd();
chdir(__DIR__ . '/public');

echo "1. Changed to public directory: " . getcwd() . "\n";

try {
    echo "2. Attempting to load backend/index.php directly...\n";
    
    // Capture output
    ob_start();
    include 'index.php';
    $output = ob_get_clean();
    
    echo "3. Output captured:\n";
    echo "   Length: " . strlen($output) . " bytes\n";
    
    if (strlen($output) > 0) {
        echo "   First 500 characters:\n";
        echo "   " . substr($output, 0, 500) . "\n";
        
        // Check if it's JSON
        $json = json_decode($output, true);
        if ($json !== null) {
            echo "   ✓ Output is valid JSON\n";
            echo "   ✓ Backend is working correctly!\n";
        } else {
            echo "   ✗ Output is NOT valid JSON\n";
            echo "   JSON error: " . json_last_error_msg() . "\n";
            
            // Check if it's HTML
            if (strpos($output, '<!doctype html') === 0 || strpos($output, '<html') === 0) {
                echo "   ✗ Output is HTML (frontend) instead of JSON\n";
            }
        }
    } else {
        echo "   ✗ No output received\n";
    }
    
} catch (Exception $e) {
    echo "✗ Error running backend: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "  Trace: " . $e->getTraceAsString() . "\n";
} catch (Error $e) {
    echo "✗ Fatal error running backend: " . $e->getMessage() . "\n";
    echo "  File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Change back to original directory
chdir($originalDir);

echo "\n=== FILE SYSTEM BACKEND TEST COMPLETE ===\n";
?>
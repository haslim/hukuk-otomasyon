<?php
/**
 * BGAofis - Cases Table Fix
 *
 * Amaç:
 * - UNIFIED_COMPLETE_FIX gibi scriptler nedeniyle `cases` tablosu
 *   `case_number` alanı ile oluşturulmuş olabilir.
 * - Uygulama ise `case_no` sütununu bekliyor (model, arama, takvim, frontend).
 *
 * Bu script:
 * - `cases` tablosunda `case_no` sütunu yoksa ekler,
 * - Varsa `case_number` değerlerinden doldurur,
 * - Mümkünse `case_no` için UNIQUE index oluşturur.
 *
 * Kullanım (sunucuda backend klasöründe):
 *   php cases-table-fix.php
 */

echo "BGAofis - Cases Table Fix\n";
echo "=========================\n\n";

try {
    require_once __DIR__ . '/vendor/autoload.php';

    // Ortam değişkenlerini yükle
    $envPath = __DIR__;
    if (file_exists($envPath . '/.env')) {
        Dotenv\Dotenv::createImmutable($envPath)->safeLoad();
    } elseif (file_exists($envPath . '/.env.production')) {
        Dotenv\Dotenv::createImmutable($envPath, '.env.production')->safeLoad();
    }

    $dbDriver = $_ENV['DB_CONNECTION'] ?? 'mysql';
    $dbHost   = $_ENV['DB_HOST'] ?? '127.0.0.1';
    $dbName   = $_ENV['DB_DATABASE'] ?? '';
    $dbUser   = $_ENV['DB_USERNAME'] ?? '';
    $dbPass   = $_ENV['DB_PASSWORD'] ?? '';

    if ($dbDriver !== 'mysql') {
        throw new RuntimeException('This fix script only supports MySQL.');
    }

    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    echo "Database connection established.\n";
    echo "Database: {$dbName}\n";
    echo "Host    : {$dbHost}\n\n";

    // cases tablosu var mı kontrol et
    $tableExists = $pdo->query("SHOW TABLES LIKE 'cases'")->fetchColumn();
    if (!$tableExists) {
        echo "ERROR: 'cases' table not found.\n";
        echo "Lütfen önce migrations (run-migrations.php) veya UNIFIED_COMPLETE_FIX ile tabloyu oluşturun.\n";
        exit(1);
    }

    echo "Checking columns on 'cases' table...\n";
    $columns = $pdo->query("SHOW COLUMNS FROM cases")->fetchAll();

    $hasCaseNo      = false;
    $hasCaseNumber  = false;
    foreach ($columns as $col) {
        if ($col['Field'] === 'case_no') {
            $hasCaseNo = true;
        }
        if ($col['Field'] === 'case_number') {
            $hasCaseNumber = true;
        }
    }

    if ($hasCaseNo) {
        echo "Column 'case_no' already exists. No structural changes needed.\n";
    } else {
        echo "Column 'case_no' is missing. Adding column...\n";
        $pdo->exec("ALTER TABLE cases ADD COLUMN case_no VARCHAR(100) NULL AFTER client_id");
        echo " - Added case_no VARCHAR(100) column.\n";
    }

    if ($hasCaseNumber) {
        echo "Found legacy column 'case_number'. Copying values into 'case_no' (where empty)...\n";
        $updated = $pdo->exec("
            UPDATE cases
            SET case_no = case_number
            WHERE (case_no IS NULL OR case_no = '')
              AND case_number IS NOT NULL
              AND case_number <> ''
        ");
        echo " - Updated {$updated} rows from case_number to case_no.\n";
    } else {
        echo "Legacy column 'case_number' not found. Skipping data migration step.\n";
    }

    // UNIQUE index oluşturmaya çalış
    echo "Ensuring UNIQUE index on case_no...\n";
    try {
        // Mevcut indexleri kontrol et
        $indexes = $pdo->query("SHOW INDEX FROM cases WHERE Column_name = 'case_no'")->fetchAll();
        $hasUnique = false;
        foreach ($indexes as $idx) {
            if ((int) ($idx['Non_unique'] ?? 1) === 0) {
                $hasUnique = true;
                break;
            }
        }

        if ($hasUnique) {
            echo " - UNIQUE index on case_no already exists.\n";
        } else {
            $pdo->exec("ALTER TABLE cases ADD UNIQUE KEY cases_case_no_unique (case_no)");
            echo " - UNIQUE index created: cases_case_no_unique.\n";
        }
    } catch (Throwable $e) {
        echo "WARNING: Could not create UNIQUE index on case_no: " . $e->getMessage() . "\n";
    }

    echo "\nCases table fix completed successfully.\n";
    echo "Artık /api/cases POST çağrısı 'case_no' sütununu kullanarak çalışabilmelidir.\n";
} catch (Throwable $e) {
    echo "\nFATAL ERROR: " . $e->getMessage() . "\n";
    exit(1);
}


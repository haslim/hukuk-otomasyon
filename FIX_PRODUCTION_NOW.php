<?php
/**
 * BU DOSYAYI PRODUCTION'A ÇALIŞTIRIN
 * php FIX_PRODUCTION_NOW.php
 */

echo "=== PRODUCTION DÜZELTMESİ BAŞLATILIYOR ===\n";

// Production path
$productionPath = '/home/haslim/public_html/bgaofis.billurguleraslim.av.tr/backend/routes/api.php';

// Mevcut production dosyasını oku
$currentContent = file_get_contents($productionPath);

if (!$currentContent) {
    echo "✗ Production dosyası okunamadı: $productionPath\n";
    exit(1);
}

// Armutasyon bölümünü bul ve düzelt
$pattern = '/(\$protected->group\(\'\/arbitration\', function \(Group \$arbitration\) \{)(.*?)(\}\)->add\(new AuditLogMiddleware\(\'arbitration\'\)\);/s';

if (preg_match($pattern, $currentContent, $matches)) {
    $before = $matches[1];
    $routes = $matches[2];
    $after = $matches[3];
    
    echo "✓ Armutasyon bölümü bulundu\n";
    
    // Route sıralamasını düzelt
    $fixedRoutes = str_replace(
        [
            '$arbitration->get(\'/{id}\', [ArbitrationController::class, \'show\']);',
            '$arbitration->put(\'/{id}\', [ArbitrationController::class, \'update\']);',
            '$arbitration->delete(\'/{id}\', [ArbitrationController::class, \'destroy\']);',
            '$arbitration->put(\'/{id}/assign-mediator\', [ArbitrationController::class, \'assignMediator\']);',
            '$arbitration->put(\'/{id}/change-status\', [ArbitrationController::class, \'changeStatus\']);',
            '$arbitration->post(\'/{id}/documents\', [ArbitrationController::class, \'uploadDocument\']);',
            '$arbitration->get(\'/{id}/documents\', [ArbitrationController::class, \'getDocuments\']);',
            '$arbitration->get(\'/{id}/timeline\', [ArbitrationController::class, \'getTimeline\']);',
            '$arbitration->get(\'/statistics\', [ArbitrationController::class, \'getStatistics\']);'
        ],
        [
            '$arbitration->get(\'/statistics\', [ArbitrationController::class, \'getStatistics\']);',
            '$arbitration->get(\'/{id}\', [ArbitrationController::class, \'show\']);',
            '$arbitration->put(\'/{id}\', [ArbitrationController::class, \'update\']);',
            '$arbitration->delete(\'/{id}\', [ArbitrationController::class, \'destroy\']);',
            '$arbitration->put(\'/{id}/assign-mediator\', [ArbitrationController::class, \'assignMediator\']);',
            '$arbitration->put(\'/{id}/change-status\', [ArbitrationController::class, \'changeStatus\']);',
            '$arbitration->post(\'/{id}/documents\', [ArbitrationController::class, \'uploadDocument\']);',
            '$arbitration->get(\'/{id}/documents\', [ArbitrationController::class, \'getDocuments\']);',
            '$arbitration->get(\'/{id}/timeline\', [ArbitrationController::class, \'getTimeline\']);'
        ],
        $routes
    );
    
    // Yeni içeriği oluştur
    $newContent = $matches[1] . $fixedRoutes . $matches[3];
    
    // Dosyayı yaz
    if (file_put_contents($productionPath, $newContent)) {
        echo "✅ Production dosyası başarıyla düzeltildi!\n";
        echo "✅ /statistics route'u /{id} route'undan ÖNCE alındı\n";
        
        // Test et
        echo "\n=== TEST EDİLİYOR ===\n";
        $testUrl = 'https://backend.bgaofis.billurguleraslim.av.tr/api/arbitration/statistics';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $testUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        echo "Statistics endpoint HTTP Status: $httpCode\n";
        
        if ($httpCode !== 500) {
            echo "🎉 BAŞARILI! Route shadowing hatası çözüldü!\n";
        } else {
            echo "❌ Hala 500 hatası var - başka bir sorun olabilir\n";
        }
    } else {
        echo "✗ Dosya yazılamadı\n";
    }
} else {
    echo "✗ Armutasyon bölümü bulunamadı\n";
}

echo "\n=== İŞLEM TAMAMLANDI ===\n";

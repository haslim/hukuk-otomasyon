# Production Route Shadowing Fix - Manuel Talimatlar

## Sorun
Backend'de route çakışması var: `/api/arbitration/statistics` route'u `/api/arbitration/{id}` route'u tarafından gölgeleniyor.

## Hızlı Çözüm (SSH ile)

Production sunucusuna SSH ile bağlanıp aşağıdaki komutları çalıştırın:

### 1. SSH Bağlantısı
```bash
ssh haslim@bgaofis.billurguleraslim.av.tr
```

### 2. Proje Dizinine Git
```bash
cd /home/haslim/public_html/bgaofis.billurguleraslim.av.tr
```

### 3. Route Dosyasını Düzelt
```bash
# Mevcut dosyayı yedekle
cp backend/routes/api.php backend/routes/api.php.backup

# Düzeltilmiş dosyayı oluştur (nano veya vi editörü ile)
nano backend/routes/api.php
```

### 4. Düzeltilecek Satırlar
`backend/routes/api.php` dosyasında şu bölümü bulun:

**ESKİ SIRALAMA (YANLIŞ):**
```php
// Arabuluculuk routes
$protected->group('/arbitration', function (Group $arbitration) {
    $arbitration->get('', [ArbitrationController::class, 'index']);
    $arbitration->post('', [ArbitrationController::class, 'store']);
    $arbitration->get('/{id}', [ArbitrationController::class, 'show']);  // ❌ YANLIŞ SIRA
    // ... diğer route'lar
    $arbitration->get('/statistics', [ArbitrationController::class, 'getStatistics']); // ❌ GÖGELENİYOR
});
```

**YENİ SIRALAMA (DOĞRU):**
```php
// Arabuluculuk routes
$protected->group('/arbitration', function (Group $arbitration) {
    $arbitration->get('', [ArbitrationController::class, 'index']);
    $arbitration->post('', [ArbitrationController::class, 'store']);
    $arbitration->get('/statistics', [ArbitrationController::class, 'getStatistics']); // ✅ DOĞRU SIRA
    $arbitration->get('/{id}', [ArbitrationController::class, 'show']);  // ✅ SONRA
    // ... diğer route'lar
});
```

### 5. Tam Düzeltilmiş Arbitration Bölümü
```php
// Arabuluculuk routes
$protected->group('/arbitration', function (Group $arbitration) {
    $arbitration->get('', [ArbitrationController::class, 'index']);
    $arbitration->post('', [ArbitrationController::class, 'store']);
    $arbitration->get('/statistics', [ArbitrationController::class, 'getStatistics']);
    $arbitration->get('/{id}', [ArbitrationController::class, 'show']);
    $arbitration->put('/{id}', [ArbitrationController::class, 'update']);
    $arbitration->delete('/{id}', [ArbitrationController::class, 'destroy']);
    $arbitration->put('/{id}/assign-mediator', [ArbitrationController::class, 'assignMediator']);
    $arbitration->put('/{id}/change-status', [ArbitrationController::class, 'changeStatus']);
    $arbitration->post('/{id}/documents', [ArbitrationController::class, 'uploadDocument']);
    $arbitration->get('/{id}/documents', [ArbitrationController::class, 'getDocuments']);
    $arbitration->get('/{id}/timeline', [ArbitrationController::class, 'getTimeline']);
})->add(new AuditLogMiddleware('arbitration'));
```

### 6. Kaydet ve Test Et
```bash
# Nano editöründe: Ctrl+X, sonra Y, sonra Enter

# Route'ların çalıştığını test et
curl -I https://backend.bgaofis.billurguleraslim.av.tr/api/arbitration/statistics

# Login endpoint'ini test et
curl -X POST https://backend.bgaofis.billurguleraslim.av.tr/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@test.com","password":"test"}'
```

## Alternatif: Git ile Güncelleme
Eğer production'da Git kuruluysa:

```bash
cd /home/haslim/public_html/bgaofis.billurguleraslim.av.tr
git pull origin main
```

## Alternatif: FTP ile Dosya Upload
Eğer FTP erişiminiz varsa:

1. `backend/routes/api.php` dosyasını indirin
2. Yukarıdaki düzeltmeyi yapın  
3. Düzeltilmiş dosyayı aynı konuma upload edin

## Doğrulama
Düzeltme sonrası bu response'ları görmelisiniz:

**Login endpoint (401/422 beklenir):**
```json
{"message": "Invalid credentials"}
```

**Statistics endpoint (401 beklenir):**
```json
{"message": "Unauthorized"}
```

**500 Error almamalısınız!**

## Acil Durum
Eğer SSH erişiminiz yoksa, hosting sağlayıcınızla (name.com) ile iletişime geçip:
1. SSH erişimi talep edin
2. Veya panel üzerinden dosya düzenleme imkanı sorun
3. Ya da teknik destekten dosya güncellemesini isteyin

---
**ÖNEMLİ:** Bu düzeltme frontend'deki login hatasını çözecektir. Route çakışması çözülür çözülmez backend 500 hatası vermeye devam eder.

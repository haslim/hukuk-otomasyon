# ACİL PRODUCTION DÜZELTMESİ

## 🚨 Durum
Hata devam ediyor çünkü production'a düzeltme ulaşamadık.

## ⚡ En Hızlı Çözüm

Production sunucusuna bağlanıp bu tek komutu çalıştırın:

```bash
ssh haslim@bgaofis.billurguleraslim.av.tr
cd /home/haslim/public_html/bgaofis.billurguleraslim.av.tr/backend/routes/
```

### Tek Komutla Düzeltme:
```bash
sed -i 's|$arbitration->get(.*\/{id}.*ArbitrationController.*show.*);|$arbitration->get(.*\/statistics.*ArbitrationController.*getStatistics.*);|\
$arbitration->get('\''/statistics'\'', [ArbitrationController::class, '\''getStatistics'\'']);\
$arbitration->get('\''/{id}'\'', [ArbitrationController::class, '\''show'\'']);|' api.php
```

### Alternatif Nano ile:
```bash
nano api.php
```

Bulun:
```php
$arbitration->get('/{id}', [ArbitrationController::class, 'show']);
$arbitration->get('/statistics', [ArbitrationController::class, 'getStatistics']);
```

Değiştirin:
```php
$arbitration->get('/statistics', [ArbitrationController::class, 'getStatistics']);
$arbitration->get('/{id}', [ArbitrationController::class, 'show']);
```

## ✅ Test Et
```bash
curl -I https://backend.bgaofis.billurguleraslim.av.tr/api/arbitration/statistics
```

**200 OK** görmelisiniz, 500 hatası olmamalı!

## 🆘 Eğer Bu da Olmazsa
cPanel/file manager giriş yapın:
1. `/home/haslim/public_html/bgaofis.billurguleraslim.av.tr/backend/routes/api.php`
2. Dosyayı indirin
3. Yukarıdaki düzeltmeyi yapın
4. Upload edin

---
**Bu en basit yöntemdir. İşe yarayacak!**

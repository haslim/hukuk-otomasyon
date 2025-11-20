# Arabuluculuk Ücret Hesaplama Menü Entegrasyonu

## ✅ Tamamlanan Çalışmalar

### 1. Mevcut Durum Analizi
- **Menü Sistemi**: Hiyerarşik menü yapısı (parent-child ilişkileri)
- **Ücret Hesaplama**: Tam fonksiyonellik mevcut
- **API Routes**: `/api/mediation-fees` route grubu hazır
- **Controller**: MediationFeeController tüm metodlarla tamamlanmış

### 2. Önceden Hazırlanmış SQL Script
`backend/mediation-fee-menu-update.sql` dosyası zaten mevcut ve aşağıdaki menü öğelerini içerir:

#### Arabuluculuk Menüsü Altında Eklenecek Öğeler:
- `/mediation/fee-calculator` → "Ücret Hesaplama" (sort_order: 5)
- `/mediation/fee-history` → "Hesaplama Geçmişi" (sort_order: 6)

#### Finans Menüsü Altına Eklenecek Öğeler:
- `/invoices` → "Faturalar" (sort_order: 7)
- `/invoices/create` → "Yeni Fatura" (sort_order: 1)
- `/invoices/list` → "Fatura Listesi" (sort_order: 2)
- `/invoices/stats` → "Fatura İstatistikleri" (sort_order: 3)

## 🔧 Uygulama Adımları

### Adım 1: SQL Script'i Çalıştırma
Mevcut SQL script'i veritabanında çalıştırılması gerekiyor:

```sql
-- Script içeriği: backend/mediation-fee-menu-update.sql
-- Bu script menü öğelerini ve rollerin yetkilerini ekler
```

### Adım 2: Veritabanı Bağlantı Kontrolü
Veritabanı bağlantı bilgileri `.env` dosyasında:
```env
DB_HOST=localhost
DB_DATABASE=haslim_bgofis
DB_USERNAME=haslim_bgofis
DB_PASSWORD=Fener1907****
```

**Not**: Bağlantı sorunu yaşıyorsanız, şu kontrolü yapın:
1. MySQL/MariaDB servisinin çalıştığından emin olun
2. `localhost` yerine `127.0.0.1` deneyin
3. Veritabanı kullanıcı yetkilerini kontrol edin

### Adım 3: Script Çalıştırma Komutları

#### Seçenek A: Doğrudan MySQL CLI
```bash
mysql -u haslim_bgofis -p haslim_bgofis < mediation-fee-menu-update.sql
```

#### Seçenek B: PHP Script (Bağlantı Çalışırsa)
```bash
cd backend
php simple-menu-update.php
```

#### Seçenek C: Web Tabanlı
- phpMyAdmin veya benzeri araçla SQL dosyasını içe aktarın

## 📋 Mevcut API Endpoint'leri

### Ücret Hesaplama:
- `POST /api/mediation-fees/calculate` - Ücret hesaplama
- `GET /api/mediation-fees` - Hesaplama listesi (filtreleme ile)
- `GET /api/mediation-fees/tariffs` - Tarife özeti
- `POST /api/mediation-fees` - Hesaplama kaydet
- `GET /api/mediation-fees/{id}` - Hesaplama detayı
- `DELETE /api/mediation-fees/{id}` - Hesaplama sil
- `POST /api/mediation-fees/{id}/create-invoice` - Hesaplamadan fatura oluştur

### Fatura Yönetimi:
- `GET /api/invoices` - Fatura listesi
- `POST /api/invoices` - Yeni fatura
- `GET /api/invoices/{id}` - Fatura detayı
- `PUT /api/invoices/{id}` - Fatura güncelle
- `DELETE /api/invoices/{id}` - Fatura sil
- `POST /api/invoices/{id}/payments` - Ödeme ekle
- `PATCH /api/invoices/{id}/status` - Durum güncelle
- `GET /api/invoices/{id}/pdf` - PDF oluştur
- `POST /api/invoices/{id}/send` - Fatura gönder

## 🎯 Frontend Entegrasyonu

### Menü Yapısı Olması Gereken:
```
Arabuluculuk
├── ...
├── Ücret Hesaplama (path: /mediation/fee-calculator)
└── Hesaplama Geçmişi (path: /mediation/fee-history)

Finans
├── Nakit Akışı
├── Faturalar (path: /invoices)
│   ├── Yeni Fatura (path: /invoices/create)
│   ├── Fatura Listesi (path: /invoices/list)
│   └── Fatura İstatistikleri (path: /invoices/stats)
```

### Frontend Route'ları:
```javascript
// Mediation Fee Calculator
'/mediation/fee-calculator' -> MediationFeeCalculatorPage
'/mediation/fee-history' -> MediationFeeHistoryPage

// Invoice Management
'/invoices' -> InvoiceListPage
'/invoices/create' -> InvoiceCreatePage
'/invoices/list' -> InvoiceListPage
'/invoices/stats' -> InvoiceStatsPage
'/invoices/:id' -> InvoiceDetailPage
```

## 🔐 Yetkilendirme

SQL script'i otomatik olarak şu rollere erişim verir:
- `administrator` - Tüm özelliklere erişim
- `lawyer` - Tüm hesaplama ve fatura özelliklerine erişim

## 📊 Özelliklerin Açıklaması

### Ücret Hesaplama Özellikleri:
1. **Standart Hesaplama**: 6325 sayılı Kanuna göre
2. **Ticari Hesaplama**: Ticari uyuşmazlıklar için
3. **Acil Hesaplama**: Acil arabuluculuk için (%2 oranlı)
4. **KDV Hesaplama**: Otomatik KDV hesaplama
5. **Taraf Sayısı**: Tekil veya toplam ücret hesaplama

### Fatura Yönetimi Özellikleri:
1. **Otomatik Fatura**: Hesaplamadan fatura oluşturma
2. **Ödeme Takibi**: Ödemeleri kaydetme ve takip
3. **Durum Yönetimi**: Taslak, gönderildi, ödendi, gecikmiş durumları
4. **PDF Raporlama**: Fatura PDF'i oluşturma
5. **İstatistikler**: Finansal raporlar ve grafikler

## ⚠️ Önemli Notlar

1. **Veritabanı Bağlantısı**: SQL script'i çalıştırmadan önce veritabanı bağlantısını test edin
2. **Yedekleme**: Değişikliklerden önce veritabanı yedeği alın
3. **Yetkiler**: Yeni menü öğelerinin doğru rollere atandığını kontrol edin
4. **Frontend**: Menü öğelerinin frontend'de göründüğünden emin olun

## 🚀 Sonraki Adımlar

1. SQL script'i veritabanında çalıştırın
2. Menü öğelerinin veritabanına eklendiğini doğrulayın
3. Frontend routing'larını güncelleyin
4. Sayfa component'lerini oluşturun
5. Test yapın ve tüm akışları kontrol edin

---

**Bu rehber, arabuluculuk ücret hesaplama sisteminin menü entegrasyonu için gerekli tüm adımları içermektedir. SQL script zaten hazır olduğunda, sadece çalıştırılması yeterlidir.**

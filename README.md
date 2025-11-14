# BGAofis Hukuk Otomasyon Sistemi

BGAofis Hukuk Otomasyon Sistemi, modern hukuk büroları için tasarlanmış kapsamlı bir yönetim çözümüdür. Bu sistem, dava yönetimi, müvekkil takibi, doküman yönetimi, finansal kontrol ve iş akışı otomasyonu gibi temel işlevleri bir araya getirir.

## 🏗️ Proje Yapısı

```
hukuk-otomasyon/
├── backend/                    # PHP 8.2+ Slim Framework tabanlı API
│   ├── app/                    # Uygulama mantığı
│   │   ├── Controllers/        # API kontrolcüleri
│   │   ├── Models/             # Veri modelleri
│   │   ├── Services/           # İş mantığı servisleri
│   │   ├── Repositories/      # Veri erişim katmanı
│   │   └── Middleware/        # Ara yazılım katmanı
│   ├── config/                 # Konfigürasyon dosyaları
│   ├── database/               # Veritabanı migrasyonları ve seed'leri
│   ├── public/                 # Web erişim noktası
│   └── routes/                 # API rotaları
├── frontend/                   # React + TypeScript + Vite tabanlı arayüz
│   ├── src/                   # Kaynak kod
│   │   ├── components/        # React bileşenleri
│   │   ├── pages/             # Sayfa bileşenleri
│   │   ├── api/               # API istemcisi
│   │   └── hooks/             # Custom React hooks
│   └── public/                # Statik dosyalar
└── docs/                      # Dokümantasyon
```

## 🚀 Özellikler

### Temel Özellikler
- **Kullanıcı Yönetimi**: Rol bazlı yetkilendirme sistemi
- **Dava Yönetimi**: Davaların takibi, duruşma yönetimi
- **Müvekkil Yönetimi**: Müvekkil bilgileri ve iletişim yönetimi
- **Doküman Yönetimi**: Doküman yükleme, versiyonlama, paylaşım
- **Finansal Yönetim**: Gelir/gider takibi, faturalandırma
- **İş Akışı Otomasyonu**: Otomatik görev atama ve takip
- **Bildirim Sistemi**: E-posta ve sistem bildirimleri
- **Arama ve Raporlama**: Gelişmiş arama ve raporlama özellikleri

### Teknik Özellikler
- **Backend**: PHP 8.2+, Slim Framework, MySQL/MariaDB
- **Frontend**: React 18, TypeScript, Vite, Tailwind CSS
- **Kimlik Doğrulama**: JWT tabanlı token sistemi
- **API**: RESTful API tasarımı
- **Veritabanı**: MySQL/MariaDB ile ilişkisel veri modeli
- **Deployment**: name.com hosting için optimize edilmiş

## 📋 Gereksinimler

### Sistem Gereksinimleri
- **PHP**: 8.2 veya üzeri
- **Veritabanı**: MySQL 8.0+ veya MariaDB 10.6+
- **Web Sunucu**: Apache (mod_rewrite ile) veya Nginx
- **Node.js**: 18.0+ (frontend geliştirme için)

### PHP Eklentileri
- php-mysql
- php-json
- php-mbstring
- php-openssl
- php-curl
- php-xml
- php-zip

## 🛠️ Kurulum

### Backend Kurulumu

1. **Veritabanı Oluşturma**
   ```sql
   CREATE DATABASE bgaofis CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Composer Dependencies**
   ```bash
   cd backend
   composer install
   ```

3. **Ortam Konfigürasyonu**
   ```bash
   cp .env.example .env
   # .env dosyasını düzenleyin
   ```

4. **Veritabanı Migrasyonları**
   ```bash
   php database/migrate.php
   php database/seed.php  # Opsiyonel: örnek veriler
   ```

### Frontend Kurulumu

1. **Node.js Dependencies**
   ```bash
   cd frontend
   npm install
   ```

2. **Geliştirme Sunucusu**
   ```bash
   npm run dev
   ```

3. **Production Build**
   ```bash
   npm run build
   ```

## 🚀 Deployment

### name.com Hosting için Otomatik Deployment

Bu proje, name.com hosting için Git tabanlı otomatik deployment çözümü içerir:

1. **GitHub Repository Ayarları**
   - Repository'yi GitHub'a pushlayın
   - GitHub Actions workflow'larını etkinleştirin

2. **name.com Panel Ayarları**
   - FTP/SFTP bilgilerini alın
   - Web root dizinini yapılandırın

3. **Otomatik Deployment**
   - GitHub'a kod push ettiğinizde otomatik olarak name.com'a deploy edilir
   - Frontend build işlemi otomatik olarak çalışır
   - Backend dependencies ve migrasyonlar otomatik yüklenir

Detaylı deployment guide için: [DEPLOYMENT_COMPLETE_GUIDE.md](DEPLOYMENT_COMPLETE_GUIDE.md)

## 📚 API Dokümantasyonu

API endpoint'leri ve kullanımı için:
- [Backend API Dokümantasyonu](backend/README_DEPLOYMENT.md)

### Örnek API Kullanımı

```javascript
// Kullanıcı girişi
POST /api/auth/login
{
  "email": "user@example.com",
  "password": "password"
}

// Davaları listele
GET /api/cases
Headers: Authorization: Bearer {token}

// Yeni dava oluştur
POST /api/cases
{
  "title": "Dava Başlığı",
  "client_id": 1,
  "case_number": "2024/123",
  "description": "Dava açıklaması"
}
```

## 🔧 Geliştirme

### Backend Geliştirme

```bash
# Geliştirme sunucusu başlat
cd backend
php -S localhost:8080 -t public

# Migrasyon çalıştır
php database/migrate.php

# Seeder çalıştır
php database/seed.php
```

### Frontend Geliştirme

```bash
# Geliştirme sunucusu
cd frontend
npm run dev

# Type checking
npm run build

# Preview
npm run preview
```

## 🧪 Testler

```bash
# Backend testleri
cd backend
vendor/bin/phpunit

# Frontend testleri
cd frontend
npm test
```

## 📝 Lisans

Bu proje MIT lisansı altında dağıtılmaktadır.

## 🤝 Katkıda Bulunma

1. Repository'yi fork edin
2. Feature branch oluşturun (`git checkout -b feature/AmazingFeature`)
3. Değişikliklerinizi commit edin (`git commit -m 'Add some AmazingFeature'`)
4. Branch'e push yapın (`git push origin feature/AmazingFeature`)
5. Pull Request oluşturun

## 📞 Destek

- **Proje Dokümantasyonu**: [docs/](docs/)
- **Deployment Sorunları**: [DEPLOYMENT_COMPLETE_GUIDE.md](DEPLOYMENT_COMPLETE_GUIDE.md)
- **name.com Destek**: https://www.name.com/support

---

**BGAofis Hukuk Otomasyon Sistemi** - Modern hukuk büroları için güçlü yönetim çözümü.

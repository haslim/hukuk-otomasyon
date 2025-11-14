# name.com Git Tabanlı Otomatik Deployment Rehberi

Bu rehber, BGAofis Hukuk Otomasyon Sistemi'nin name.com hosting üzerinde Git tabanlı otomatik deployment için nasıl yapılandırılacağını adım adım açıklar.

## 📋 İçerik Tablosu

1. [Gereksinimler](#gereksinimler)
2. [name.com Hosting Ayarları](#namecom-hosting-ayarları)
3. [GitHub Repository Yapılandırması](#github-repository-yapılandırması)
4. [Webhook Kurulumu](#webhook-kurulumu)
5. [Otomatik Deployment Akışı](#otomatik-deployment-akışı)
6. [Manuel Deployment Script'leri](#manuel-deployment-scriptleri)
7. [Sorun Giderme](#sorun-giderme)

## 🚀 Gereksinimler

### name.com Hosting Gereksinimleri
- **PHP**: 8.2 veya üzeri
- **MySQL/MariaDB**: 8.0+ veya 10.6+
- **Web Sunucu**: Apache (mod_rewrite ile) veya Nginx
- **SSH Erişimi**: Terminal komutları çalıştırmak için
- **FTP/SFTP**: Dosya transferi için

### Yerel Geliştirme Ortamı
- **Git**: Version kontrol için
- **Node.js**: 18+ (frontend geliştirme için)
- **PHP**: 8.2+ (backend geliştirme için)
- **Composer**: PHP dependency management

## 🖥️ name.com Hosting Ayarları

### 1. cPanel'e Giriş
1. name.com hesabınıza giriş yapın
2. Hosting kontrol paneline (cPanel) gidin
3. "Advanced" bölümüne tıklayın

### 2. SSH Erişimi Aktifleştirme
1. "SSH Access" veya "Terminal" seçeneğini bulun
2. SSH erişimini aktifleştirin
3. SSH anahtarları oluşturun veya mevcut anahtarları yükleyin

### 3. Veritabanı Oluşturma
1. "MySQL Databases" veya "MariaDB" seçeneğine gidin
2. Yeni veritabanı oluşturun:
   - Database name: `bgaofis_production`
   - Username: `bgaofis_user`
   - Password: Güçlü bir şifre oluşturun
3. Kullanıcıya veritabanı için tam yetki verin

### 4. PHP Versiyonu Ayarlama
1. "Select PHP Version" veya "MultiPHP Manager" seçeneğine gidin
2. PHP 8.2+ seçin
3. Gerekli eklentilerin aktif olduğundan emin olun:
   - php-mysql
   - php-json
   - php-mbstring
   - php-openssl
   - php-curl
   - php-xml
   - php-zip

### 5. Dosya Yollarını Belirleme
Hosting panelinizde dosya yollarını not alın:
- **Home Directory**: `/home/username/`
- **Public HTML**: `/home/username/public_html/`
- **Backend Directory**: `/home/username/public_html/backend/`
- **Frontend Directory**: `/home/username/public_html/`

## 📁 GitHub Repository Yapılandırması

### 1. Repository Oluşturma
1. GitHub hesabınızda yeni repository oluşturun
2. Repository adı: `hukuk-otomasyon`
3. Public veya Private seçimini yapın
4. "Initialize with README" seçeneğini işaretleyin

### 2. Secrets Ayarlama
GitHub repository'nizde以下 secrets'leri ekleyin:

1. Repository'ye gidin
2. "Settings" → "Secrets and variables" → "Actions"
3. "New repository secret" tıklayın ve以下 bilgileri ekleyin:

| Secret Name | Description | Example Value |
|-------------|-------------|---------------|
| `FTP_SERVER` | FTP sunucu adresi | `ftp.yourdomain.com` |
| `FTP_USERNAME` | FTP kullanıcı adı | `username@yourdomain.com` |
| `FTP_PASSWORD` | FTP şifresi | `your-ftp-password` |
| `FTP_BACKEND_DIR` | Backend dizini | `/public_html/backend/` |
| `FTP_FRONTEND_DIR` | Frontend dizini | `/public_html/` |
| `DOMAIN_NAME` | Alan adı | `yourdomain.com` |
| `FRONTEND_API_URL` | Frontend API URL | `https://yourdomain.com/backend/api` |

### 3. GitHub Actions Aktifleştirme
1. Repository'de "Actions" sekmesine gidin
2. "I understand my workflows, go ahead and enable them" tıklayın
3. Workflow'ların aktif olduğundan emin olun

## 🪝 Webhook Kurulumu

### 1. Webhook Handler Dosyasını Yükleme
1. `webhook-handler.php` dosyasını sunucunuza yükleyin
2. Dosyayı public erişilebilir bir dizine koyun:
   ```
   /home/username/public_html/webhook-handler.php
   ```

### 2. Webhook Handler Konfigürasyonu
1. Webhook handler dosyasını düzenleyin:
   ```php
   // Environment variables
   $_ENV['WEBHOOK_SECRET'] = 'your-github-webhook-secret';
   $_ENV['REPO_NAME'] = 'hukuk-otomasyon';
   $_ENV['WEB_ROOT'] = '/home/username/public_html';
   ```

2. Dosya izinlerini ayarlayın:
   ```bash
   chmod 755 /home/username/public_html/webhook-handler.php
   chmod 666 /home/username/public_html/webhook.log
   ```

### 3. GitHub Webhook Oluşturma
1. GitHub repository'nizde "Settings" → "Webhooks" gidin
2. "Add webhook" tıklayın
3.以下 bilgileri girin:
   - **Payload URL**: `https://yourdomain.com/webhook-handler.php`
   - **Content type**: `application/json`
   - **Secret**: Güçlü bir secret oluşturun
   - **Which events**: "Just the `push` event" seçin
   - **Active**: İşaretleyin

4. "Add webhook" tıklayın

## 🔄 Otomatik Deployment Akışı

### Push Sırasında Ne Olur?
1. **Kod GitHub'a pushlanır**
2. **GitHub Actions tetiklenir**
3. **Backend deployment**:
   - Composer dependencies yüklenir
   - Dosyalar FTP ile sunucuya upload edilir
   - Veritabanı migrasyonları çalıştırılır
4. **Frontend deployment**:
   - Node.js dependencies yüklenir
   - Proje build edilir
   - Build dosyaları sunucuya upload edilir
   - .htaccess dosyası oluşturulur
5. **Deployment bildirimi** GitHub'da gösterilir

### Webhook ile Deployment
1. **GitHub push algılanır**
2. **Signature doğrulanır**
3. **Git pull çalıştırılır**
4. **Backend ve frontend otomatik build edilir**
5. **Dosyalar web root'a kopyalanır**
6. **Deployment log'ları kaydedilir**

## 📜 Manuel Deployment Script'leri

### Backend Deployment Script'i
```bash
# Backend deployment
chmod +x deploy-backend.sh
./deploy-backend.sh
```

### Frontend Deployment Script'i
```bash
# Frontend deployment
chmod +x deploy-frontend.sh
./deploy-frontend.sh
```

### Ortam Değişkenleri
Script'leri çalıştırmadan önce ortam değişkenlerini ayarlayın:
```bash
export FTP_SERVER="ftp.yourdomain.com"
export FTP_USERNAME="username@yourdomain.com"
export FTP_PASSWORD="your-ftp-password"
export FTP_BACKEND_DIR="/public_html/backend/"
export FTP_FRONTEND_DIR="/public_html/"
```

## 🔧 Sorun Giderme

### Yaygın Sorunlar ve Çözümleri

#### 1. FTP Bağlantı Hatası
**Sorun**: FTP connection failed
**Çözüm**:
- FTP bilgilerini kontrol edin
- name.com panelinde FTP erişimini aktifleştirin
- Firewall ayarlarını kontrol edin

#### 2. Composer Install Hatası
**Sorun**: Composer install failed
**Çözüm**:
- PHP versiyonunu kontrol edin (8.2+ olmalı)
- Memory limit'i artırın: `php -d memory_limit=512M composer install`
- Disk alanını kontrol edin

#### 3. Frontend Build Hatası
**Sorun**: npm run build failed
**Çözüm**:
- Node.js versiyonunu kontrol edin (18+ olmalı)
- `package.json' dosyasını kontrol edin
- `node_modules`'i temizleyip yeniden kurun: `rm -rf node_modules && npm install`

#### 4. Veritabanı Bağlantı Hatası
**Sorun**: Database connection failed
**Çözüm**:
- Veritabanı bilgilerini kontrol edin
- Veritabanı kullanıcısının yetkilerini kontrol edin
- Veritabanı sunucusunun çalıştığını doğrulayın

#### 5. Permission Hataları
**Sorun**: File permission denied
**Çözüm**:
```bash
# Doğru izinleri ayarlayın
chmod 755 /home/username/public_html/backend/
chmod 644 /home/username/public_html/backend/*.php
chmod 755 /home/username/public_html/backend/logs/
chmod 755 /home/username/public_html/backend/uploads/
```

#### 6. Webhook Çalışmıyor
**Sorun**: Webhook not triggering
**Çözüm**:
- Webhook URL'sini kontrol edin
- Secret key'i doğrulayın
- Webhook log'larını kontrol edin: `tail -f webhook.log`

### Log'ları Kontrol Etme

#### GitHub Actions Log'ları
1. Repository'de "Actions" sekmesine gidin
2. İlgili workflow'u seçin
3. Detaylı log'ları görüntüleyin

#### Sunucu Log'ları
```bash
# Webhook log'ları
tail -f /home/username/public_html/webhook.log

# Apache log'ları
tail -f /home/username/logs/error_log

# PHP error log'ları
tail -f /home/username/logs/php_errors.log
```

### Deployment Durumunu Kontrol Etme

#### Manuel Kontrol
```bash
# Deployment info dosyasını kontrol et
cat /home/username/public_html/deployment-info.json

# Backend çalışıyor mu?
curl -I https://yourdomain.com/backend/api/

# Frontend çalışıyor mu?
curl -I https://yourdomain.com/
```

## 📞 Destek

### name.com Destek
- **Web**: https://www.name.com/support
- **Email**: support@name.com
- **Phone**: +1-720-249-2374

### Proje Destek
- **Documentation**: [README.md](README.md)
- **Issues**: GitHub repository'de "Issues" sekmesi
- **Deployment Guide**: [DEPLOYMENT_COMPLETE_GUIDE.md](DEPLOYMENT_COMPLETE_GUIDE.md)

## 🎯 Başarılı Deployment Kontrol Listesi

Deployment sonrası以下 kontrolleri yapın:

- [ ] Web sitesi açılıyor mu?
- [ ] Backend API çalışıyor mu?
- [ ] Veritabanı bağlantısı başarılı mı?
- [ ] Kullanıcı girişi yapılabilir mi?
- [ ] Dosya yükleme çalışıyor mu?
- [ ] Bildirimler gidiyor mu?
- [ ] Mobil uyumlu mu?
- [ ] SSL sertifikası çalışıyor mu?
- [ ] Log'ları kontrol ettiniz mi?

---

**Bu rehber, BGAofis Hukuk Otomasyon Sistemi'nin name.com hosting üzerinde sorunsuz deployment'ı için hazırlanmıştır. Sorularınız için destek kanallarını kullanabilirsiniz.**
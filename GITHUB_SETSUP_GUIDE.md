# GitHub Repository Secrets Kurulum Rehberi

Bu rehber, BGAofis Hukuk Otomasyon projesi için gerekli GitHub Secrets'lerin nasıl ekleneceğini adım adım açıklar.

## 🔐 Gerekli Secrets Listesi

Aşağıdaki secrets'leri GitHub repository'nize eklemeniz gerekmektedir:

| Secret Name | Değeri | Açıklama |
|-------------|--------|-----------|
| `FTP_SERVER` | `ftp.bgaofis.billurguleraslim.av.tr` | FTP sunucu adresi |
| `FTP_USERNAME` | `haslim@bgaofis.billurguleraslim.av.tr` | FTP kullanıcı adı |
| `FTP_PASSWORD` | `Fener1907****` | FTP şifresi |
| `FTP_BACKEND_DIR` | `/public_html/backend/` | Backend dizin yolu |
| `FTP_FRONTEND_DIR` | `/public_html/` | Frontend dizin yolu |
| `DOMAIN_NAME` | `bgaofis.billurguleraslim.av.tr` | Alan adı |
| `FRONTEND_API_URL` | `https://bgaofis.billurguleraslim.av.tr/backend/api` | Frontend API URL |

## 📋 Adım Adım Kurulum

### 1. GitHub Repository'ye Gidin
1. https://github.com/haslim/hukuk-otomasyon.git adresine gidin
2. Repository'ye giriş yapın

### 2. Secrets Ayarlarına Gidin
1. Repository ana sayfasında **"Settings"** sekmesine tıklayın
2. Sol menüden **"Secrets and variables"** → **"Actions"** seçeneğine tıklayın
3. **"New repository secret"** butonuna tıklayın

### 3. Secrets'leri Tek Tek Ekleyin

#### FTP_SERVER Secret'i
1. **Name**: `FTP_SERVER`
2. **Secret**: `ftp.bgaofis.billurguleraslim.av.tr`
3. **Add secret** butonuna tıklayın

#### FTP_USERNAME Secret'i
1. **Name**: `FTP_USERNAME`
2. **Secret**: `haslim@bgaofis.billurguleraslim.av.tr`
3. **Add secret** butonuna tıklayın

#### FTP_PASSWORD Secret'i
1. **Name**: `FTP_PASSWORD`
2. **Secret**: `Fener1907****`
3. **Add secret** butonuna tıklayın

#### FTP_BACKEND_DIR Secret'i
1. **Name**: `FTP_BACKEND_DIR`
2. **Secret**: `/public_html/backend/`
3. **Add secret** butonuna tıklayın

#### FTP_FRONTEND_DIR Secret'i
1. **Name**: `FTP_FRONTEND_DIR`
2. **Secret**: `/public_html/`
3. **Add secret** butonuna tıklayın

#### DOMAIN_NAME Secret'i
1. **Name**: `DOMAIN_NAME`
2. **Secret**: `bgaofis.billurguleraslim.av.tr`
3. **Add secret** butonuna tıklayın

#### FRONTEND_API_URL Secret'i
1. **Name**: `FRONTEND_API_URL`
2. **Secret**: `https://bgaofis.billurguleraslim.av.tr/backend/api`
3. **Add secret** butonuna tıklayın

## ⚠️ Güvenlik Uyarıları

### Secret Güvenliği
- **Asla şifreleri doğrudan kod'a eklemeyin**: Her zaman GitHub Secrets kullanın
- **Strong password'ler kullanın**: FTP şifreniz güçlü olmalı
- **Regular değişim**: Şifreleri düzenli aralıklarla değiştirin
- **Access control**: Sadece gerekli kişilerin repository erişimi olmalı

### Environment Variables vs Secrets
- **Repository Secrets**: Hassas bilgiler (şifreler, API anahtarları)
- **Environment Variables**: Genel konfigürasyon (debug mode, feature flags)
- **Best practice**: Hassas bilgiler her zaman Secrets olarak saklanmalı

## 🔍 Doğrulama

### Secrets'lerin Doğru Yapılandırıldığını Kontrol Etme
1. GitHub Actions log'larını kontrol edin
2. Deployment workflow'unun başarılı olup olmadığını görün
3. Hata varsa, secrets'lerin doğru girildiğini kontrol edin

### Test Deployment
```bash
# Test için main branch'e push yapın
git add .
git commit -m "Test deployment with new secrets"
git push origin main
```

## 🚨 Sorun Giderme

### Yaygın Hatalar
1. **"Secret not found" hatası**:
   - Secret adının doğru yazıldığını kontrol edin
   - Case-sensitive olduğunu unutmayın

2. **FTP connection failed** hatası**:
   - FTP bilgilerinin doğru olduğunu kontrol edin
   - name.com panelinde FTP erişimin aktif olduğunu doğrulayın

3. **Permission denied** hatası**:
   - FTP kullanıcısının doğru dizinlere erişim izni olduğunu kontrol edin
   - Dosya izinlerini kontrol edin

### Debug Adımları
1. GitHub Actions sekmesine gidin
2. Başarısız workflow'u tıklayın
3. Adım adım log'ları inceleyin
4. Hata mesajında hangi secret'in sorunlu olduğunu belirleyin

## 📝 Alternatif Yöntem: Environment Variables

Eğer GitHub Secrets kullanmak istemiyorsanız, environment variables kullanabilirsiniz:

```yaml
# .github/workflows/deploy.yml
env:
  FTP_SERVER: ftp.bgaofis.billurguleraslim.av.tr
  FTP_USERNAME: haslim@bgaofis.billurguleraslim.av.tr
  FTP_PASSWORD: Fener1907****
  FTP_BACKEND_DIR: /public_html/backend/
  FTP_FRONTEND_DIR: /public_html/
  DOMAIN_NAME: bgaofis.billurguleraslim.av.tr
  FRONTEND_API_URL: https://bgaofis.billurguleraslim.av.tr/backend/api
```

**⚠️ Uyarı**: Bu yöntem daha az güvenlidir çünkü secrets repository'de açıkça görünür.

## ✅ Kurulum Tamamlandı

Tüm secrets'leri ekledikten sonra:

1. **Deployment test edin**: Main branch'e kod pushlayın
2. **Log'ları kontrol edin**: GitHub Actions sekmesinde workflow durumunu izleyin
3. **Doğrulayın**: Web sitesinin çalıştığını kontrol edin

## 📞 Destek

Eğer sorun yaşarsanız:
- **GitHub Dokümantasyon**: https://docs.github.com/en/actions/security-guides/using-secrets
- **name.com Destek**: https://www.name.com/support
- **Proje Issues**: https://github.com/haslim/hukuk-otomasyon/issues

---

**Önemli**: Bu secrets'leri güvenli bir yerde saklayın ve asla kimseyle paylaşmayın. GitHub Secrets, bu bilgileri güvenli bir şekilde saklamak için tasarlanmıştır.
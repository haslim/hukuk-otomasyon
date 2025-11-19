# Menu System Deployment Guide

## Overview
Bu guide, avukat kullanıcıları için menü kısıtlama sistemini production ortamına kurmak için kullanılır.

## Problem
Avukat kullanıcıları aşağıdaki menüleri görmemeli:
- Kullanıcılar & Roller (`/users`)
- Workflow (`/workflow`) 
- Profilim (`/profile`)

## Çözüm
Rol bazlı dinamik menü yönetim sistemi.

## Deployment Steps

### 1. Veritabanı Migration

**ÖNEMLİ:** Production ortamında `backend/menu-tables-simple.sql` dosyasını manuel çalıştırın.

```sql
-- MySQL/phpMyAdmin veya benzeri araçla bu dosyayı import edin
-- backend/menu-tables-simple.sql
```

Bu dosya foreign key sorunlarını atlamak için tasarlanmıştır ve uygulama seviyesinde veri bütünlüğü sağlar.

Bu dosya şunları yapar:
- `menu_items` ve `menu_permissions` tablolarını oluşturur
- 13 adet menü öğesi ekler
- Administrator rolü için tüm menüleri visible yapar
- Avukat rolü için kısıtlı menüleri ayarlar

### 2. Backend Kontrol

Controller ve API endpoint'leri zaten mevcut:
- `backend/app/Controllers/MenuController.php`
- `backend/routes/api.php` (menu rotaları)
- `backend/app/Models/MenuItem.php`
- `backend/app/Models/MenuPermission.php`

### 3. Frontend Kontrol

Frontend component'leri zaten mevcut:
- `frontend/src/components/Sidebar.tsx` (dinamik menü)
- `frontend/src/pages/MenuManagementPage.tsx` (admin arayüzü)
- `frontend/src/services/MenuService.ts` (API service)
- `frontend/src/router/AppRoutes.tsx` (menü yönetimi rotası)

### 4. Test

Manuel test için:
1. Administrator hesapla giriş yapın
2. `/menu-management` sayfasına gidin
3. Avukat rolü için menü izinlerini düzenleyin
4. Avukat hesapla giriş yapın
5. Sidebar'da kısıtlı menülerin olmadığını doğrulayın

## Beklenen Sonuçlar

### Administrator Menüsü (Tümü Visible):
1. Dashboard
2. Profilim
3. Dosyalar
4. Arabuluculuk
5. Müvekkiller
6. Kasa
7. Takvim
8. Kullanıcılar & Roller
9. Dokümanlar
10. Bildirimler
11. Workflow
12. Menü Yönetimi
13. Arama

### Avukat Menüsü (Kısıtlı):
1. Dashboard ✓
2. Profilim ❌ (Gizli)
3. Dosyalar ✓
4. Arabuluculuk ✓
5. Müvekkiller ✓
6. Kasa ✓
7. Takvim ✓
8. Kullanıcılar & Roller ❌ (Gizli)
9. Dokümanlar ✓
10. Bildirimler ✓
11. Workflow ❌ (Gizli)
12. Menü Yönetimi ❌ (Sadece admin)
13. Arama ✓

## API Endpoint'leri

### GET /api/menu/my
Kullanıcının rolüne göre menü listesi döner
```json
[
  {
    "path": "/cases",
    "label": "Dosyalar",
    "icon": "folder",
    "sort_order": 3
  }
]
```

### GET /api/menu
Tüm menü öğeleri (admin only)

### PUT /api/menu/roles/{id}/permissions
Rol menü izinlerini günceller (admin only)

## Troubleshooting

### Eğer tablolar oluşmazsa:
1. MySQL versiyonunu kontrol edin (5.7+)
2. UUID_TO_BIN fonksiyonunun desteklendiğini doğrulayın
3. FOREIGN_KEY_CHECKS ayarını kontrol edin

### Eğer menüler görünmezse:
1. `menu_items` ve `menu_permissions` tablolarındaki veriyi kontrol edin
2. User-Role ilişkisini kontrol edin
3. API endpoint'lerini test edin

### Eğer admin panel çalışmazsa:
1. Yetki kontrolünü yapın
2. Route tanımlarını kontrol edin
3. Frontend build sürecini kontrol edin

## Security Notes

- Menü erişimi sadece backend kontrol edilmeli
- Frontend doğrulama sadece UI için
- Admin yetkileri sıkı kontrol edilmeli

## Production Checklist

- [ ] SQL migration çalıştırıldı
- [ ] Tablolar oluşturuldu
- [ ] Menu seed verisi eklendi
- [ ] API endpoint'leri çalışıyor
- [ ] Frontend build edildi
- [ ] Admin test edildi
- [ ] Avukat test edildi
- [ ] Menü izinleri doğrulandı

## Support

Sorun yaşarsanız:
1. Backend loglarını kontrol edin
2. Browser konsolunu kontrol edin
3. Network tab'da API çağrılarını doğrulayın

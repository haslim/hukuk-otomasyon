# BGAofis Hukuk Bürosu Otomasyon Sistemi

Tam kapsamlı hukuk bürosu yönetimi için PHP 8 + Slim tabanlı backend ve React + Vite frontend iskeleti.

## Backend

- Slim 4 HTTP katmanı
- Eloquent (Illuminate Database) ile veri modeli
- Rol > Yetki > Eylem middleware zinciri
- KVKK uyumlu `audit_logs` middleware opsiyonu
- Workflow, bildirim, doküman versiyonlama, kasa, gelişmiş arama servisleri

### Çalıştırma

```bash
cd backend
cp .env.example .env
composer install
php database/migrate.php
php -S localhost:8080 -t public
```

> Not: Composer/PHP kurulumunu yerelde sağlayın.

## Frontend

- React + Vite + Tailwind taslağı
- React Query ve Axios ile API istemcisi
- Dashboard, CRM, Dosya, Workflow, Doküman, Finans, Bildirim ve Arama ekranları

### Çalıştırma

```bash
cd frontend
npm install
npm run dev
```

## Modüller

- **CRM**: Müvekkil CRUD
- **Dosya Yönetimi**: Dava/icra/arabuluculuk kayıtları, workflow bağlama
- **Workflow**: Şablon ve adım listeleri
- **Dokümanlar**: Versiyonlama + fulltext arama
- **Kasa**: Gelir/gider raporu
- **Bildirim**: pending_notifications kuyruğu
- **Dashboard & Arama**: Kritik göstergeler + tam metin sonuçları

## Güvenlik

- JWT tabanlı oturum
- Rol/izin kontrolü
- Audit middleware ile kritik API logları

## TODO

- Dosya upload adaptörü
- Cron tabanlı bildirim worker'ı
- Testler ve CI scriptleri

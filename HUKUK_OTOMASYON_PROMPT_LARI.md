# Hukuk Otomasyon Sistemi Prompt'ları

Bu dosyada BGAofis Hukuk Otomasyon Sistemi oluşturmak için farklı senaryolara göre hazırlanmış prompt'lar bulunmaktadır.

---

## 🔰 1. TAM PROJE OLUŞTURMA PROMPT'U (ONE-SHOT)

```
Bana modern bir hukuk bürosu otomasyon sistemi sıfırdan oluştur.

🏗️ TEKNİK ALTYAPI:
- Backend: PHP 8.2+ Slim Framework, MySQL/MariaDB veritabanı
- Frontend: React 18 + TypeScript + Vite + Tailwind CSS
- Authentication: JWT token tabanlı güvenlik
- API: RESTful JSON endpoint'leri
- Deployment: name.com shared hosting uyumlu

📋 ZORUNLU MODÜLLER:

1. KULLANICI YÖNETİMİ
- Rol bazlı yetkilendirme: ADMIN, AVUKAT, STAJYER, SEKRETERYA, FINANS
- Permission sistemi: CASE_VIEW_ALL, CASE_EDIT, CASH_VIEW, DOC_UPLOAD vb.
- KVKK uyumlu audit_log sistemi
- IP bazlı erişim kontrolü

2. MÜVEKKİL/CRM MODÜLÜ
- Gerçek ve tüzel kişi yönetimi
- İletişim bilgileri (telefon, e-posta, adres)
- Etiketleme sistemi (VIP, kurumsal, bireysel vb.)
- Not alma ve dosya bağlantısı
- TCKN/VKN doğrulama

3. DAVA DOSYA YÖNETİMİ
- Dava türleri: İcra, Arabuluculuk, Danışmanlık, Ticaret, Ceza vb.
- Esas no, dosya no, konu, talepler
- Taraflar (müvekkil, karşı taraf, vekiller)
- Duruşma takvimi ve kararlar
- Masraf yönetimi (harç, vekalet ücreti)
- Görev atama ve takip

4. ARABULUCULUK MODÜLÜ
- Başvuru formu ve bilgi girişi
- Taraflar ve temsilciler
- Toplantı tarihleri ve notları
- Anlaşma/Anlaşmama sonuçları
- OTOMATİK ŞABLON ÜRETİMİ:
  * Başvuru formu (PDF)
  * Son tutanak (Word/PDF)
  * Anlaşma metni (Word/PDF)

5. FİNANS/KASA MODÜLÜ
- Tahsilat takibi (avans, vekalet ücreti)
- Masraf girişi (harç, uzman ücreti, posta vb.)
- Dosya bazlı finansal durum
- Aylık gelir-gider raporları
- Filtreleme (tarih aralığı, dosya, müvekkil)
- Kasa hareketleri

6. DOKÜMAN YÖNETİMİ
- Dosya bazlı evrak listesi
- Versiyonlama sistemi (v1, v2, v3...)
- Yükleyen/indiren/silen kullanıcı log'u
- Desteklenen formatlar: PDF, DOCX, XLSX, JPG, PNG
- Doküman kategorizasyonu
- Erişim izinleri

7. GÖREV & TAKVİM SİSTEMİ
- Görev atama (avukata, stajyere)
- Son tarih uyarıları (E-posta + sistem bildirimi)
- Takvim entegrasyonu (duruşmalar + görevler birleşik)
- Önceliklendirme (acil, normal, düşük)
- Görev durum takibi (yapıldı, ertelendi, iptal)

8. WORKFLOW/CHECKLIST MODÜLÜ
- İş türüne göre şablonlar:
  * İşçi alacağı davası (12 adım)
  * Arabuluculuk süreci (8 adım)
  * İcra takibi açılışı (6 adım)
- Her adım için açıklama ve zorunluluk
- Dashboard: "Eksik adımı olan dosyalar"
- İlerleme yüzde takibi

9. BİLDİRİM SİSTEMİ
- Duruşma yaklaşınca (3 gün, 1 gün önce)
- Temyiz/istinaf kritik süreleri
- Görev son tarihleri
- E-posta cron-job sistemi
- pending_notifications tablosu

10. DASHBOARD & RAPORLAMA
- Bugünkü duruşmalar
- Gelecek 7 gün kritik süreler
- Açık görevlerim
- Bu ay tahsilat/masraf grafiği
- En aktif müvekkiller
- Workflow tamamlanma oranları

11. GELİŞMİŞ ARAMA
- Müvekkil, karşı taraf, vekil araması
- TCKN/VKN ile hızlı arama
- Dosya no, taraf isimleri
- Doküman içeriği (MySQL FULLTEXT)
- Notlarda metin arama
- Filtreleme kombinasyonları

12. BİLGİ BANKASI (WIKI)
- İçtihat linkleri ve notları
- Mahkeme/bilirkişi bilgileri
- Etiketli bilgi havuzu
- Arama ve kategorizasyon

🗄️ VERİTABANI ŞEMASI:
```sql
users (id, name, email, password, role_id, created_at, updated_at)
roles (id, name, description, created_at, updated_at)
permissions (id, name, description, created_at, updated_at)
user_roles (user_id, role_id)
role_permissions (role_id, permission_id)

clients (id, type, name_surname, tax_number, tax_office, phone, email, address, notes, tags, created_at, updated_at)
cases (id, case_number, case_type, title, description, client_id, status, created_at, updated_at)
case_parties (id, case_id, party_type, name_surname, tax_number, lawyer_id, created_at, updated_at)
hearings (id, case_id, hearing_date, hearing_type, location, description, result, created_at, updated_at)
tasks (id, case_id, assigned_to, title, description, priority, due_date, status, created_at, updated_at)
documents (id, case_id, client_id, title, file_path, file_type, file_size, uploaded_by, created_at, updated_at)
document_versions (id, document_id, version_number, file_path, uploaded_by, created_at)

arbitration_applications (id, case_id, application_date, parties, subject, status, created_at, updated_at)
mediation_meetings (id, application_id, meeting_date, participants, notes, outcome, created_at, updated_at)

financial_transactions (id, case_id, client_id, type, amount, description, transaction_date, created_by, created_at, updated_at)
invoices (id, case_id, client_id, invoice_number, total_amount, status, issue_date, due_date, created_at, updated_at)
invoice_items (id, invoice_id, description, quantity, unit_price, total, created_at)
invoice_payments (id, invoice_id, amount, payment_date, payment_method, created_at)

workflow_templates (id, name, case_type, steps_json, created_at, updated_at)
workflow_steps (id, case_id, template_id, step_number, title, description, is_required, status, completed_at, completed_by, created_at, updated_at)

notifications (id, user_id, title, message, type, is_read, created_at, updated_at)
pending_notifications (id, user_id, notification_data, scheduled_at, sent_at, status, created_at)

audit_logs (id, user_id, entity_type, entity_id, action, old_values, new_values, ip_address, user_agent, created_at)

knowledge_base (id, title, content, category, tags, created_by, created_at, updated_at)
```

🔒 GÜVENLİK ÖZELLİKLERİ:
- Tüm API endpoint'leri authentication middleware'den geçmeli
- Rol > Yetki > Eylem hiyerarşisi
- SQL injection ve XSS koruması
- Rate limiting (brute force koruması)
- Dosya yükleme güvenliği (type kontrolü, boyut limiti)
- Session güvenliği (secure cookies, httpOnly)
- CORS politikası

🚀 KURULUM VE DEPLOYMENT:
- composer.json ve package.json dosyaları
- .env.example konfigürasyon şablonu
- Veritabanı migrasyon sistemi (up/down)
- Seeder dosyaları (örnek veriler)
- name.com hosting için deployment script'leri
- Otomatik backup sistemi

📱 UI/UX GEREKSİNİMLERİ:
- Responsive mobil uyumlu tasarım
- Modern ve profesyonel arayüz
- Hızlı sayfa geçişleri
- Loading states ve error handling
- Dark/light mode (opsiyonel)
- Erişilebilirlik (WCAG 2.1)

Lütfen tüm bu özellikleri içeren complete bir proje yapısı oluştur. Her modül için ayrı ayrı kodlanmış, test edilmiş ve entegre çalışır durumda olmalı.
```

---

## 🔧 2. MEVCUT PROJEYE ÖZELLİK EKLEME PROMPT'U

```
Mevcut BGAofis hukuk otomasyon sistemime aşağıdaki özellikleri ekle/geliştir:

MEVCUT DURUM:
- Backend: PHP 8.2 Slim Framework + MySQL
- Frontend: React + TypeScript + Vite + Tailwind CSS
- Var olan modüller: Kullanıcı yönetimi, davalar, arabuluculuk, finans, dokümanlar

EKLENECEK ÖZELLİKLER:
1. [Özellik adı]: [Detaylı açıklama]
2. [Özellik adı]: [Detaylı açıklama]
3. ...

TEKNİK GEREKSİNİMLER:
- Mevcut veritabanı yapısını koru, sadece ekleme yap
- Var olan API endpoint'lerine uyumlu ol, yeni endpoint'ler ekle
- Frontend component yapısına uygun geliştir
- Mevcut authentication ve authorization sistemini kullan
- Audit log sistemine tüm yeni işlemleri ekle

IMPLEMENTASYON ADIMLARI:
1. Veritabanı migrasyon dosyaları
2. Backend Controller/Model/Repository dosyaları
3. API endpoint'leri
4. Frontend component'leri
5. State management (Zustand) güncellemeleri
6. Test senaryoları

Önce backend'i sonra frontend'i geliştir. Her adımı doğrula ve entegrasyon testi yap.
```

---

## 📋 3. SPESİFİK MODÜL GELİŞTİRME PROMPT'U

```
Hukuk otomasyon sistemim için [MODÜL ADI] modülü geliştir.

MODÜL İHTİYAÇLARI:
- [Modülün amacı ve scope'u]
- Kullanıcı senaryoları
- Veri akış diyagramı

TEKNİK KISITLAMALAR:
- PHP 8.2+ Slim Framework uyumlu
- React TypeScript component yapısı
- MySQL veritabanı entegrasyonu
- JWT authentication entegrasyonu
- Mevcut audit log sistemini kullan

GEREKLİ DOSYALAR:
Backend:
- app/Controllers/[Module]Controller.php
- app/Models/[Model].php
- app/Repositories/[Module]Repository.php
- database/migrations/create_[table_name].php

Frontend:
- src/pages/[Module]/[Module]List.tsx
- src/pages/[Module]/[Module]Detail.tsx
- src/pages/[Module]/[Module]Form.tsx
- src/components/[Module]/[Component].tsx
- src/types/[module].ts

API ENDPOINT'LERİ:
- GET /api/[module] - Listeleme (filtreleme ve pagination ile)
- GET /api/[module]/:id - Detay
- POST /api/[module] - Yeni kayıt
- PUT /api/[module]/:id - Güncelleme
- DELETE /api/[module]/:id - Silme

VALIDATION KURALLARI:
- [Form validasyon kuralları]
- [Veritabanı constraint'leri]
- [Business rule'ları]

Complete implementasyon ve test senaryolarını sun.
```

---

## 🎯 4. HIZLI PROTOTİP PROMPT'U

```
Hızlı prototip için basit hukuk bürosu yönetim sistemi oluştur:

TEMEL ÖZELLİKLER:
- Kullanıcı girişi (avukat, stajyer)
- Müvekkil listesi ve detay
- Basit dava takibi (dosya no, konu, durum)
- Görev listesi
- Basit finans takibi (gelir/gider)

TEKNOLOJİLER:
- Backend: Node.js + Express (hızlı kurulum için)
- Frontend: React + TypeScript
- Veritabanı: SQLite (geliştirme için)
- Authentication: JWT

KURULUM:
- npm install && npm start ile çalışmalı
- README.md ile kurulum adımları
- Temel verilerle dolu örnek veritabanı

2 saat içinde çalışan prototip oluştur. Özellikler tam olmasın, temel işlevsellik olsun.
```

---

## 🔍 5. KOD İYİLEŞTİRME PROMPT'U

```
Mevcut hukuk otomasyon sistemimin kodunu analiz et ve iyileştir:

MEVCUT KOD STRUCTURE:
- [Proje yapısını kısaca açıkla]

TESPİT EDİLEN SORUNLAR:
- Performans sorunları
- Güvenlik açıkları
- Kod tekrarları
- Bakım zorlukları

İYİLEŞTİRME ÖNERİLERİ:
1. Backend optimizasyonları
   - Query optimizasyonu
   - Cache ekleme
   - API endpoint refactor

2. Frontend iyileştirmeleri
   - Component optimizasyonu
   - State management düzenleme
   - Performance optimizations

3. Veritabanı iyileştirmeleri
   - Index optimizasyonu
   - Tablo yapısı düzenlemeleri
   - Migration cleanup

4. Güvenlik güçlendirmeleri
   - Input validation
   - Rate limiting
   - Security headers

5. Code quality
   - PSR standartları
   - TypeScript strict mode
   - ESLint/Prettier kuralları

Implementasyon öncelik sırası ve adım adım iyileştirme planı sun.
```

---

## 📚 KULLANIM İPUÇLARI

### Prompt'u Özelleştirme:
1. **Scope belirleyin**: Tam proje, özellik ekleme, veya modül geliştirme
2. **Teknoloji kısıtlamaları ekleyin**: Sadece PHP, sadece Node.js vb.
3. **Spesifik ihtiyaçları belirtin**: Türkçe dil desteği, mobil uyum vb.
4. **Zaman çerçevesi belirleyin**: 2 saat prototip, 1 hafta complete proje vb.

### En İyi Sonuçlar İçin:
- Prompt'u kopyala-yapıştır yapmadan önce kendi ihtiyaçlarınıza göre düzenleyin
- Mevcut teknoloji stack'inizi belirtin
- Örnek veriler veya senaryolar ekleyin
- Test ve deployment gereksinimlerini belirtin

### Ek Özellikler İstiyorsanız:
- Docker containerization
- Mobil uygulama (React Native)
- API documentation (Swagger/OpenAPI)
- Automated testing (PHPUnit, Jest)
- CI/CD pipeline (GitHub Actions)
- Monitoring ve logging (ELK stack)

Bu prompt'lar kendi projenizin özelliklerine göre kolayca uyarlanabilir.

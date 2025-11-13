✅ ONE-SHOT PROJECT SPEC — HUKUK BÜROSU OTOMASYON SİSTEMİ

(Vibe Coding için tek parça, COPY-PASTE hazır)

📌 Proje Adı: BGAofis – Hukuk Bürosu Yönetim Sistemi

Backend: PHP 8 + Slim/Laravel-lite + MySQL
Frontend: React / Vite (build → shared hostingte static servis)
API tipi: JSON REST
Authentication: Session-based veya JWT
Deployment ortamı: name.com shared hosting

1) 🔥 PROJE AMAÇLARI

Hukuk bürosunun tüm süreçlerini tek panelde yönetmek

Dava + icra + arabuluculuk dosyalarını sistematik takip etmek

Görevler, duruşmalar, süreçler ve kritik süreleri otomatik hatırlatmak

Kasa/masraf tahsilatlarını kontrol etmek

Profesyonel belge şablonları (dilekçe, tutanak, sözleşme) üretebilmek

KVKK uyumlu log & rol tabanlı yetkilendirme sağlamak

2) 🔧 ANA MODÜLLER

Aşağıdaki modüller zorunlu şekilde dahil edilmelidir:

2.1. Müvekkil / İlgili Kişi (CRM)

Gerçek/tüzel kişi

İletişim bilgileri

Etiketleme

Notlar

Dosya bağlantıları

2.2. Dosya Yönetimi

Dava, icra, danışmanlık, arabuluculuk

Esas no, dosya no, taraflar

Dayanak, konu, talepler

Duruşmalar, kararlar

Masraflar

Görevler

Dokümanlar

Workflow durumu

2.3. Arabuluculuk Modülü

Başvuru bilgileri

Taraflar

Toplantı tarihleri

Sonuç: Anlaşma / Anlaşmama

Otomatik şablon üretimi:

Başvuru formu

Son tutanak

Anlaşma metni (word/pdf)

2.4. Kasa / Finans Modülü

Tahsilat

Masraf

Dosya bazlı finansal durum

Aylık gelir-gider raporu

Filtreleme (tarih, dosya, tür)

2.5. Görev & Takvim Sistem

Görev atama

Son tarih uyarıları

Duruşma ve görevlerin takvim birleşik görünümü

3) ⚡ EK MODÜLLER (ZORUNLU)
3.1. Bildirim & Hatırlatma Sistemi

Duruşma yaklaşınca X gün/saat önce

Temyiz/istinaf/itiraz kritik süreleri

Görev son tarihleri

E-posta tabanlı cron-job sistemi

“pending_notifications” tablosu

3.2. Doküman Yönetimi + Versiyonlama

Dosya bazlı evrak listesi

Belgeler için auto-versioning (v1, v2…)

Yükleyen / indiren / silen kullanıcı log’u

PDF, DOCX, görsel desteği

3.3. Check-list / Workflow Modülü

İş türüne göre workflow şablonları:

İşçi alacağı davası

Arabuluculuk süreci

İcra takibi açılışı

Her adım için:

Açıklama

Zorunlu/opsiyonel flag

Dosya açılışında workflow kopyalanır

Dashboard: “eksik adımı olan dosyalar”

3.4. Gelişmiş Arama + Full Text Search

Müvekkil, karşı taraf, vekil, TCKN/VKN

Dosya no, taraf isimleri

Doküman ismi

Notlar

Dilekçe içeriklerinde metin arama (MySQL FULLTEXT)

3.5. Dashboard + Raporlama

Bugünkü duruşmalar

Gelecek kritik süreler

Açık görevler

Bu ay tahsilat/masraf

Workflow ilerleme oranları

En aktif müvekkiller

3.6. Ücret / Teklif & Sözleşme Üretici

Masraf/harç hesaplama

Avukatlık ücreti hesap formu

Tek tıkla sözleşme taslağı üretme

3.7. Bilgi Bankası (Wiki Style)

İçtihat linkleri

Mahkeme/bilirkişi notları

Etiketli bilgi havuzu

4) 🛡️ GÜVENLİK & UYUM MODÜLLERİ
4.1. Rol > Yetki > Eylem Yetkilendirme Sistemi

Roller (örnek):

ADMIN

AVUKAT

STAJYER

SEKRETERYA

FINANS

Yetki anahtarları:

CASE_VIEW_ALL

CASE_VIEW_OWN

CASE_EDIT

CASH_VIEW

CASH_EDIT

DOC_UPLOAD

DOC_DELETE

LOG_VIEW

ADMIN_USERS

Her API endpoint’i, yetkilendirme middleware’inden geçmelidir.

4.2. KVKK Uyumlu Audit Log Sistemi

audit_logs tablosu içerik:

user_id

entity_type (case, client, task, doc, cash…)

entity_id

action: (create, update, delete, view, download, login…)

timestamp

ip

Log görüntüleme sadece ADMIN ve belirli rollerle sınırlandırılmalı.

4.3. Yedekleme Stratejisi

Günlük otomatik DB dump

Doküman klasörü için zip/arsiv

Yedek indirme ekranı

Yedek indirme işlemi de audit_log içine yazılır

5) 📂 VERİ TABANI ŞEMASI (Kısaltılmış)

Aşağıdaki tablolar oluşturulmalıdır:

users
roles
permissions
user_roles
role_permissions

clients
cases
case_parties
hearings
tasks
documents
document_versions
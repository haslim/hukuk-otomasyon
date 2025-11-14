1. Backend .env Örneği (BGAofis – PHP + MySQL)

Shared hosting’te PHP projesi kök dizininde .env dosyası:

APP_NAME=BGAofis
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bgaofis.example.com

# Timezone
APP_TIMEZONE=Europe/Istanbul

# Database
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=bgaofis
DB_USERNAME=bgaofis_user
DB_PASSWORD=strong_password_here

# JWT / Auth
JWT_SECRET=change_this_to_a_long_random_string

# Mail (name.com / hosting SMTP bilgine göre düzenle)
MAIL_MAILER=smtp
MAIL_HOST=mail.bgaofis.com
MAIL_PORT=587
MAIL_USERNAME=no-reply@bgaofis.com
MAIL_PASSWORD=mail_password_here
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@bgaofis.com
MAIL_FROM_NAME="${APP_NAME}"

# Logs
LOG_CHANNEL=daily

# File uploads
FILES_UPLOAD_PATH=/home/username/bgaofis/uploads
BACKUP_PATH=/home/username/bgaofis/backups


Not: FILES_UPLOAD_PATH ve BACKUP_PATH’i shared hostingteki gerçek kullanıcı yoluna göre revize edeceksiniz.

2. MySQL Migration SQL (Toplu Şema – BGAofis)

Bunu tek seferde phpMyAdmin → SQL sekmesine yapıştırıp çalıştırabilirsiniz. Gerekirse Vibe Coding’e “bunlara uygun model ve repository yaz” diyeceksiniz.

-- USERS & AUTH

CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(150) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE user_roles (
    user_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, role_id),
    CONSTRAINT fk_user_roles_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_user_roles_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE role_permissions (
    role_id BIGINT UNSIGNED NOT NULL,
    permission_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (role_id, permission_id),
    CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- CLIENTS (MÜVEKKİL / İLGİLİ KİŞİ)

CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type ENUM('individual','company') NOT NULL DEFAULT 'individual',
    first_name VARCHAR(150) NULL,
    last_name VARCHAR(150) NULL,
    company_name VARCHAR(255) NULL,
    identification_no VARCHAR(20) NULL,   -- TCKN
    tax_no VARCHAR(20) NULL,              -- VKN
    phone VARCHAR(50) NULL,
    email VARCHAR(190) NULL,
    address TEXT NULL,
    notes TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_clients_name (last_name, first_name),
    INDEX idx_clients_company (company_name),
    INDEX idx_clients_ident (identification_no),
    INDEX idx_clients_tax (tax_no),
    CONSTRAINT fk_clients_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    CONSTRAINT fk_clients_updated_by FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- CASES (DOSYALAR)

CREATE TABLE cases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    case_type ENUM('lawsuit','enforcement','mediation','consultancy','other') NOT NULL DEFAULT 'lawsuit',
    court_name VARCHAR(255) NULL,
    file_no VARCHAR(100) NULL,            -- Mahkeme dosya no
    basis_no VARCHAR(100) NULL,           -- Esas no
    status ENUM('open','pending','closed','appeal','supreme_court') NOT NULL DEFAULT 'open',
    description TEXT NULL,
    assigned_user_id BIGINT UNSIGNED NULL,
    opened_at DATE NULL,
    closed_at DATE NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cases_client (client_id),
    INDEX idx_cases_type_status (case_type, status),
    INDEX idx_cases_file (file_no, basis_no),
    CONSTRAINT fk_cases_client FOREIGN KEY (client_id) REFERENCES clients(id),
    CONSTRAINT fk_cases_assigned_user FOREIGN KEY (assigned_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- CASE PARTIES (TARAF / KARŞI TARAF / 3. KİŞİ)

CREATE TABLE case_parties (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    role ENUM('plaintiff','defendant','counterparty','other') NOT NULL DEFAULT 'other',
    contact_info TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_case_parties_case (case_id),
    CONSTRAINT fk_case_parties_case FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- HEARINGS (DURUŞMALAR)

CREATE TABLE hearings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_id BIGINT UNSIGNED NOT NULL,
    hearing_date DATETIME NOT NULL,
    courtroom VARCHAR(150) NULL,
    judge VARCHAR(255) NULL,
    notes TEXT NULL,
    result VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_hearings_case (case_id),
    INDEX idx_hearings_date (hearing_date),
    CONSTRAINT fk_hearings_case FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- TASKS (GÖREVLER)

CREATE TABLE tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_id BIGINT UNSIGNED NULL,
    assigned_to BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    status ENUM('open','in_progress','completed','cancelled') NOT NULL DEFAULT 'open',
    priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    due_date DATETIME NULL,
    completed_at DATETIME NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_tasks_case (case_id),
    INDEX idx_tasks_assigned (assigned_to),
    INDEX idx_tasks_status_due (status, due_date),
    CONSTRAINT fk_tasks_case FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL,
    CONSTRAINT fk_tasks_assigned_to FOREIGN KEY (assigned_to) REFERENCES users(id),
    CONSTRAINT fk_tasks_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- DOCUMENTS & VERSIONS

CREATE TABLE documents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_id BIGINT UNSIGNED NULL,
    client_id BIGINT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(150) NULL,
    file_size BIGINT UNSIGNED NULL,
    uploaded_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_documents_case (case_id),
    INDEX idx_documents_client (client_id),
    CONSTRAINT fk_documents_case FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL,
    CONSTRAINT fk_documents_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    CONSTRAINT fk_documents_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE document_versions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    document_id BIGINT UNSIGNED NOT NULL,
    version_number INT UNSIGNED NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_doc_versions_document (document_id),
    CONSTRAINT fk_doc_versions_document FOREIGN KEY (document_id) REFERENCES documents(id) ON DELETE CASCADE,
    CONSTRAINT fk_doc_versions_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- WORKFLOW (CHECK-LIST / İŞ AKIŞLARI)

CREATE TABLE workflows (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    case_type ENUM('lawsuit','enforcement','mediation','consultancy','other') NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE workflow_steps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workflow_id BIGINT UNSIGNED NOT NULL,
    step_order INT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_required TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_workflow_steps_workflow (workflow_id),
    CONSTRAINT fk_workflow_steps_workflow FOREIGN KEY (workflow_id) REFERENCES workflows(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE workflow_instances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_id BIGINT UNSIGNED NOT NULL,
    workflow_id BIGINT UNSIGNED NOT NULL,
    status ENUM('not_started','in_progress','completed') NOT NULL DEFAULT 'not_started',
    started_at DATETIME NULL,
    completed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_workflow_instances_case (case_id),
    CONSTRAINT fk_workflow_instances_case FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE,
    CONSTRAINT fk_workflow_instances_workflow FOREIGN KEY (workflow_id) REFERENCES workflows(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE workflow_instance_steps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    workflow_instance_id BIGINT UNSIGNED NOT NULL,
    workflow_step_id BIGINT UNSIGNED NOT NULL,
    status ENUM('pending','completed','skipped') NOT NULL DEFAULT 'pending',
    completed_at DATETIME NULL,
    completed_by BIGINT UNSIGNED NULL,
    notes TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_wi_steps_instance (workflow_instance_id),
    CONSTRAINT fk_wi_steps_instance FOREIGN KEY (workflow_instance_id) REFERENCES workflow_instances(id) ON DELETE CASCADE,
    CONSTRAINT fk_wi_steps_step FOREIGN KEY (workflow_step_id) REFERENCES workflow_steps(id),
    CONSTRAINT fk_wi_steps_completed_by FOREIGN KEY (completed_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- CASH TRANSACTIONS (KASA)

CREATE TABLE cash_transactions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    case_id BIGINT UNSIGNED NULL,
    client_id BIGINT UNSIGNED NULL,
    type ENUM('income','expense') NOT NULL,
    category VARCHAR(150) NULL,
    amount DECIMAL(12,2) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'TRY',
    transaction_date DATE NOT NULL,
    description TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_cash_case (case_id),
    INDEX idx_cash_client (client_id),
    INDEX idx_cash_date (transaction_date),
    CONSTRAINT fk_cash_case FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE SET NULL,
    CONSTRAINT fk_cash_client FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    CONSTRAINT fk_cash_created_by FOREIGN KEY (created_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- NOTIFICATIONS

CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(150) NOT NULL,
    data JSON NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    read_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notifications_user (user_id, is_read),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pending_notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    notification_type VARCHAR(150) NOT NULL,
    related_entity_type VARCHAR(150) NULL,
    related_entity_id BIGINT UNSIGNED NULL,
    due_at DATETIME NOT NULL,
    processed_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_pending_notifications_due (due_at, processed_at),
    CONSTRAINT fk_pending_notifications_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- AUDIT LOGS (KVKK UYUMLU)

CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    entity_type VARCHAR(150) NOT NULL,
    entity_id BIGINT UNSIGNED NULL,
    action VARCHAR(50) NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_entity (entity_type, entity_id),
    INDEX idx_audit_user (user_id),
    CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- KNOWLEDGE BASE (BİLGİ BANKASI)

CREATE TABLE knowledge_base (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
    FULLTEXT KEY ft_knowledge_content (title, content),
    CONSTRAINT fk_kb_created_by FOREIGN KEY (created_by) REFERENCES users(id),
    CONSTRAINT fk_kb_updated_by FOREIGN KEY (updated_by) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- TAGS & ENTITY TAGS (ETİKETLEME)

CREATE TABLE tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE,
    slug VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE entity_tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tag_id BIGINT UNSIGNED NOT NULL,
    entity_type VARCHAR(150) NOT NULL,
    entity_id BIGINT UNSIGNED NOT NULL,
    UNIQUE KEY uniq_entity_tag (tag_id, entity_type, entity_id),
    CONSTRAINT fk_entity_tags_tag FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


Bu şema BGAofis’in tüm konuştuğumuz modüllerini taşıyacak kapasitede.

3. Frontend .env (React / Vite için)

frontend/.env ya da .env.local:

VITE_APP_NAME=BGAofis
VITE_API_BASE_URL=https://bgaofis.example.com/api
VITE_DEFAULT_LOCALE=tr-TR


Component’lerde:

const api = import.meta.env.VITE_API_BASE_URL;


şeklinde kullanırsınız.

4. İlk Ekran Tasarımları (React Component İskeletleri)
4.1. Dashboard (BGAofis ana paneli)

Sol tarafta Sidebar

Üstte top bar (kullanıcı menüsü, arama)

Ortada kartlar:

Bugünkü duruşma sayısı

Açık görev sayısı

Bu ay tahsilat / masraf

Kritik süreli dosyalar

// src/pages/DashboardPage.tsx
import React from "react";

export const DashboardPage: React.FC = () => {
  return (
    <div className="min-h-screen flex bg-slate-100">
      {/* Sidebar */}
      <aside className="w-64 bg-slate-900 text-white flex flex-col">
        <div className="p-4 font-bold text-xl">BGAofis</div>
        <nav className="flex-1 px-2">
          <a href="/dashboard" className="block px-3 py-2 rounded hover:bg-slate-800">
            Dashboard
          </a>
          <a href="/cases" className="block px-3 py-2 rounded hover:bg-slate-800">
            Dosyalar
          </a>
          <a href="/clients" className="block px-3 py-2 rounded hover:bg-slate-800">
            Müvekkiller
          </a>
          <a href="/calendar" className="block px-3 py-2 rounded hover:bg-slate-800">
            Takvim
          </a>
          <a href="/cash" className="block px-3 py-2 rounded hover:bg-slate-800">
            Kasa
          </a>
        </nav>
      </aside>

      {/* Main */}
      <main className="flex-1 flex flex-col">
        {/* Top bar */}
        <header className="h-14 px-6 flex items-center justify-between bg-white border-b">
          <h1 className="text-xl font-semibold">Dashboard</h1>
          <div className="flex items-center gap-4">
            <input
              type="search"
              placeholder="Dosya, müvekkil ara..."
              className="border rounded px-3 py-1 text-sm"
            />
            <div className="flex items-center gap-2">
              <span className="text-sm text-slate-600">Av. Kullanıcı</span>
              <div className="w-8 h-8 rounded-full bg-slate-300" />
            </div>
          </div>
        </header>

        {/* Content */}
        <section className="p-6 space-y-6">
          {/* Stats cards */}
          <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
            <DashboardCard title="Bugünkü Duruşmalar" value="3" />
            <DashboardCard title="Açık Görevler" value="12" />
            <DashboardCard title="Bu Ay Tahsilat (₺)" value="145.000" />
            <DashboardCard title="Kritik Süreli Dosyalar" value="5" />
          </div>

          {/* Two-column layout: upcoming hearings & tasks */}
          <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div className="bg-white rounded-lg shadow p-4">
              <h2 className="font-semibold mb-3">Yaklaşan Duruşmalar</h2>
              {/* Buraya hearing listesi gelecek */}
              <p className="text-sm text-slate-500">Henüz veri bağlanmadı.</p>
            </div>
            <div className="bg-white rounded-lg shadow p-4">
              <h2 className="font-semibold mb-3">Açık Görevler</h2>
              {/* Buraya görev listesi gelecek */}
              <p className="text-sm text-slate-500">Henüz veri bağlanmadı.</p>
            </div>
          </div>
        </section>
      </main>
    </div>
  );
};

type CardProps = { title: string; value: string };

const DashboardCard: React.FC<CardProps> = ({ title, value }) => (
  <div className="bg-white rounded-lg shadow p-4">
    <div className="text-sm text-slate-500">{title}</div>
    <div className="text-2xl font-bold mt-2">{value}</div>
  </div>
);

4.2. Dosya Listesi Ekranı (Cases)

Üstte filtre barı: durum, tür, müvekkil arama

Ortada tablo: esas no, dosya no, mahkeme, müvekkil, durum, sorumlu

// src/pages/CasesListPage.tsx
import React from "react";

export const CasesListPage: React.FC = () => {
  // Örnek dummy data
  const cases = [
    {
      id: 1,
      title: "İşçilik alacağı davası",
      client: "Ali Veli",
      court: "Ankara 5. İş Mahkemesi",
      fileNo: "2025/123",
      status: "Açık",
      assignedTo: "Av. BG",
    },
  ];

  return (
    <div className="p-6 space-y-4">
      <header className="flex items-center justify-between mb-4">
        <h1 className="text-xl font-semibold">Dosyalar</h1>
        <button className="px-4 py-2 rounded bg-slate-900 text-white text-sm">
          Yeni Dosya Aç
        </button>
      </header>

      {/* Filter bar */}
      <div className="flex flex-wrap gap-3 bg-white p-4 rounded-lg shadow">
        <input
          type="search"
          placeholder="Müvekkil, esas no, karşı taraf..."
          className="border rounded px-3 py-1 text-sm flex-1 min-w-[200px]"
        />
        <select className="border rounded px-3 py-1 text-sm">
          <option value="">Tür: Hepsi</option>
          <option value="lawsuit">Dava</option>
          <option value="enforcement">İcra</option>
          <option value="mediation">Arabuluculuk</option>
        </select>
        <select className="border rounded px-3 py-1 text-sm">
          <option value="">Durum: Hepsi</option>
          <option value="open">Açık</option>
          <option value="pending">Beklemede</option>
          <option value="closed">Kapanmış</option>
        </select>
      </div>

      {/* Table */}
      <div className="bg-white rounded-lg shadow overflow-x-auto">
        <table className="min-w-full text-sm">
          <thead className="bg-slate-50 border-b">
            <tr>
              <th className="text-left px-4 py-2">Başlık</th>
              <th className="text-left px-4 py-2">Müvekkil</th>
              <th className="text-left px-4 py-2">Mahkeme</th>
              <th className="text-left px-4 py-2">Dosya / Esas</th>
              <th className="text-left px-4 py-2">Durum</th>
              <th className="text-left px-4 py-2">Sorumlu</th>
            </tr>
          </thead>
          <tbody>
            {cases.map((c) => (
              <tr key={c.id} className="border-b hover:bg-slate-50">
                <td className="px-4 py-2">{c.title}</td>
                <td className="px-4 py-2">{c.client}</td>
                <td className="px-4 py-2">{c.court}</td>
                <td className="px-4 py-2">{c.fileNo}</td>
                <td className="px-4 py-2">{c.status}</td>
                <td className="px-4 py-2">{c.assignedTo}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </div>
  );
};

4.3. Dosya Detay Ekranı (Tabs: Özet / Duruşmalar / Görevler / Dokümanlar / Workflow / Kasa)

Burada sadece iskelet:

// src/pages/CaseDetailPage.tsx
import React, { useState } from "react";

const tabs = ["Özet", "Duruşmalar", "Görevler", "Dokümanlar", "Workflow", "Kasa"];

export const CaseDetailPage: React.FC = () => {
  const [activeTab, setActiveTab] = useState("Özet");

  return (
    <div className="p-6 space-y-4">
      <header className="flex items-center justify-between">
        <div>
          <h1 className="text-xl font-semibold">İşçilik alacağı davası</h1>
          <p className="text-sm text-slate-500">
            Müvekkil: Ali Veli – Ankara 5. İş Mahkemesi – 2025/123
          </p>
        </div>
        <button className="px-3 py-1 text-sm rounded border">
          Düzenle
        </button>
      </header>

      {/* Tabs */}
      <div className="border-b flex gap-4">
        {tabs.map((tab) => (
          <button
            key={tab}
            onClick={() => setActiveTab(tab)}
            className={
              "px-3 py-2 text-sm border-b-2 -mb-px " +
              (activeTab === tab
                ? "border-slate-900 font-semibold"
                : "border-transparent text-slate-500")
            }
          >
            {tab}
          </button>
        ))}
      </div>

      {/* Content placeholder */}
      <div className="bg-white rounded-lg shadow p-4">
        {activeTab === "Özet" && <p>Özet bilgileri burada gösterilecek.</p>}
        {activeTab === "Duruşmalar" && <p>Duruşma listesi burada.</p>}
        {activeTab === "Görevler" && <p>Görevler burada.</p>}
        {activeTab === "Dokümanlar" && <p>Doküman listesi / upload komponenti.</p>}
        {activeTab === "Workflow" && <p>Checklist / iş akışı burada.</p>}
        {activeTab === "Kasa" && <p>Bu dosyaya bağlı kasa hareketleri.</p>}
      </div>
    </div>
  );
};
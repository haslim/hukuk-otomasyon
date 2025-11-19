-- Menü tablolarını oluştur (eğer yoksa)
CREATE TABLE IF NOT EXISTS menu_items (
    id VARCHAR(255) PRIMARY KEY,
    path VARCHAR(255) NOT NULL UNIQUE,
    label VARCHAR(255) NOT NULL,
    icon VARCHAR(100) NOT NULL,
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS role_menu_permissions (
    id VARCHAR(255) PRIMARY KEY,
    role_id VARCHAR(255) NOT NULL,
    menu_item_id VARCHAR(255) NOT NULL,
    is_visible BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_role_menu (role_id, menu_item_id)
);

-- Arabuluculuk menüsünü ekle
INSERT INTO menu_items (id, path, label, icon, sort_order, is_active, created_at, updated_at) VALUES
('arbitration-dashboard', '/arbitration/dashboard', 'Arabuluculuk Dashboard', 'dashboard', 50, 1, NOW(), NOW()),
('arbitration-list', '/arbitration', 'Arabuluculuk Başvuruları', 'gavel', 51, 1, NOW(), NOW()),
('arbitration-new', '/arbitration/new', 'Yeni Başvuru', 'add', 52, 1, NOW(), NOW());

-- Avukat rolü için arabuluculuk menülerini aktif et
INSERT INTO role_menu_permissions (id, role_id, menu_item_id, is_visible, created_at, updated_at) VALUES
(UUID(), (SELECT id FROM roles WHERE name = 'avukat' LIMIT 1), 'arbitration-dashboard', 1, NOW(), NOW()),
(UUID(), (SELECT id FROM roles WHERE name = 'avukat' LIMIT 1), 'arbitration-list', 1, NOW(), NOW()),
(UUID(), (SELECT id FROM roles WHERE name = 'avukat' LIMIT 1), 'arbitration-new', 1, NOW(), NOW());

-- Admin rolü için arabuluculuk menülerini aktif et
INSERT INTO role_menu_permissions (id, role_id, menu_item_id, is_visible, created_at, updated_at) VALUES
(UUID(), (SELECT id FROM roles WHERE name = 'admin' LIMIT 1), 'arbitration-dashboard', 1, NOW(), NOW()),
(UUID(), (SELECT id FROM roles WHERE name = 'admin' LIMIT 1), 'arbitration-list', 1, NOW(), NOW()),
(UUID(), (SELECT id FROM roles WHERE name = 'admin' LIMIT 1), 'arbitration-new', 1, NOW(), NOW());

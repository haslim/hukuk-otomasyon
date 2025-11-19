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

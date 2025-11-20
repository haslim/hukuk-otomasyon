-- Menu Restoration SQL Script
-- This script restores the complete menu system with all items and permissions

-- Clear existing data
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM menu_permissions;
DELETE FROM menu_items;
SET FOREIGN_KEY_CHECKS = 1;

-- Main menu items (13 items)
INSERT INTO menu_items (id, path, label, icon, sort_order, is_active, parent_id, created_at, updated_at) VALUES
(UUID(), '/', 'Dashboard', 'dashboard', 1, 1, NULL, NOW(), NOW()),
(UUID(), '/profile', 'Profilim', 'account_circle', 2, 1, NULL, NOW(), NOW()),
(UUID(), '/cases', 'Dosyalar', 'folder', 3, 1, NULL, NOW(), NOW()),
(UUID(), '/mediation', 'Arabuluculuk', 'handshake', 4, 1, NULL, NOW(), NOW()),
(UUID(), '/clients', 'Müvekkiller', 'group', 5, 1, NULL, NOW(), NOW()),
(UUID(), '/finance/cash', 'Kasa', 'account_balance_wallet', 6, 1, NULL, NOW(), NOW()),
(UUID(), '/calendar', 'Takvim', 'calendar_month', 7, 1, NULL, NOW(), NOW()),
(UUID(), '/users', 'Kullanıcılar & Roller', 'manage_accounts', 8, 1, NULL, NOW(), NOW()),
(UUID(), '/documents', 'Dokümanlar', 'folder_open', 9, 1, NULL, NOW(), NOW()),
(UUID(), '/notifications', 'Bildirimler', 'notifications', 10, 1, NULL, NOW(), NOW()),
(UUID(), '/workflow', 'Workflow', 'route', 11, 1, NULL, NOW(), NOW()),
(UUID(), '/menu-management', 'Menü Yönetimi', 'menu', 12, 1, NULL, NOW(), NOW()),
(UUID(), '/search', 'Arama', 'search', 13, 1, NULL, NOW(), NOW());

-- Get the mediation menu ID for sub-items
SET @mediation_id = (SELECT id FROM menu_items WHERE path = '/mediation' LIMIT 1);

-- Sub-menu items (4 items)
INSERT INTO menu_items (id, path, label, icon, sort_order, is_active, parent_id, created_at, updated_at) VALUES
(UUID(), '/mediation/list', 'Arabuluculuk Dosyaları', 'list', 1, 1, @mediation_id, NOW(), NOW()),
(UUID(), '/mediation/new', 'Yeni Arabuluculuk Başvurusu', 'add', 2, 1, @mediation_id, NOW(), NOW()),
(UUID(), '/arbitration', 'Arabuluculuk Başvuruları', 'gavel', 3, 1, @mediation_id, NOW(), NOW()),
(UUID(), '/arbitration/dashboard', 'Arabuluculuk İstatistikleri', 'bar_chart', 4, 1, @mediation_id, NOW(), NOW());

-- Ensure roles exist
INSERT IGNORE INTO roles (id, `key`, name, created_at, updated_at) VALUES
(UUID(), 'administrator', 'Administrator', NOW(), NOW()),
(UUID(), 'lawyer', 'Avukat', NOW(), NOW());

-- Get role IDs
SET @admin_role_id = (SELECT id FROM roles WHERE `key` = 'administrator' LIMIT 1);
SET @lawyer_role_id = (SELECT id FROM roles WHERE `key` = 'lawyer' LIMIT 1);

-- Create administrator permissions (all 17 menu items visible)
INSERT INTO menu_permissions (id, role_id, menu_item_id, is_visible, created_at, updated_at)
SELECT UUID(), @admin_role_id, id, 1, NOW(), NOW() FROM menu_items;

-- Create lawyer permissions (13 menu items visible, 4 hidden)
INSERT INTO menu_permissions (id, role_id, menu_item_id, is_visible, created_at, updated_at)
SELECT 
    UUID(), 
    @lawyer_role_id, 
    id, 
    CASE 
        WHEN path IN (
            '/', -- Dashboard
            '/cases', -- Dosyalar
            '/mediation', -- Arabuluculuk (ana menü)
            '/mediation/list', -- Arabuluculuk Dosyaları
            '/mediation/new', -- Yeni Arabuluculuk Başvurusu
            '/arbitration', -- Arabuluculuk Başvuruları
            '/arbitration/dashboard', -- Arabuluculuk İstatistikleri
            '/clients', -- Müvekkiller
            '/finance/cash', -- Kasa
            '/calendar', -- Takvim
            '/documents', -- Dokümanlar
            '/notifications', -- Bildirimler
            '/search' -- Arama
        ) THEN 1
        ELSE 0
    END,
    NOW(), 
    NOW() 
FROM menu_items;

-- Verification queries (for manual checking)
-- SELECT COUNT(*) FROM menu_items; -- Should be 17
-- SELECT COUNT(*) FROM menu_permissions; -- Should be 34
-- SELECT label, path FROM menu_items WHERE parent_id IS NULL ORDER BY sort_order;
-- SELECT mi.label, mi.path FROM menu_items mi JOIN menu_permissions mp ON mi.id = mp.menu_item_id WHERE mp.role_id = @lawyer_role_id AND mp.is_visible = 1 ORDER BY mi.sort_order;
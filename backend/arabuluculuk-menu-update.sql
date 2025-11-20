-- Add parent_id column to menu_items table if it doesn't exist
ALTER TABLE menu_items ADD COLUMN parent_id VARCHAR(36) NULL AFTER id;

-- Add foreign key constraint if it doesn't exist
ALTER TABLE menu_items ADD CONSTRAINT menu_items_parent_id_foreign FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE;

-- Add index for parent_id if it doesn't exist
CREATE INDEX menu_items_parent_id_index ON menu_items(parent_id);

-- Clear existing menu data
DELETE FROM menu_permissions;
DELETE FROM menu_items;

-- Insert new hierarchical menu structure
INSERT INTO menu_items (id, path, label, icon, sort_order, is_active, parent_id, created_at, updated_at) VALUES
-- Main menu items
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

-- Get the mediation parent ID
SET @mediation_parent_id = (SELECT id FROM menu_items WHERE path = '/mediation' LIMIT 1);

-- Insert Arabuluculuk sub-menu items
INSERT INTO menu_items (id, path, label, icon, sort_order, is_active, parent_id, created_at, updated_at) VALUES
(UUID(), '/mediation/list', 'Arabuluculuk Dosyaları', 'list', 1, 1, @mediation_parent_id, NOW(), NOW()),
(UUID(), '/mediation/new', 'Yeni Arabuluculuk Başvurusu', 'add', 2, 1, @mediation_parent_id, NOW(), NOW()),
(UUID(), '/arbitration', 'Arabuluculuk Başvuruları', 'gavel', 3, 1, @mediation_parent_id, NOW(), NOW()),
(UUID(), '/arbitration/dashboard', 'Arabuluculuk İstatistikleri', 'bar_chart', 4, 1, @mediation_parent_id, NOW(), NOW());

-- Get role IDs
SET @admin_role_id = (SELECT id FROM roles WHERE `key` = 'administrator' LIMIT 1);
SET @lawyer_role_id = (SELECT id FROM roles WHERE `key` = 'lawyer' LIMIT 1);

-- Insert menu permissions for administrator (all menus visible)
INSERT INTO menu_permissions (id, role_id, menu_item_id, is_visible, created_at, updated_at)
SELECT UUID(), @admin_role_id, id, 1, NOW(), NOW() FROM menu_items;

-- Insert menu permissions for lawyer (specific menus visible)
INSERT INTO menu_permissions (id, role_id, menu_item_id, is_visible, created_at, updated_at)
SELECT 
    UUID(), 
    @lawyer_role_id, 
    id, 
    CASE 
        WHEN path IN ('/', '/cases', '/mediation', '/mediation/list', '/mediation/new', '/arbitration', '/arbitration/dashboard', '/clients', '/finance/cash', '/calendar', '/documents', '/notifications', '/search') THEN 1
        ELSE 0
    END,
    NOW(), 
    NOW() 
FROM menu_items;
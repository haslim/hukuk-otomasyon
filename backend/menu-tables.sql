-- BGAofis Menu Tables SQL
-- Run this manually in your database

-- Create menu_items table
CREATE TABLE IF NOT EXISTS `menu_items` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `path` VARCHAR(255) NOT NULL UNIQUE,
    `label` VARCHAR(255) NOT NULL,
    `icon` VARCHAR(255) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create menu_permissions table
CREATE TABLE IF NOT EXISTS `menu_permissions` (
    `id` CHAR(36) NOT NULL PRIMARY KEY,
    `role_id` CHAR(36) NOT NULL,
    `menu_item_id` CHAR(36) NOT NULL,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_role_menu` (`role_id`, `menu_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert basic menu items
INSERT INTO `menu_items` (`id`, `path`, `label`, `icon`, `sort_order`, `is_active`) VALUES
('550e8400-e29b-41d4-a716-446655440001', '/', 'Dashboard', 'dashboard', 1, 1),
('550e8400-e29b-41d4-a716-446655440002', '/profile', 'Profilim', 'account_circle', 2, 1),
('550e8400-e29b-41d4-a716-446655440003', '/cases', 'Dosyalar', 'folder', 3, 1),
('550e8400-e29b-41d4-a716-446655440004', '/mediation', 'Arabuluculuk', 'handshake', 4, 1),
('550e8400-e29b-41d4-a716-446655440005', '/clients', 'Müvekkiller', 'group', 5, 1),
('550e8400-e29b-41d4-a716-446655440006', '/finance/cash', 'Kasa', 'account_balance_wallet', 6, 1),
('550e8400-e29b-41d4-a716-446655440007', '/calendar', 'Takvim', 'calendar_month', 7, 1),
('550e8400-e29b-41d4-a716-446655440008', '/users', 'Kullanıcılar & Roller', 'manage_accounts', 8, 1),
('550e8400-e29b-41d4-a716-446655440009', '/documents', 'Dokümanlar', 'folder_open', 9, 1),
('550e8400-e29b-41d4-a716-446655440010', '/notifications', 'Bildirimler', 'notifications', 10, 1),
('550e8400-e29b-41d4-a716-446655440011', '/workflow', 'Workflow', 'route', 11, 1),
('550e8400-e29b-41d4-a716-446655440012', '/menu-management', 'Menü Yönetimi', 'menu', 12, 1),
('550e8400-e29b-41d4-a716-446655440013', '/search', 'Arama', 'search', 13, 1);

-- Get administrator role ID (assuming it exists)
SET @admin_role_id = (SELECT id FROM roles WHERE `key` = 'administrator' LIMIT 1);
SET @lawyer_role_id = (SELECT id FROM roles WHERE `key` = 'lawyer' LIMIT 1);

-- Insert permissions for administrator (all menus visible)
INSERT INTO `menu_permissions` (`id`, `role_id`, `menu_item_id`, `is_visible`)
SELECT 
    CONCAT('550e8400-e29b-41d4-a716-44665544', LPAD(menu_item_index, 3, '0')) as id,
    @admin_role_id as role_id,
    id as menu_item_id,
    1 as is_visible
FROM menu_items
WHERE @admin_role_id IS NOT NULL;

-- Insert permissions for lawyer (restricted menus)
INSERT INTO `menu_permissions` (`id`, `role_id`, `menu_item_id`, `is_visible`) VALUES
('550e8400-e29b-41d4-a716-446655440100', @lawyer_role_id, '550e8400-e29b-41d4-a716-446655440001', 1), -- Dashboard
('550e8400-e29b-41d4-a716-446655440101', @lawyer_role_id, '550e8400-e29b-41d4-a716-446655440003', 1), -- Dosyalar
('550e8400-e29b-41d4-a716-446655440102', @lawyer_role_id, '550e8400-e29b-41d4-a716-446655440004', 1), -- Arabuluculuk
('550e8400-e29b-41d4-a716-446655440103', @lawyer_role_id, '550e8400-e29b-41d4-a716-446655440005', 1), -- Müvekkiller
('550e8400-e29b-41d4-a716-446655440104', @lawyer_role_id, '550e8400-e29b-41d4-a716-446655440006', 1), -- Kasa
('550e8400-e29b-41d4-a716-446655440105', @lawyer_role_id, '550e8400-e29b-41d4-a716-446655440007', 1), -- Takvim
('550e8400-e29b-41d4-a716-446655440106', @lawyer_role_id, '550e8400-e29b-41d4-a716-446655440009', 1), -- Dokümanlar
('550e8400-e29b-41d4-a716-446655440107', @lawyer_role_id, '550e8400-e29b-41d4-a716-446655440010', 1), -- Bildirimler
('550e8400-e29b-41d4-a716-446655440108', @lawyer_role_id, '550e8400-e29b-41d4-a716-446655440012', 1); -- Arama

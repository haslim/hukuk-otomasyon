-- BGAofis Menu Tables SQL - Fixed Foreign Key Issues
-- Run this manually in your database

-- Step 1: Drop existing tables if they exist (clean start)
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `menu_permissions`;
DROP TABLE IF EXISTS `menu_items`;
SET FOREIGN_KEY_CHECKS = 1;

-- Step 2: Create menu_items table (no foreign keys)
CREATE TABLE `menu_items` (
    `id` BINARY(16) NOT NULL PRIMARY KEY,
    `path` VARCHAR(255) NOT NULL UNIQUE,
    `label` VARCHAR(255) NOT NULL,
    `icon` VARCHAR(255) NOT NULL,
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    `deleted_at` TIMESTAMP NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 3: Create menu_permissions table (no foreign keys initially)
CREATE TABLE `menu_permissions` (
    `id` BINARY(16) NOT NULL PRIMARY KEY,
    `role_id` BINARY(16) NOT NULL,
    `menu_item_id` BINARY(16) NOT NULL,
    `is_visible` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_role_id` (`role_id`),
    INDEX `idx_menu_item_id` (`menu_item_id`),
    UNIQUE KEY `unique_role_menu` (`role_id`, `menu_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 4: Add foreign keys after tables are created
ALTER TABLE `menu_permissions` 
ADD CONSTRAINT `fk_menu_permissions_role_id` 
FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE;

ALTER TABLE `menu_permissions` 
ADD CONSTRAINT `fk_menu_permissions_menu_item_id` 
FOREIGN KEY (`menu_item_id`) REFERENCES `menu_items`(`id`) ON DELETE CASCADE;

-- Step 5: Insert basic menu items (using UUIDBIN for proper binary UUIDs)
INSERT INTO `menu_items` (`id`, `path`, `label`, `icon`, `sort_order`, `is_active`) VALUES
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440001'), '/', 'Dashboard', 'dashboard', 1, 1),
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440002'), '/profile', 'Profilim', 'account_circle', 2, 1),
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440003'), '/cases', 'Dosyalar', 'folder', 3, 1),
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440004'), '/mediation', 'Arabuluculuk', 'handshake', 4, 1),
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440005'), '/clients', 'Müvekkiller', 'group', 5, 1),
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440006'), '/finance/cash', 'Kasa', 'account_balance_wallet', 6, 1),
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440007'), '/calendar', 'Takvim', 'calendar_month', 7, 1),
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440008'), '/users', 'Kullanıcılar & Roller', 'manage_accounts', 8, 1),
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440009'), '/documents', 'Dokümanlar', 'folder_open', 9, 1),
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440010'), '/notifications', 'Bildirimler', 'notifications', 10, 1),
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440011'), '/workflow', 'Workflow', 'route', 11, 1),
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440012'), '/menu-management', 'Menü Yönetimi', 'menu', 12, 1),
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440013'), '/search', 'Arama', 'search', 13, 1);

-- Step 6: Get role IDs
SET @admin_role_id = (SELECT id FROM roles WHERE `key` = 'administrator' LIMIT 1);
SET @lawyer_role_id = (SELECT id FROM roles WHERE `key` = 'lawyer' LIMIT 1);

-- Step 7: Insert permissions for administrator (all menus visible)
INSERT INTO `menu_permissions` (`id`, `role_id`, `menu_item_id`, `is_visible`)
SELECT 
    UUID_TO_BIN(CONCAT('550e8400-e29b-41d4-a716-44665544', LPAD(menu_item_index, 3, '0'))) as id,
    @admin_role_id as role_id,
    id as menu_item_id,
    1 as is_visible
FROM menu_items
WHERE @admin_role_id IS NOT NULL;

-- Step 8: Insert permissions for lawyer (restricted menus - exclude profile, users, workflow, menu-management)
INSERT INTO `menu_permissions` (`id`, `role_id`, `menu_item_id`, `is_visible`) VALUES
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440100'), @lawyer_role_id, UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440001'), 1), -- Dashboard
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440101'), @lawyer_role_id, UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440003'), 1), -- Dosyalar
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440102'), @lawyer_role_id, UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440004'), 1), -- Arabuluculuk
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440103'), @lawyer_role_id, UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440005'), 1), -- Müvekkiller
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440104'), @lawyer_role_id, UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440006'), 1), -- Kasa
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440105'), @lawyer_role_id, UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440007'), 1), -- Takvim
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440106'), @lawyer_role_id, UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440009'), 1), -- Dokümanlar
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440107'), @lawyer_role_id, UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440010'), 1), -- Bildirimler
(UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440108'), @lawyer_role_id, UUID_TO_BIN('550e8400-e29b-41d4-a716-446655440013'), 1); -- Arama

-- Verification queries
SELECT COUNT(*) as menu_items_count FROM menu_items;
SELECT COUNT(*) as menu_permissions_count FROM menu_permissions;
SELECT 
    mi.path, 
    mi.label, 
    r.name as role_name, 
    mp.is_visible 
FROM menu_permissions mp
JOIN menu_items mi ON mp.menu_item_id = mi.id
JOIN roles r ON mp.role_id = r.id
ORDER BY r.name, mi.sort_order;

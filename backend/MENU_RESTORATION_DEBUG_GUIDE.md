# Menu Restoration Debug Guide

## Problem Analysis

The `improved-emergency-menu-restore.php` script is hanging at "Step 3: Creating menu items..." due to several potential issues:

### Identified Causes:

1. **Large Transaction Block**: The script uses a single large transaction (lines 47-195) that might be causing a deadlock or timeout
2. **Foreign Key Constraint Issues**: When creating sub-menu items, there might be an issue with the foreign key constraint on parent_id
3. **Database Connection Timeout**: The script might be hitting a database timeout during the transaction
4. **Memory Issues**: The script might be running out of memory when processing all menu items at once
5. **Model vs Direct DB Usage**: The script uses direct DB operations instead of Eloquent models, which might be causing issues

## Solutions

### Solution 1: Simple Menu Restoration Script (`simple-menu-restore.php`)

This script addresses the identified issues by:

- **Breaking down the large transaction** into smaller, individual operations
- **Adding error handling** for each menu item creation
- **Increasing time and memory limits**
- **Using step-by-step approach** with detailed logging
- **Processing items individually** instead of in batches

**To run:**
```bash
cd backend
php simple-menu-restore.php
```

### Solution 2: SQL-Based Menu Restoration Script (`sql-menu-restore.php`)

This is the most robust solution that:

- **Uses raw SQL queries** instead of ORM operations
- **Disables foreign key checks** temporarily during data clearing
- **Pre-generates all UUIDs** to avoid conflicts
- **Uses bulk INSERT operations** for better performance
- **Minimal transaction usage**

**To run:**
```bash
cd backend
php sql-menu-restore.php
```

### Solution 3: Direct SQL File (menu-restore.sql)

For maximum reliability, you can use this direct SQL file:

```sql
-- Clear existing data
SET FOREIGN_KEY_CHECKS = 0;
DELETE FROM menu_permissions;
DELETE FROM menu_items;
SET FOREIGN_KEY_CHECKS = 1;

-- Main menu items
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

-- Sub-menu items
INSERT INTO menu_items (id, path, label, icon, sort_order, is_active, parent_id, created_at, updated_at) VALUES
(UUID(), '/mediation/list', 'Arabuluculuk Dosyaları', 'list', 1, 1, @mediation_id, NOW(), NOW()),
(UUID(), '/mediation/new', 'Yeni Arabuluculuk Başvurusu', 'add', 2, 1, @mediation_id, NOW(), NOW()),
(UUID(), '/arbitration', 'Arabuluculuk Başvuruları', 'gavel', 3, 1, @mediation_id, NOW(), NOW()),
(UUID(), '/arbitration/dashboard', 'Arabuluculuk İstatistikleri', 'bar_chart', 4, 1, @mediation_id, NOW(), NOW());

-- Get role IDs
SET @admin_role_id = (SELECT id FROM roles WHERE `key` = 'administrator' LIMIT 1);
SET @lawyer_role_id = (SELECT id FROM roles WHERE `key` = 'lawyer' LIMIT 1);

-- Create administrator permissions (all menus)
INSERT INTO menu_permissions (id, role_id, menu_item_id, is_visible, created_at, updated_at)
SELECT UUID(), @admin_role_id, id, 1, NOW(), NOW() FROM menu_items;

-- Create lawyer permissions (restricted)
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
```

**To run:**
```bash
cd backend
mysql -u username -p database_name < menu-restore.sql
```

## Recommended Approach

1. **First try**: `sql-menu-restore.php` (most robust)
2. **If that fails**: `simple-menu-restore.php` (more verbose debugging)
3. **As last resort**: Direct SQL file execution

## Verification

After running any of these solutions, verify the restoration by:

1. **Check the counts**: Should show 17 menu items and 34 permissions (17 for admin + 17 for lawyer)
2. **Test in the application**: Login as both administrator and lawyer to verify menu visibility
3. **Check database directly**:
   ```sql
   SELECT COUNT(*) FROM menu_items; -- Should be 17
   SELECT COUNT(*) FROM menu_permissions; -- Should be 34
   ```

## Prevention

To prevent this issue in the future:

1. **Use smaller transactions** instead of large ones
2. **Add proper error handling** for each operation
3. **Implement proper logging** to identify issues quickly
4. **Consider using seeder classes** instead of standalone scripts
5. **Test restoration scripts** in development before production use
-- Script to check and fix user role assignments for user ID 22 (alihaydaraslim@gmail.com)

-- Check if user exists
SELECT 'User Check:' as info;
SELECT id, name, email FROM users WHERE id = 22;

-- Check if administrator role exists
SELECT 'Administrator Role Check:' as info;
SELECT id, name, `key` FROM roles WHERE `key` = 'administrator';

-- Create administrator role if it doesn't exist
INSERT IGNORE INTO roles (name, `key`, description, created_at, updated_at) 
VALUES ('Administrator', 'administrator', 'System administrator with full access', NOW(), NOW());

-- Get the administrator role ID
SET @admin_role_id = (SELECT id FROM roles WHERE `key` = 'administrator' LIMIT 1);

-- Check current user-role assignments
SELECT 'Current User Roles:' as info;
SELECT ur.user_id, ur.role_id, r.name, r.`key` 
FROM user_roles ur 
JOIN roles r ON ur.role_id = r.id 
WHERE ur.user_id = 22;

-- Assign administrator role to user if not already assigned
INSERT IGNORE INTO user_roles (user_id, role_id, created_at, updated_at) 
VALUES (22, @admin_role_id, NOW(), NOW());

-- Verify final assignment
SELECT 'Final User Roles:' as info;
SELECT ur.user_id, ur.role_id, r.name, r.`key` 
FROM user_roles ur 
JOIN roles r ON ur.role_id = r.id 
WHERE ur.user_id = 22;

-- Also check if user has any menu permissions
SELECT 'User Menu Permissions:' as info;
SELECT mp.user_id, mp.menu_item_id, mi.title, mi.route, mp.can_view, mp.can_create, mp.can_edit, mp.can_delete
FROM menu_permissions mp
JOIN menu_items mi ON mp.menu_item_id = mi.id
WHERE mp.user_id = 22;

-- Grant full menu permissions to administrator user
INSERT IGNORE INTO menu_permissions (user_id, menu_item_id, can_view, can_create, can_edit, can_delete, created_at, updated_at)
SELECT 22, mi.id, 1, 1, 1, 1, NOW(), NOW()
FROM menu_items mi
WHERE mi.id NOT IN (
    SELECT menu_item_id FROM menu_permissions WHERE user_id = 22
);

SELECT 'Script completed!' as info;
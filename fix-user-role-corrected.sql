-- Script to check and fix user role assignments for user ID 22 (alihaydaraslim@gmail.com)
-- CORRECTED VERSION - Fixed table structure issues

-- Check if user exists
SELECT 'User Check:' as info;
SELECT id, name, email FROM users WHERE id = 22;

-- Check if administrator role exists
SELECT 'Administrator Role Check:' as info;
SELECT id, name, `key` FROM roles WHERE `key` = 'administrator';

-- Create administrator role if it doesn't exist (removed description column)
INSERT IGNORE INTO roles (name, `key`) 
VALUES ('Administrator', 'administrator');

-- Get the administrator role ID
SET @admin_role_id = (SELECT id FROM roles WHERE `key` = 'administrator' LIMIT 1);

-- Check current user-role assignments
SELECT 'Current User Roles:' as info;
SELECT ur.user_id, ur.role_id, r.name, r.`key` 
FROM user_roles ur 
JOIN roles r ON ur.role_id = r.id 
WHERE ur.user_id = 22;

-- Assign administrator role to user if not already assigned (removed created_at, updated_at)
INSERT IGNORE INTO user_roles (user_id, role_id) 
VALUES (22, @admin_role_id);

-- Verify final assignment
SELECT 'Final User Roles:' as info;
SELECT ur.user_id, ur.role_id, r.name, r.`key` 
FROM user_roles ur 
JOIN roles r ON ur.role_id = r.id 
WHERE ur.user_id = 22;

-- Check if user has any role-based menu permissions
SELECT 'User Role Menu Permissions:' as info;
SELECT mp.role_id, r.name as role_name, mp.menu_item_id, mi.label, mi.path, mp.is_visible
FROM menu_permissions mp
JOIN menu_items mi ON mp.menu_item_id = mi.id
JOIN roles r ON mp.role_id = r.id
JOIN user_roles ur ON ur.role_id = r.id
WHERE ur.user_id = 22;

-- Grant full menu permissions to administrator role
INSERT IGNORE INTO menu_permissions (role_id, menu_item_id, is_visible)
SELECT @admin_role_id, mi.id, 1
FROM menu_items mi
WHERE mi.id NOT IN (
    SELECT menu_item_id FROM menu_permissions WHERE role_id = @admin_role_id
);

-- Verify final menu permissions for administrator role
SELECT 'Final Administrator Role Menu Permissions:' as info;
SELECT mp.role_id, r.name as role_name, mp.menu_item_id, mi.label, mi.path, mp.is_visible
FROM menu_permissions mp
JOIN menu_items mi ON mp.menu_item_id = mi.id
JOIN roles r ON mp.role_id = r.id
WHERE r.`key` = 'administrator';

SELECT 'Script completed!' as info;
-- Arabulucu Ücret Hesaplama ve Fatura Yönetimi menü öğeleri

-- Arabuluculuk parent ID'sini al
SET @mediation_parent_id = (SELECT id FROM menu_items WHERE path = '/mediation' LIMIT 1);

-- Arabulucu Ücret Hesaplama menü öğelerini ekle
INSERT INTO menu_items (id, path, label, icon, sort_order, is_active, parent_id, created_at, updated_at) VALUES
(UUID(), '/mediation/fee-calculator', 'Ücret Hesaplama', 'calculate', 5, 1, @mediation_parent_id, NOW(), NOW()),
(UUID(), '/mediation/fee-history', 'Hesaplama Geçmişi', 'history', 6, 1, @mediation_parent_id, NOW(), NOW());

-- Finans parent ID'sini al
SET @finance_parent_id = (SELECT id FROM menu_items WHERE path = '/finance/cash' LIMIT 1);

-- Fatura yönetimi menü öğelerini ekle
INSERT INTO menu_items (id, path, label, icon, sort_order, is_active, parent_id, created_at, updated_at) VALUES
(UUID(), '/invoices', 'Faturalar', 'receipt', 7, 1, @finance_parent_id, NOW(), NOW()),
(UUID(), '/invoices/create', 'Yeni Fatura', 'add', 1, 1, (SELECT id FROM menu_items WHERE path = '/invoices' LIMIT 1), NOW(), NOW()),
(UUID(), '/invoices/list', 'Fatura Listesi', 'list', 2, 1, (SELECT id FROM menu_items WHERE path = '/invoices' LIMIT 1), NOW(), NOW()),
(UUID(), '/invoices/stats', 'Fatura İstatistikleri', 'bar_chart', 3, 1, (SELECT id FROM menu_items WHERE path = '/invoices' LIMIT 1), NOW(), NOW());

-- Rollerin ID'lerini al
SET @admin_role_id = (SELECT id FROM roles WHERE `key` = 'administrator' LIMIT 1);
SET @lawyer_role_id = (SELECT id FROM roles WHERE `key` = 'lawyer' LIMIT 1);

-- Yeni menü öğelerini administrator rolüne ekle
INSERT INTO menu_permissions (id, role_id, menu_item_id, is_visible, created_at, updated_at)
SELECT UUID(), @admin_role_id, id, 1, NOW(), NOW() 
FROM menu_items 
WHERE path IN (
    '/mediation/fee-calculator', 
    '/mediation/fee-history', 
    '/invoices', 
    '/invoices/create', 
    '/invoices/list', 
    '/invoices/stats'
);

-- Yeni menü öğelerini lawyer rolüne ekle
INSERT INTO menu_permissions (id, role_id, menu_item_id, is_visible, created_at, updated_at)
SELECT UUID(), @lawyer_role_id, id, 1, NOW(), NOW() 
FROM menu_items 
WHERE path IN (
    '/mediation/fee-calculator', 
    '/mediation/fee-history', 
    '/invoices', 
    '/invoices/create', 
    '/invoices/list', 
    '/invoices/stats'
);

-- Finans menüsünün sort_order'ını güncelle
UPDATE menu_items SET sort_order = 6 WHERE path = '/finance/cash';

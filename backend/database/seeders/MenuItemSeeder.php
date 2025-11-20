<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;
use App\Models\MenuPermission;
use App\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        // Use Eloquent capsule transaction instead of Laravel DB facade
        \Illuminate\Database\Capsule\Manager::transaction(function () {
            // Ana menü öğelerini oluştur
            $menuItems = [
                ['path' => '/', 'label' => 'Dashboard', 'icon' => 'dashboard', 'sort_order' => 1, 'parent_id' => null],
                ['path' => '/profile', 'label' => 'Profilim', 'icon' => 'account_circle', 'sort_order' => 2, 'parent_id' => null],
                ['path' => '/cases', 'label' => 'Dosyalar', 'icon' => 'folder', 'sort_order' => 3, 'parent_id' => null],
                ['path' => '/mediation', 'label' => 'Arabuluculuk', 'icon' => 'handshake', 'sort_order' => 4, 'parent_id' => null],
                ['path' => '/clients', 'label' => 'Müvekkiller', 'icon' => 'group', 'sort_order' => 5, 'parent_id' => null],
                ['path' => '/finance/cash', 'label' => 'Kasa', 'icon' => 'account_balance_wallet', 'sort_order' => 6, 'parent_id' => null],
                ['path' => '/calendar', 'label' => 'Takvim', 'icon' => 'calendar_month', 'sort_order' => 7, 'parent_id' => null],
                ['path' => '/users', 'label' => 'Kullanıcılar & Roller', 'icon' => 'manage_accounts', 'sort_order' => 8, 'parent_id' => null],
                ['path' => '/documents', 'label' => 'Dokümanlar', 'icon' => 'folder_open', 'sort_order' => 9, 'parent_id' => null],
                ['path' => '/notifications', 'label' => 'Bildirimler', 'icon' => 'notifications', 'sort_order' => 10, 'parent_id' => null],
                ['path' => '/workflow', 'label' => 'Workflow', 'icon' => 'route', 'sort_order' => 11, 'parent_id' => null],
                ['path' => '/menu-management', 'label' => 'Menü Yönetimi', 'icon' => 'menu', 'sort_order' => 12, 'parent_id' => null],
                ['path' => '/search', 'label' => 'Arama', 'icon' => 'search', 'sort_order' => 13, 'parent_id' => null],
            ];

            $createdMenuItems = [];
            foreach ($menuItems as $item) {
                $menuItem = MenuItem::create([
                    'id' => Str::uuid(),
                    'path' => $item['path'],
                    'label' => $item['label'],
                    'icon' => $item['icon'],
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                    'parent_id' => $item['parent_id'],
                ]);
                $createdMenuItems[$item['path']] = $menuItem;
            }

            // Arabuluculuk alt menü öğelerini oluştur
            $mediationParent = $createdMenuItems['/mediation'];
            $mediationSubItems = [
                ['path' => '/mediation/list', 'label' => 'Arabuluculuk Dosyaları', 'icon' => 'list', 'sort_order' => 1],
                ['path' => '/mediation/new', 'label' => 'Yeni Arabuluculuk Başvurusu', 'icon' => 'add', 'sort_order' => 2],
                ['path' => '/arbitration', 'label' => 'Arabuluculuk Başvuruları', 'icon' => 'gavel', 'sort_order' => 3],
                ['path' => '/arbitration/dashboard', 'label' => 'Arabuluculuk İstatistikleri', 'icon' => 'bar_chart', 'sort_order' => 4],
            ];

            foreach ($mediationSubItems as $item) {
                $subMenuItem = MenuItem::create([
                    'id' => Str::uuid(),
                    'path' => $item['path'],
                    'label' => $item['label'],
                    'icon' => $item['icon'],
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                    'parent_id' => $mediationParent->id,
                ]);
                $createdMenuItems[$item['path']] = $subMenuItem;
            }

            // Rolleri al
            $adminRole = Role::where('key', 'administrator')->first();
            $lawyerRole = Role::where('key', 'lawyer')->first();

            // Administrator için tüm menüleri göster
            if ($adminRole) {
                foreach ($createdMenuItems as $menuItem) {
                    MenuPermission::create([
                        'id' => Str::uuid(),
                        'role_id' => $adminRole->id,
                        'menu_item_id' => $menuItem->id,
                        'is_visible' => true,
                    ]);
                }
            }

            // Avukat için kısıtlı menüleri göster (mevcut mantık)
            if ($lawyerRole) {
                // Avukat rolü için görünür olacak menüler
                $visibleForLawyer = [
                    '/', // Dashboard
                    '/cases', // Dosyalar
                    '/mediation', // Arabuluculuk (ana menü)
                    '/mediation/list', // Arabuluculuk Dosyaları
                    '/mediation/new', // Yeni Arabuluculuk Başvurusu
                    '/arbitration', // Arabuluculuk Başvuruları
                    '/arbitration/dashboard', // Arabuluculuk İstatistikleri
                    '/clients', // Müvekkiller
                    '/finance/cash', // Kasa
                    '/calendar', // Takvim
                    '/documents', // Dokümanlar
                    '/notifications', // Bildirimler
                    '/search', // Arama
                ];

                foreach ($createdMenuItems as $path => $menuItem) {
                    $isVisible = in_array($path, $visibleForLawyer);
                    MenuPermission::create([
                        'id' => Str::uuid(),
                        'role_id' => $lawyerRole->id,
                        'menu_item_id' => $menuItem->id,
                        'is_visible' => $isVisible,
                    ]);
                }
            }
        });
    }
}

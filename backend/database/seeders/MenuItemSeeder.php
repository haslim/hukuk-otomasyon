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
        DB::transaction(function () {
            // Menü öğelerini oluştur
            $menuItems = [
                ['path' => '/', 'label' => 'Dashboard', 'icon' => 'dashboard', 'sort_order' => 1],
                ['path' => '/profile', 'label' => 'Profilim', 'icon' => 'account_circle', 'sort_order' => 2],
                ['path' => '/cases', 'label' => 'Dosyalar', 'icon' => 'folder', 'sort_order' => 3],
                ['path' => '/mediation', 'label' => 'Arabuluculuk', 'icon' => 'handshake', 'sort_order' => 4],
                ['path' => '/clients', 'label' => 'Müvekkiller', 'icon' => 'group', 'sort_order' => 5],
                ['path' => '/finance/cash', 'label' => 'Kasa', 'icon' => 'account_balance_wallet', 'sort_order' => 6],
                ['path' => '/calendar', 'label' => 'Takvim', 'icon' => 'calendar_month', 'sort_order' => 7],
                ['path' => '/users', 'label' => 'Kullanıcılar & Roller', 'icon' => 'manage_accounts', 'sort_order' => 8],
                ['path' => '/documents', 'label' => 'Dokümanlar', 'icon' => 'folder_open', 'sort_order' => 9],
                ['path' => '/notifications', 'label' => 'Bildirimler', 'icon' => 'notifications', 'sort_order' => 10],
                ['path' => '/workflow', 'label' => 'Workflow', 'icon' => 'route', 'sort_order' => 11],
                ['path' => '/menu-management', 'label' => 'Menü Yönetimi', 'icon' => 'menu', 'sort_order' => 12],
                ['path' => '/search', 'label' => 'Arama', 'icon' => 'search', 'sort_order' => 13],
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
                ]);
                $createdMenuItems[$item['path']] = $menuItem;
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
                    '/mediation', // Arabuluculuk
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

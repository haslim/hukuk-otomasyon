<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as DB;

return new class extends Migration
{
    /**
     * Run migrations.
     */
    public function up(): void
    {
        DB::schema()->create('menu_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('path')->unique(); // '/users', '/profile', etc.
            $table->string('label'); // 'Kullanıcılar & Roller', 'Profilim'
            $table->string('icon'); // 'manage_accounts', 'account_circle'
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        DB::schema()->create('menu_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('role_id');
            $table->uuid('menu_item_id');
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
            
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('menu_item_id')->references('id')->on('menu_items')->onDelete('cascade');
            
            $table->unique(['role_id', 'menu_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::schema()->dropIfExists('menu_permissions');
        DB::schema()->dropIfExists('menu_items');
    }
};

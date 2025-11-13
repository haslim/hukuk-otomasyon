<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up()
    {
        Capsule::schema()->create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Capsule::schema()->create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('key')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Capsule::schema()->create('permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('key')->unique();
            $table->timestamps();
            $table->softDeletes();
        });

        Capsule::schema()->create('user_roles', function (Blueprint $table) {
            $table->uuid('user_id');
            $table->uuid('role_id');
        });

        Capsule::schema()->create('role_permissions', function (Blueprint $table) {
            $table->uuid('role_id');
            $table->uuid('permission_id');
        });
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('role_permissions');
        Capsule::schema()->dropIfExists('user_roles');
        Capsule::schema()->dropIfExists('permissions');
        Capsule::schema()->dropIfExists('roles');
        Capsule::schema()->dropIfExists('users');
    }
};

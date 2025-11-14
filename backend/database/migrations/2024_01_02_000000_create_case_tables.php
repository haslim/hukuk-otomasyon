<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up()
    {
        Capsule::schema()->create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->enum('type', ['real', 'legal']);
            $table->string('identifier')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->json('labels')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->fullText(['name', 'identifier', 'notes']);
        });

        Capsule::schema()->create('cases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('client_id');
            $table->string('case_no')->unique();
            $table->string('type');
            $table->string('title');
            $table->text('subject')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->fullText(['title', 'subject', 'case_no']);
        });

        Capsule::schema()->create('case_parties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('case_id');
            $table->string('role');
            $table->string('name');
            $table->string('identifier')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Capsule::schema()->create('hearings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('case_id');
            $table->dateTime('hearing_date');
            $table->string('court');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Capsule::schema()->create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('case_id')->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->string('title');
            $table->date('due_date')->nullable();
            $table->enum('status', ['open','in_progress','completed'])->default('open');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('tasks');
        Capsule::schema()->dropIfExists('hearings');
        Capsule::schema()->dropIfExists('case_parties');
        Capsule::schema()->dropIfExists('cases');
        Capsule::schema()->dropIfExists('clients');
    }
};

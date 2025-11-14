<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

return new class {
    public function up()
    {
        Capsule::schema()->create('finance_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('case_id')->nullable();
            $table->enum('type', ['income','expense']);
            $table->decimal('amount', 12, 2);
            $table->date('occurred_on');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Capsule::schema()->create('workflow_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('case_type');
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Capsule::schema()->create('workflow_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id');
            $table->string('title');
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('order');
            $table->timestamps();
            $table->softDeletes();
        });

        Capsule::schema()->create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('subject');
            $table->json('payload');
            $table->json('channels')->nullable();
            $table->enum('status', ['pending','sent','failed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        Capsule::schema()->create('pending_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('payload');
            $table->enum('status', ['pending','sent','failed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        Capsule::schema()->create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('entity_type');
            $table->string('entity_id');
            $table->string('action');
            $table->json('metadata')->nullable();
            $table->string('ip')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('audit_logs');
        Capsule::schema()->dropIfExists('pending_notifications');
        Capsule::schema()->dropIfExists('notifications');
        Capsule::schema()->dropIfExists('workflow_steps');
        Capsule::schema()->dropIfExists('workflow_templates');
        Capsule::schema()->dropIfExists('finance_transactions');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('finance_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('case_id')->nullable();
            $table->enum('type', ['income','expense']);
            $table->decimal('amount', 12, 2);
            $table->date('occurred_on');
            $table->string('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('workflow_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('case_type');
            $table->json('tags')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('workflow_steps', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('template_id');
            $table->string('title');
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('order');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->string('subject');
            $table->json('payload');
            $table->json('channels')->nullable();
            $table->enum('status', ['pending','sent','failed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('pending_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('payload');
            $table->enum('status', ['pending','sent','failed'])->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('pending_notifications');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('workflow_steps');
        Schema::dropIfExists('workflow_templates');
        Schema::dropIfExists('finance_transactions');
    }
};

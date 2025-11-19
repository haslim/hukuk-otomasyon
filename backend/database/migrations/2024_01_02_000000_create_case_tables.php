<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
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

        Schema::create('cases', function (Blueprint $table) {
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
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });

        Schema::create('case_parties', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('case_id');
            $table->string('role');
            $table->string('name');
            $table->string('identifier')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('case_id')->references('id')->on('cases')->onDelete('cascade');
        });

        Schema::create('hearings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('case_id');
            $table->dateTime('hearing_date');
            $table->string('court');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('case_id')->references('id')->on('cases')->onDelete('cascade');
        });

        Schema::create('tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('case_id')->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->string('title');
            $table->date('due_date')->nullable();
            $table->enum('status', ['open','in_progress','completed'])->default('open');
            $table->timestamps();
            $table->softDeletes();
            $table->foreign('case_id')->references('id')->on('cases')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
        Schema::dropIfExists('hearings');
        Schema::dropIfExists('case_parties');
        Schema::dropIfExists('cases');
        Schema::dropIfExists('clients');
    }
};

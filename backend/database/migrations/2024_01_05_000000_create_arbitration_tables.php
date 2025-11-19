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
        Schema::create('arbitration_applications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('application_no')->unique();
            $table->json('applicant_info'); // Başvuran bilgileri
            $table->json('respondent_info'); // Cevaplayan bilgileri
            $table->enum('application_type', [
                'ihtiyati',
                'ihtiyati_tedbir', 
                'ticari',
                'is_hukuku',
                'tuketici',
                'icra',
                'diger'
            ])->default('diger');
            $table->text('subject_matter'); // Uyuşmazlık konusu
            $table->decimal('monetary_value', 15, 2)->nullable(); // Uyuşmazlık değeri
            $table->string('currency', 3)->default('TRY'); // Para birimi
            $table->date('application_date'); // Başvuru tarihi
            $table->enum('status', [
                'pending',      // Beklemede
                'accepted',     // Kabul edildi
                'rejected',     // Reddedildi
                'in_progress',  // İşlemde
                'completed',    // Tamamlandı
                'cancelled'     // İptal edildi
            ])->default('pending');
            $table->uuid('created_by')->nullable(); // Oluşturan kullanıcı
            $table->uuid('mediator_id')->nullable(); // Atanan arabulucu
            $table->text('notes')->nullable(); // Notlar
            $table->json('metadata')->nullable(); // Ek veriler
            $table->timestamps();
            $table->softDeletes();

            // Index'ler
            $table->index(['status', 'application_date']);
            $table->index(['created_by']);
            $table->index(['mediator_id']);
            $table->index(['application_type']);

            // Foreign key'ler
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('mediator_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('application_documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('application_id');
            $table->enum('document_type', [
                'basvuru_dilekcesi',    // Başvuru dilekçesi
                'delil',                 // Delil
                'vekaletname',          // Vekaletname
                'kimlik',               // Kimlik
                'sirket_belgesi',       // Şirket belgesi
                'vergi_borcu_yoktur',   // Vergi borcu yoktur yazısı
                'adres_kaydi',          // Adres kaydı
                'diger'                 // Diğer
            ])->default('diger');
            $table->string('title');
            $table->string('file_path');
            $table->integer('file_size');
            $table->string('mime_type');
            $table->uuid('uploaded_by')->nullable();
            $table->text('ocr_text')->nullable(); // OCR ile çıkarılan metin
            $table->text('ai_summary')->nullable(); // AI tarafından özetlenen içerik
            $table->boolean('is_public')->default(false); // Tarafların görüp göremeyeceği
            $table->json('metadata')->nullable(); // Ek veriler
            $table->timestamps();
            $table->softDeletes();

            // Index'ler
            $table->index(['application_id', 'document_type']);
            $table->index(['uploaded_by']);

            // Foreign key'ler
            $table->foreign('application_id')->references('id')->on('arbitration_applications')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('application_timeline', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('application_id');
            $table->string('event_type'); // created, updated, document_added, status_changed, etc.
            $table->text('description');
            $table->json('event_data')->nullable(); // Olay verileri
            $table->uuid('user_id')->nullable(); // Olayı oluşturan kullanıcı
            $table->timestamps();

            // Index'ler
            $table->index(['application_id', 'created_at']);

            // Foreign key'ler
            $table->foreign('application_id')->references('id')->on('arbitration_applications')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('application_timeline');
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('arbitration_applications');
    }
};

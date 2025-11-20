<?php

use App\Models\BaseModel;
use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public function up()
    {
        // Arabulucu ücret hesaplama tablosu
        Capsule::schema()->create('mediation_fee_calculations', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('case_id')->nullable();
            $table->uuid('client_id')->nullable();
            $table->string('calculation_type'); // 'standard', 'urgent', 'commercial'
            $table->integer('party_count');
            $table->decimal('subject_value', 15, 2);
            $table->decimal('base_fee', 15, 2);
            $table->decimal('vat_rate', 5, 2)->default(18.00);
            $table->decimal('vat_amount', 15, 2);
            $table->decimal('total_fee', 15, 2);
            $table->decimal('fee_per_party', 15, 2);
            $table->json('calculation_details')->nullable(); // Detaylı hesaplama adımları
            $table->date('calculation_date');
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('case_id')->references('id')->on('cases')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['case_id', 'client_id']);
            $table->index('calculation_date');
        });

        // Fatura tablosu
        Capsule::schema()->create('invoices', function ($table) {
            $table->uuid('id')->primary();
            $table->string('invoice_number')->unique();
            $table->uuid('calculation_id')->nullable();
            $table->uuid('client_id');
            $table->uuid('case_id')->nullable();
            $table->date('issue_date');
            $table->date('due_date');
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 15, 2);
            $table->decimal('vat_rate', 5, 2)->default(18.00);
            $table->decimal('vat_amount', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->json('client_details')->nullable(); // Fatura için müşteri bilgileri
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->timestamps();
            
            $table->foreign('calculation_id')->references('id')->on('mediation_fee_calculations')->onDelete('set null');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('case_id')->references('id')->on('cases')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['client_id', 'status']);
            $table->index('invoice_number');
            $table->index('issue_date');
            $table->index('due_date');
        });

        // Fatura kalemleri tablosu
        Capsule::schema()->create('invoice_items', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id');
            $table->string('item_type'); // 'fee', 'expense', 'tax', 'other'
            $table->string('description');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('line_total', 15, 2);
            $table->decimal('vat_rate', 5, 2)->default(0);
            $table->decimal('vat_amount', 15, 2)->default(0);
            $table->string('reference_id')->nullable(); // İlgili hesaplama ID'si vb.
            $table->timestamps();
            
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            
            $table->index('invoice_id');
        });

        // Ödemeler tablosu
        Capsule::schema()->create('invoice_payments', function ($table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id');
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method'); // 'cash', 'bank_transfer', 'credit_card', 'check'
            $table->string('payment_reference')->nullable(); // Banka referans no
            $table->text('notes')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['invoice_id', 'payment_date']);
        });

        // Arabulucu tarife tabloları (resmi ücret tarifesi)
        Capsule::schema()->create('mediation_fee_tariffs', function ($table) {
            $table->uuid('id')->primary();
            $table->string('tariff_type'); // 'standard', 'commercial', 'urgent'
            $table->decimal('min_value', 15, 2);
            $table->decimal('max_value', 15, 2);
            $table->decimal('fee_amount', 15, 2);
            $table->decimal('fee_percentage', 5, 2)->nullable();
            $table->string('party_count_rule'); // 'per_party', 'total'
            $table->boolean('is_active')->default(true);
            $table->date('valid_from');
            $table->date('valid_to')->nullable();
            $table->text('description')->nullable();
            $table->uuid('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            
            $table->index(['tariff_type', 'min_value', 'max_value']);
            $table->index('is_active');
        });
    }

    public function down()
    {
        Capsule::schema()->dropIfExists('invoice_payments');
        Capsule::schema()->dropIfExists('invoice_items');
        Capsule::schema()->dropIfExists('invoices');
        Capsule::schema()->dropIfExists('mediation_fee_calculations');
        Capsule::schema()->dropIfExists('mediation_fee_tariffs');
    }
};

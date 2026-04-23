<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * CRM Tables - Customer & Supplier Management
     */
    public function up(): void
    {
        // Customers
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('customer_code', 50);
            $table->string('name');
            $table->string('trade_name')->nullable();
            $table->string('tax_registration_number')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('national_id')->nullable();
            $table->enum('type', ['individual', 'corporate'])->default('corporate');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('governorate')->nullable();
            $table->string('country')->default('EG');
            $table->decimal('credit_limit', 20, 2)->default(0);
            $table->integer('payment_terms_days')->default(30);
            $table->decimal('balance', 20, 2)->default(0);
            $table->string('currency', 3)->default('EGP');
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'customer_code']);
            $table->index(['company_id', 'type']);
            $table->index(['tax_registration_number']);
        });

        // Suppliers
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('supplier_code', 50);
            $table->string('name');
            $table->string('trade_name')->nullable();
            $table->string('tax_registration_number')->nullable();
            $table->string('vat_number')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('governorate')->nullable();
            $table->string('country')->default('EG');
            $table->integer('payment_terms_days')->default(30);
            $table->decimal('credit_limit', 20, 2)->default(0);
            $table->decimal('balance', 20, 2)->default(0);
            $table->string('currency', 3)->default('EGP');
            $table->integer('rating')->default(5);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'supplier_code']);
            $table->index(['tax_registration_number']);
        });

        // Contacts
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });

        // Party Categories
        Schema::create('party_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name_ar');
            $table->string('name_en');
            $table->enum('type', ['customer', 'supplier']);
            $table->foreignId('parent_category_id')->nullable()->constrained('party_categories')->onDelete('set null');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'type']);
        });

        // Party Category Pivot
        Schema::create('category_party', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('party_categories')->onDelete('cascade');
            $table->string('party_type');
            $table->unsignedBigInteger('party_id');
            $table->timestamps();

            $table->unique(['category_id', 'party_type', 'party_id']);
            $table->index(['party_type', 'party_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_party');
        Schema::dropIfExists('party_categories');
        Schema::dropIfExists('contacts');
        Schema::dropIfExists('suppliers');
        Schema::dropIfExists('customers');
    }
};

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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('tax_registration_number')->unique();
            $table->string('eta_client_id');
            $table->text('eta_client_secret'); // Encrypted
            $table->text('digital_certificate')->nullable(); // e-Signature certificate path/content
            $table->text('certificate_password')->nullable(); // Encrypted
            $table->string('api_base_url')->default('https://api.invoicing.eta.gov.eg');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->json('settings')->nullable(); // Additional company settings
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('code'); // GS1 or Internal Code
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('unit_price', 15, 2)->default(0);
            $table->enum('tax_type', ['VAT', 'TABLE_TAX'])->default('VAT');
            $table->decimal('tax_rate', 5, 2)->default(14.00); // Default VAT in Egypt is 14%
            $table->string('unit_type')->default('EA'); // EA=Each, BOX, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            
            // Invoice Identification
            $table->uuid('uuid')->unique(); // Unique UUID for ETA
            $table->string('invoice_number'); // Internal invoice number
            $table->string('invoice_type')->default('B2B'); // B2B, B2C
            $table->date('issue_date');
            $table->date('delivery_date')->nullable();
            
            // Seller Info (cached from company)
            $table->string('seller_name');
            $table->string('seller_tax_id');
            $table->string('seller_address')->nullable();
            
            // Buyer Info
            $table->string('buyer_name');
            $table->string('buyer_tax_id')->nullable();
            $table->string('buyer_vat_number')->nullable();
            $table->text('buyer_address')->nullable();
            $table->string('buyer_email')->nullable();
            $table->string('buyer_phone')->nullable();
            
            // Calculations
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount_total', 15, 2)->default(0);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('net_total', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2)->default(0);
            
            // ETA Status
            $table->string('eta_submission_id')->nullable();
            $table->enum('status', [
                'DRAFT',
                'SUBMITTED',
                'VALID',
                'INVALID',
                'REJECTED',
                'CANCELLED'
            ])->default('DRAFT');
            $table->text('eta_response')->nullable(); // Full response from ETA
            $table->text('rejection_reason')->nullable();
            
            // Signature & Hash
            $table->text('document_hash')->nullable();
            $table->text('signature')->nullable();
            
            $table->boolean('is_synced')->default(false);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'issue_date']);
            $table->index(['uuid']);
        });

        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->nullable()->constrained()->onDelete('set null');
            
            $table->string('item_code');
            $table->string('item_name');
            $table->text('item_description')->nullable();
            $table->decimal('quantity', 15, 3)->default(1);
            $table->string('unit_type')->default('EA');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->enum('tax_type', ['VAT', 'TABLE_TAX'])->default('VAT');
            $table->decimal('tax_rate', 5, 2)->default(14.00);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('subtotal', 15, 2); // (qty * price) - discount
            $table->decimal('total', 15, 2); // subtotal + tax
            
            $table->timestamps();
        });

        Schema::create('eta_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->text('access_token');
            $table->text('refresh_token')->nullable();
            $table->integer('expires_in');
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['company_id', 'expires_at']);
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->string('user_type')->nullable(); // App\User, System, etc.
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action'); // CREATED, UPDATED, SUBMITTED, CANCELLED, SYNCED
            $table->string('model_type')->nullable(); // App\Models\Invoice, etc.
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'action']);
            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('eta_tokens');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('items');
        Schema::dropIfExists('companies');
    }
};

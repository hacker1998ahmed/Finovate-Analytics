<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * General Ledger / Accounting Tables
     * - Chart of Accounts
     * - Journal Entries
     * - Fiscal Years
     * - Cost Centers
     */
    public function up(): void
    {
        // Chart of Accounts
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('account_code', 50);
            $table->string('account_name_ar');
            $table->string('account_name_en');
            $table->enum('account_type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
            $table->foreignId('parent_account_id')->nullable()->constrained('chart_of_accounts')->onDelete('set null');
            $table->string('currency', 3)->default('EGP');
            $table->decimal('balance', 20, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'account_code']);
            $table->index(['company_id', 'account_type']);
            $table->index(['parent_account_id']);
        });

        // Fiscal Years
        Schema::create('fiscal_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_current')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closing_date')->nullable();
            $table->foreignId('closed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->unique(['company_id', 'name']);
            $table->index(['company_id', 'is_current']);
        });

        // Cost Centers
        Schema::create('cost_centers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('code', 50);
            $table->string('name_ar');
            $table->string('name_en');
            $table->enum('type', ['department', 'project', 'location', 'other']);
            $table->foreignId('parent_cost_center_id')->nullable()->constrained('cost_centers')->onDelete('set null');
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('budget', 20, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'type']);
        });

        // Journal Entries
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('entry_number', 50);
            $table->date('entry_date');
            $table->foreignId('fiscal_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('period_id')->nullable();
            $table->text('description');
            $table->string('reference')->nullable();
            $table->enum('source', ['manual', 'invoice', 'payment', 'payroll', 'purchase', 'sale', 'adjustment']);
            $table->string('source_type')->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->foreignId('posted_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('posted_at')->nullable();
            $table->enum('status', ['draft', 'posted', 'void'])->default('draft');
            $table->boolean('is_reversing')->default(false);
            $table->foreignId('reversed_entry_id')->nullable()->constrained('journal_entries')->onDelete('set null');
            $table->timestamps();

            $table->unique(['company_id', 'entry_number']);
            $table->index(['company_id', 'entry_date']);
            $table->index(['company_id', 'status']);
            $table->index(['source_type', 'source_id']);
        });

        // Journal Entry Lines
        Schema::create('journal_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->decimal('debit', 20, 4)->default(0);
            $table->decimal('credit', 20, 4)->default(0);
            $table->string('currency', 3)->default('EGP');
            $table->decimal('exchange_rate', 18, 6)->default(1);
            $table->foreignId('cost_center_id')->nullable()->constrained('cost_centers')->onDelete('set null');
            $table->foreignId('project_id')->nullable();
            $table->text('description')->nullable();
            $table->integer('line_order')->default(0);
            $table->timestamps();

            $table->index(['journal_entry_id']);
            $table->index(['account_id']);
        });

        // Account Periods
        Schema::create('account_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fiscal_year_id')->constrained()->onDelete('cascade');
            $table->integer('period_number');
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_closed')->default(false);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();

            $table->unique(['fiscal_year_id', 'period_number']);
        });

        // Budgets
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('fiscal_year_id')->constrained()->onDelete('cascade');
            $table->foreignId('account_id')->constrained('chart_of_accounts')->onDelete('cascade');
            $table->foreignId('cost_center_id')->nullable()->constrained('cost_centers')->onDelete('set null');
            $table->decimal('budget_amount', 20, 2);
            $table->decimal('actual_amount', 20, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'fiscal_year_id', 'account_id', 'cost_center_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
        Schema::dropIfExists('account_periods');
        Schema::dropIfExists('journal_entry_lines');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('cost_centers');
        Schema::dropIfExists('fiscal_years');
        Schema::dropIfExists('chart_of_accounts');
    }
};

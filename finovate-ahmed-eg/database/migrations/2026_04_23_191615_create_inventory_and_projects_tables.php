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
        // Inventory/Warehouse Management - إدارة المخازن
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('address')->nullable();
            $table->string('manager_name')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade'); // من جدول items الأصلي
            $table->foreignId('warehouse_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 18, 4)->default(0);
            $table->decimal('reserved_quantity', 18, 4)->default(0);
            $table->decimal('reorder_level', 18, 4)->default(0);
            $table->decimal('reorder_quantity', 18, 4)->default(0);
            $table->decimal('unit_cost', 18, 2)->default(0);
            $table->date('last_purchase_date')->nullable();
            $table->timestamps();
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->string('movement_type'); // in, out, transfer, adjustment
            $table->decimal('quantity', 18, 4);
            $table->decimal('unit_cost', 18, 2)->nullable();
            $table->foreignId('from_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('to_warehouse_id')->nullable()->constrained('warehouses')->onDelete('set null');
            $table->foreignId('reference_id')->nullable(); // رقم الفاتورة أو أمر الصرف
            $table->string('reference_type')->nullable(); // invoice, purchase_order, etc.
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('movement_date');
            $table->timestamps();
        });

        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            $table->string('order_number')->unique();
            $table->date('order_date');
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->string('status')->default('draft'); // draft, sent, received, cancelled
            $table->decimal('total_amount', 18, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 18, 4);
            $table->decimal('unit_price', 18, 2);
            $table->decimal('discount', 18, 2)->default(0);
            $table->decimal('tax', 18, 2)->default(0);
            $table->decimal('total', 18, 2);
            $table->decimal('received_quantity', 18, 4)->default(0);
            $table->timestamps();
        });

        // Projects Management - إدارة المشاريع
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('budget', 18, 2)->default(0);
            $table->decimal('actual_cost', 18, 2)->default(0);
            $table->string('status')->default('planning'); // planning, active, on_hold, completed, cancelled
            $table->integer('priority')->default(3); // 1=high, 2=medium, 3=low
            $table->foreignId('project_manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('project_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('task_name');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->string('status')->default('pending'); // pending, in_progress, completed, cancelled
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('parent_task_id')->nullable()->constrained('project_tasks')->onDelete('cascade');
            $table->integer('priority')->default(3);
            $table->decimal('estimated_hours', 8, 2)->nullable();
            $table->decimal('actual_hours', 8, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('project_timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained('project_tasks')->onDelete('set null');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('work_date');
            $table->decimal('hours', 8, 2);
            $table->text('description')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('project_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->string('expense_type');
            $table->text('description');
            $table->decimal('amount', 18, 2);
            $table->date('expense_date');
            $table->foreignId('vendor_id')->nullable()->constrained()->onDelete('set null');
            $table->string('receipt_number')->nullable();
            $table->string('receipt_path')->nullable();
            $table->boolean('is_billable')->default(true);
            $table->string('status')->default('pending'); // pending, approved, reimbursed
            $table->foreignId('submitted_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // Fixed Assets - الأصول الثابتة
        Schema::create('fixed_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('asset_name');
            $table->string('asset_code')->unique();
            $table->string('category')->nullable(); // مباني، آلات، سيارات، أثاث
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 18, 2);
            $table->decimal('salvage_value', 18, 2)->default(0);
            $table->integer('useful_life_years');
            $table->string('depreciation_method')->default('straight_line'); // straight_line, declining_balance
            $table->decimal('accumulated_depreciation', 18, 2)->default(0);
            $table->string('status')->default('active'); // active, disposed, fully_depreciated
            $table->date('disposal_date')->nullable();
            $table->decimal('disposal_value', 18, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_depreciations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fixed_asset_id')->constrained()->onDelete('cascade');
            $table->string('fiscal_year');
            $table->string('period'); // الشهر أو الربع
            $table->decimal('depreciation_amount', 18, 2);
            $table->decimal('accumulated_depreciation', 18, 2);
            $table->decimal('net_book_value', 18, 2);
            $table->date('depreciation_date');
            $table->boolean('is_posted')->default(false);
            $table->foreignId('journal_entry_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });

        // Bank & Cash Management - البنوك والخزينة
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('account_name');
            $table->string('bank_name');
            $table->string('account_number')->unique();
            $table->string('iban')->nullable();
            $table->string('currency')->default('EGP');
            $table->decimal('balance', 18, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cash_registers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('register_name');
            $table->string('currency')->default('EGP');
            $table->decimal('balance', 18, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->onDelete('cascade');
            $table->string('transaction_type'); // deposit, withdrawal, transfer
            $table->decimal('amount', 18, 2);
            $table->string('currency')->default('EGP');
            $table->date('transaction_date');
            $table->string('reference_number')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('related_invoice_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('related_journal_entry_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });

        // Reports Templates - قوالب التقارير
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->string('report_name');
            $table->string('report_type'); // مالي، ضريبي، مخازن، مشاريع
            $table->text('description')->nullable();
            $table->json('configuration')->nullable(); // إعدادات التقرير
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_templates');
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('cash_registers');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('asset_depreciations');
        Schema::dropIfExists('fixed_assets');
        Schema::dropIfExists('project_expenses');
        Schema::dropIfExists('project_timesheets');
        Schema::dropIfExists('project_tasks');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('warehouses');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * HRMS Tables - Human Resources Management System
     */
    public function up(): void
    {
        // Departments
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('code', 50);
            $table->string('name_ar');
            $table->string('name_en');
            $table->foreignId('parent_department_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('budget', 20, 2)->default(0);
            $table->string('cost_center_code')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id']);
        });

        // Employees
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('employee_code', 50);
            $table->string('first_name');
            $table->string('second_name')->nullable();
            $table->string('third_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('national_id')->unique();
            $table->date('birth_date')->nullable();
            $table->date('hire_date');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->string('job_title')->nullable();
            $table->enum('employment_type', ['full-time', 'part-time', 'contractor', 'intern'])->default('full-time');
            $table->decimal('salary_basic', 15, 2)->default(0);
            $table->decimal('salary_allowances', 15, 2)->default(0);
            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->text('address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->integer('tax_bracket')->default(1);
            $table->decimal('insurance_rate', 5, 2)->default(14.00);
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('status', ['active', 'inactive', 'terminated', 'resigned'])->default('active');
            $table->date('termination_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'employee_code']);
            $table->index(['company_id', 'department_id']);
            $table->index(['national_id']);
        });

        // Leave Types
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('name_ar');
            $table->string('name_en');
            $table->integer('default_days')->default(0);
            $table->boolean('is_paid')->default(true);
            $table->boolean('requires_approval')->default(true);
            $table->boolean('carry_over_allowed')->default(false);
            $table->integer('max_carry_over_days')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Employee Leave Entitlements
        Schema::create('leave_entitlements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');
            $table->integer('year');
            $table->decimal('total_days', 8, 2);
            $table->decimal('used_days', 8, 2)->default(0);
            $table->decimal('pending_days', 8, 2)->default(0);
            $table->decimal('carried_over', 8, 2)->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id', 'year']);
        });

        // Leave Requests
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('leave_type_id')->constrained()->onDelete('cascade');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('days_count', 8, 2);
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['employee_id', 'status']);
        });

        // Attendances
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->integer('late_minutes')->default(0);
            $table->integer('early_leave_minutes')->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->enum('status', ['present', 'absent', 'half-day', 'on-leave'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'date']);
            $table->index(['employee_id', 'date']);
        });

        // Payroll Periods
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('period'); // YYYY-MM
            $table->date('start_date');
            $table->date('end_date');
            $table->date('payment_date')->nullable();
            $table->enum('status', ['draft', 'calculated', 'reviewed', 'paid'])->default('draft');
            $table->decimal('total_gross', 20, 2)->default(0);
            $table->decimal('total_deductions', 20, 2)->default(0);
            $table->decimal('total_net', 20, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'period']);
        });

        // Payroll Items
        Schema::create('payroll_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_period_id')->constrained()->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->decimal('basic_salary', 15, 2)->default(0);
            $table->decimal('housing_allowance', 15, 2)->default(0);
            $table->decimal('transport_allowance', 15, 2)->default(0);
            $table->decimal('other_allowances', 15, 2)->default(0);
            $table->decimal('overtime_pay', 15, 2)->default(0);
            $table->decimal('gross_salary', 15, 2)->default(0);
            $table->decimal('tax_deduction', 15, 2)->default(0);
            $table->decimal('insurance_deduction', 15, 2)->default(0);
            $table->decimal('loan_deduction', 15, 2)->default(0);
            $table->decimal('advance_deduction', 15, 2)->default(0);
            $table->decimal('other_deductions', 15, 2)->default(0);
            $table->decimal('total_deductions', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2)->default(0);
            $table->boolean('is_paid')->default(false);
            $table->date('paid_at')->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamps();

            $table->index(['payroll_period_id', 'employee_id']);
        });

        // Employee Documents
        Schema::create('employee_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('document_type'); // contract, id, certificate, etc.
            $table->string('document_name');
            $table->string('file_path');
            $table->string('file_mime_type');
            $table->integer('file_size');
            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_documents');
        Schema::dropIfExists('payroll_items');
        Schema::dropIfExists('payroll_periods');
        Schema::dropIfExists('attendances');
        Schema::dropIfExists('leaves');
        Schema::dropIfExists('leave_entitlements');
        Schema::dropIfExists('leave_types');
        Schema::dropIfExists('employees');
        Schema::dropIfExists('departments');
    }
};

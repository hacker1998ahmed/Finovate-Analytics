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
        // 1. Tax Audits - الفحوصات الضريبية
        Schema::create('tax_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('audit_type'); // فحص ضريبي، فحص جدول، فحص عشوائي، فحص خاص
            $table->string('tax_year');
            $table->date('notification_date');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('auditor_name')->nullable();
            $table->string('tax_office')->nullable();
            $table->decimal('assessed_tax', 18, 2)->default(0);
            $table->decimal('penalties', 18, 2)->default(0);
            $table->decimal('additional_charges', 18, 2)->default(0);
            $table->string('status')->default('pending'); // pending, in_progress, completed, appealed, closed
            $table->text('findings')->nullable();
            $table->text('objections')->nullable();
            $table->date('objection_deadline')->nullable();
            $table->date('appeal_date')->nullable();
            $table->text('final_decision')->nullable();
            $table->timestamps();
        });

        // 2. Audit Documents - مستندات الفحص
        Schema::create('audit_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tax_audit_id')->constrained()->onDelete('cascade');
            $table->string('document_type'); // إخطار، مستندات مطلوبة، تقارير
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->date('submission_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Company Founding - تأسيس الشركات
        Schema::create('company_foundings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('company_type'); // LLC, JSC, Establishment, Branch
            $table->string('registration_number')->unique();
            $table->string('commercial_register_number')->nullable();
            $table->date('registration_date');
            $table->string('governorate')->nullable();
            $table->text('address')->nullable();
            $table->decimal('capital', 18, 2)->default(0);
            $table->decimal('paid_capital', 18, 2)->default(0);
            $table->text('activities')->nullable(); // النشاط
            $table->string('status')->default('active'); // active, under_process, suspended, dissolved
            $table->date('incorporation_date')->nullable();
            $table->text('articles_of_association')->nullable();
            $table->timestamps();
        });

        // 4. Partners/Shareholders - الشركاء/المساهمين
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_founding_id')->constrained()->onDelete('cascade');
            $table->string('partner_type'); // individual, corporate
            $table->string('name');
            $table->string('national_id')->nullable(); // رقم قومي
            $table->string('passport_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->decimal('shares_percentage', 5, 2)->default(0);
            $table->decimal('shares_value', 18, 2)->default(0);
            $table->string('position')->nullable(); // مدير، شريك، مساهم
            $table->boolean('is_manager')->default(false);
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        // 5. Internal Documents - المستندات الداخلية
        Schema::create('internal_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('document_category'); // عقود، محاضر، قرارات، مراسلات
            $table->string('document_type');
            $table->string('title');
            $table->string('reference_number')->nullable();
            $table->date('document_date');
            $table->date('expiry_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('department_id')->nullable()->constrained()->onDelete('set null');
            $table->string('file_path');
            $table->string('file_name');
            $table->integer('file_size')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->boolean('is_confidential')->default(false);
            $table->boolean('is_signed')->default(false);
            $table->timestamps();
        });

        // 6. Client Portals - بوابات العملاء
        Schema::create('client_portals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('portal_name'); // بوابة ضريبية، جمركية، تأمينات
            $table->string('portal_url');
            $table->string('username');
            $table->string('password_encrypted');
            $table->string('two_factor_secret')->nullable();
            $table->string('recovery_codes')->nullable();
            $table->date('password_last_changed')->nullable();
            $table->date('next_password_change')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->json('additional_credentials')->nullable();
            $table->timestamps();
        });

        // 7. Portal Access Logs - سجل الدخول للبوابات
        Schema::create('portal_access_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_portal_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamp('access_time');
            $table->string('action'); // login, logout, update, view
            $table->string('ip_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 8. Government Entities - الجهات الحكومية
        Schema::create('government_entities', function (Blueprint $table) {
            $table->id();
            $table->string('entity_name'); // مصلحة الضرائب، التأمينات، السجل التجاري
            $table->string('entity_type');
            $table->string('website_url')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 9. Compliance Tasks - مهام الامتثال
        Schema::create('compliance_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('task_type'); // إقرار ضريبي، تأمينات، فاتورة إلكترونية
            $table->string('task_name');
            $table->text('description')->nullable();
            $table->date('due_date');
            $table->date('reminder_dates')->nullable(); // JSON array
            $table->string('status')->default('pending'); // pending, in_progress, completed, overdue
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('completed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_notes')->nullable();
            $table->integer('priority')->default(3); // 1=high, 2=medium, 3=low
            $table->timestamps();
        });

        // 10. Email Templates - قوالب الإيميلات
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('set null');
            $table->string('template_name');
            $table->string('subject');
            $table->text('body');
            $table->string('category')->nullable(); // تذكير، تقرير، إشعار
            $table->boolean('is_active')->default(true);
            $table->json('variables')->nullable(); // متغيرات القالب
            $table->timestamps();
        });

        // 11. Email Logs - سجل الإيميلات
        Schema::create('email_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('recipient_email');
            $table->string('recipient_name')->nullable();
            $table->string('subject');
            $table->text('body')->nullable();
            $table->string('status')->default('pending'); // pending, sent, failed, opened, clicked
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('clicked_at')->nullable();
            $table->text('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->timestamps();
        });

        // 12. National IDs Registry - سجل الأرقام القومية
        Schema::create('national_ids_registry', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->onDelete('cascade');
            $table->string('national_id')->unique();
            $table->string('person_name');
            $table->string('person_type'); // موظف، شريك، عميل، مورد
            $table->date('birth_date')->nullable();
            $table->string('gender')->nullable();
            $table->string('governorate')->nullable();
            $table->string('center')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->date('verification_date')->nullable();
            $table->timestamps();
        });

        // Pivot table for document tagging
        Schema::create('document_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color')->default('#6c757d');
            $table->timestamps();
        });

        Schema::create('document_taggable', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_tag_id')->constrained()->onDelete('cascade');
            $table->morphs('taggable'); // يمكن ربطه بـ internal_documents أو audit_documents
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_taggable');
        Schema::dropIfExists('document_tags');
        Schema::dropIfExists('national_ids_registry');
        Schema::dropIfExists('email_logs');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('compliance_tasks');
        Schema::dropIfExists('government_entities');
        Schema::dropIfExists('portal_access_logs');
        Schema::dropIfExists('client_portals');
        Schema::dropIfExists('internal_documents');
        Schema::dropIfExists('partners');
        Schema::dropIfExists('company_foundings');
        Schema::dropIfExists('audit_documents');
        Schema::dropIfExists('tax_audits');
    }
};

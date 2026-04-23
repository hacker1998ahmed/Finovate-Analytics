<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use App\Models\GovernmentEntity;
use App\Models\Warehouse;
use App\Models\BankAccount;
use App\Models\CashRegister;
use App\Models\Department;
use App\Models\LeaveType;
use App\Models\PayrollPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create Admin User
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@finovate.com',
            'password' => Hash::make('password123'),
        ]);

        // Create Government Entities
        GovernmentEntity::create([
            'entity_name' => 'Egyptian Tax Authority',
            'entity_type' => 'TAX_AUTHORITY',
            'website_url' => 'https://api.invoicing.eta.gov.eg',
        ]);

        GovernmentEntity::create([
            'entity_name' => 'National Organization for Social Insurance',
            'entity_type' => 'SOCIAL_INSURANCE',
        ]);

        GovernmentEntity::create([
            'entity_name' => 'Egyptian Customs Authority',
            'entity_type' => 'CUSTOMS',
        ]);

        GovernmentEntity::create([
            'entity_name' => 'General Organization for Supply Commodities',
            'entity_type' => 'SUPPLY',
        ]);

        // Create Sample Companies
        Company::create([
            'name' => 'AHMED EG Trading Co.',
            'tax_registration_number' => '123-456-789',
            'eta_client_id' => 'client_123456',
            'eta_client_secret' => encrypt('secret_abcdef'),
            'digital_certificate' => null,
            'certificate_password' => encrypt('cert_pass_123'),
            'api_base_url' => 'https://api.invoicing.eta.gov.eg',
            'status' => 'active',
            'settings' => ['currency' => 'EGP', 'timezone' => 'Africa/Cairo'],
        ]);

        Company::create([
            'name' => 'Finovate Consulting',
            'tax_registration_number' => '987-654-321',
            'eta_client_id' => 'client_654321',
            'eta_client_secret' => encrypt('secret_xyzabc'),
            'digital_certificate' => null,
            'certificate_password' => encrypt('cert_pass_456'),
            'api_base_url' => 'https://api.invoicing.eta.gov.eg',
            'status' => 'active',
            'settings' => ['currency' => 'EGP', 'timezone' => 'Africa/Cairo'],
        ]);

        // Create Warehouses for each company
        foreach (Company::all() as $company) {
            Warehouse::create([
                'company_id' => $company->id,
                'name' => 'Main Warehouse',
                'code' => 'WH-' . str_pad($company->id, 3, '0', STR_PAD_LEFT) . '-001',
                'address' => 'Cairo, Egypt',
                'is_active' => true,
            ]);
        }

        // Create Bank Accounts
        BankAccount::create([
            'company_id' => 1,
            'bank_name' => 'National Bank of Egypt',
            'account_name' => 'AHMED EG Trading Co.',
            'account_number' => '1234567890123456',
            'iban' => 'EG380019000500000000123456789',
            'currency' => 'EGP',
            'balance' => 100000.00,
            'is_active' => true,
        ]);

        // Create Cash Registers
        CashRegister::create([
            'company_id' => 1,
            'register_name' => 'Main Cash Register',
            'currency' => 'EGP',
            'balance' => 5000.00,
            'is_active' => true,
        ]);

        // Create Departments
        Department::create([
            'company_id' => 1,
            'name' => 'Accounting',
            'code' => 'ACC',
        ]);

        Department::create([
            'company_id' => 1,
            'name' => 'Sales',
            'code' => 'SLS',
        ]);

        Department::create([
            'company_id' => 1,
            'name' => 'HR',
            'code' => 'HR',
        ]);

        // Create Leave Types
        LeaveType::create([
            'company_id' => 1,
            'name' => 'Annual Leave',
            'days_per_year' => 21,
            'is_paid' => true,
        ]);

        LeaveType::create([
            'company_id' => 1,
            'name' => 'Sick Leave',
            'days_per_year' => 14,
            'is_paid' => true,
        ]);

        // Create Payroll Period
        PayrollPeriod::create([
            'company_id' => 1,
            'name' => 'Monthly',
            'period_type' => 'MONTHLY',
            'is_active' => true,
        ]);

        $this->call([
            InitialDataSeeder::class,
        ]);
    }
}

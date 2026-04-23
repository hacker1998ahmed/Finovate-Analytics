<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;
use App\Models\ChartOfAccount;
use App\Models\GovernmentEntity;
use App\Models\Department;
use App\Models\Warehouse;
use App\Models\BankAccount;
use App\Models\CashRegister;
use Illuminate\Support\Facades\Hash;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Demo Company 1 - AHMED EG for Trading
        $company1 = Company::create([
            'name' => 'AHMED EG for Trading',
            'tax_registration_number' => '123-456-789',
            'address' => '123 Tahrir Street, Cairo, Egypt',
            'phone' => '+20 2 12345678',
            'email' => 'info@ahmedeg.com',
            'industry' => 'Trading',
            'eta_client_id' => 'demo_client_id_001',
            'eta_client_secret' => 'demo_secret_001',
            'certificate_path' => '/certificates/demo_001.pfx',
            'certificate_password' => 'demo123',
            'is_active' => true,
        ]);

        // Create default warehouse for company 1
        Warehouse::create([
            'company_id' => $company1->id,
            'name' => 'Main Warehouse',
            'code' => 'WH-001',
            'address' => $company1->address,
            'is_active' => true,
        ]);

        // Create default bank account
        BankAccount::create([
            'company_id' => $company1->id,
            'bank_name' => 'National Bank of Egypt',
            'account_number' => '1234567890123456',
            'iban' => 'EG380019000500000000123456789',
            'currency' => 'EGP',
            'is_default' => true,
            'is_active' => true,
        ]);

        // Create default cash register
        CashRegister::create([
            'company_id' => $company1->id,
            'name' => 'Main Cash Register',
            'code' => 'CR-001',
            'currency' => 'EGP',
            'is_default' => true,
            'is_active' => true,
        ]);

        // Create Demo Company 2 - El Nile Consultants
        $company2 = Company::create([
            'name' => 'El Nile Consultants Office',
            'tax_registration_number' => '987-654-321',
            'address' => '456 Corniche El Nil, Maadi, Cairo',
            'phone' => '+20 2 98765432',
            'email' => 'contact@nileconsultants.com',
            'industry' => 'Professional Services',
            'eta_client_id' => 'demo_client_id_002',
            'eta_client_secret' => 'demo_secret_002',
            'certificate_path' => '/certificates/demo_002.pfx',
            'certificate_password' => 'demo456',
            'is_active' => true,
        ]);

        // Create warehouse for company 2
        Warehouse::create([
            'company_id' => $company2->id,
            'name' => 'Office Supplies Store',
            'code' => 'WH-002',
            'address' => $company2->address,
            'is_active' => true,
        ]);

        // Create bank account for company 2
        BankAccount::create([
            'company_id' => $company2->id,
            'bank_name' => 'Banque Misr',
            'account_number' => '9876543210987654',
            'iban' => 'EG380002000100000000987654321',
            'currency' => 'EGP',
            'is_default' => true,
            'is_active' => true,
        ]);

        $this->command->info('✓ Companies seeded successfully!');
        $this->command->info("  - Created {$company1->name} (ID: {$company1->id})");
        $this->command->info("  - Created {$company2->name} (ID: {$company2->id})");
    }
}

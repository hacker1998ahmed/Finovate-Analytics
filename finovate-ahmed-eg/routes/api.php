<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\V1\TaxAuditController;
use App\Http\Controllers\Api\V1\CompanyFoundingController;
use App\Http\Controllers\Api\V1\InternalDocumentController;
use App\Http\Controllers\Api\V1\ClientPortalController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\PayrollController;
use App\Http\Controllers\Api\V1\WarehouseController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\FixedAssetController;
use App\Http\Controllers\Api\V1\JournalEntryController;
use App\Http\Controllers\Api\V1\BankController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes - Finovate AHMED EG ERP System
|--------------------------------------------------------------------------
|
| Complete API routes for Egyptian Tax Authority E-Invoice System
| plus full Accounting Law Office Management features.
|
*/

// Public routes (authentication middleware can be added later)
Route::prefix('v1')->group(function () {
    
    // ==================== DASHBOARD ====================
    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);
    
    // ==================== COMPANIES (Multi-Tenant) ====================
    Route::apiResource('companies', CompanyController::class);
    Route::post('/companies/{company}/test-connection', [CompanyController::class, 'testConnection']);
    Route::get('/companies/{company}/stats', [CompanyController::class, 'stats']);
    
    // ==================== ITEMS (per company) ====================
    Route::get('/companies/{company}/items', [ItemController::class, 'index']);
    Route::post('/companies/{company}/items', [ItemController::class, 'store']);
    Route::get('/companies/{company}/items/{item}', [ItemController::class, 'show']);
    Route::put('/companies/{company}/items/{item}', [ItemController::class, 'update']);
    Route::delete('/companies/{company}/items/{item}', [ItemController::class, 'destroy']);
    Route::post('/companies/{company}/items/bulk-import', [ItemController::class, 'bulkImport']);
    
    // ==================== INVOICES (ETA Integration) ====================
    Route::get('/companies/{company}/invoices', [InvoiceController::class, 'index']);
    Route::post('/companies/{company}/invoices', [InvoiceController::class, 'store']);
    Route::get('/companies/{company}/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::post('/companies/{company}/invoices/{invoice}/submit', [InvoiceController::class, 'submit']);
    Route::post('/companies/{company}/invoices/{invoice}/sync', [InvoiceController::class, 'sync']);
    Route::post('/companies/{company}/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel']);
    Route::get('/companies/{company}/invoices/stats', [InvoiceController::class, 'stats']);
    
    // ==================== TAX AUDITS ====================
    Route::apiResource('companies/{company}/tax-audits', TaxAuditController::class);
    Route::post('/companies/{company}/tax-audits/{audit}/objection', [TaxAuditController::class, 'submitObjection']);
    Route::get('/companies/{company}/tax-audits/{audit}/documents', [TaxAuditController::class, 'documents']);
    
    // ==================== COMPANY FOUNDINGS ====================
    Route::apiResource('companies/{company}/foundings', CompanyFoundingController::class);
    Route::post('/companies/{company}/foundings/{founding}/partners', [CompanyFoundingController::class, 'addPartner']);
    Route::get('/companies/{company}/foundings/{founding}/status', [CompanyFoundingController::class, 'status']);
    
    // ==================== INTERNAL DOCUMENTS ====================
    Route::apiResource('companies/{company}/documents', InternalDocumentController::class);
    Route::post('/companies/{company}/documents/{document}/tags', [InternalDocumentController::class, 'addTags']);
    Route::get('/companies/{company}/documents/search', [InternalDocumentController::class, 'search']);
    
    // ==================== CLIENT PORTALS ====================
    Route::apiResource('companies/{company}/portals', ClientPortalController::class);
    Route::post('/companies/{company}/portals/{portal}/access', [ClientPortalController::class, 'logAccess']);
    Route::get('/companies/{company}/portals/{portal}/credentials', [ClientPortalController::class, 'getCredentials']);
    
    // ==================== CUSTOMERS (CRM) ====================
    Route::apiResource('companies/{company}/customers', CustomerController::class);
    Route::get('/companies/{company}/customers/{customer}/invoices', [CustomerController::class, 'invoices']);
    Route::get('/companies/{company}/customers/{customer}/statements', [CustomerController::class, 'statements']);
    
    // ==================== SUPPLIERS ====================
    Route::apiResource('companies/{company}/suppliers', SupplierController::class);
    Route::get('/companies/{company}/suppliers/{supplier}/orders', [SupplierController::class, 'orders']);
    Route::get('/companies/{company}/suppliers/{supplier}/statements', [SupplierController::class, 'statements']);
    
    // ==================== EMPLOYEES (HRMS) ====================
    Route::apiResource('companies/{company}/employees', EmployeeController::class);
    Route::post('/companies/{company}/employees/{employee}/attendances', [EmployeeController::class, 'logAttendance']);
    Route::post('/companies/{company}/employees/{employee}/leaves', [EmployeeController::class, 'requestLeave']);
    Route::get('/companies/{company}/employees/{employee}/payroll', [EmployeeController::class, 'payrollHistory']);
    
    // ==================== PAYROLL ====================
    Route::apiResource('companies/{company}/payroll', PayrollController::class);
    Route::post('/companies/{company}/payroll/generate', [PayrollController::class, 'generatePayroll']);
    Route::get('/companies/{company}/payroll/{period}/payslips', [PayrollController::class, 'generatePayslips']);
    
    // ==================== WAREHOUSES & INVENTORY ====================
    Route::apiResource('companies/{company}/warehouses', WarehouseController::class);
    Route::get('/companies/{company}/inventory', [WarehouseController::class, 'inventory']);
    Route::post('/companies/{company}/inventory/movements', [WarehouseController::class, 'stockMovement']);
    
    // ==================== PROJECTS ====================
    Route::apiResource('companies/{company}/projects', ProjectController::class);
    Route::post('/companies/{company}/projects/{project}/tasks', [ProjectController::class, 'addTask']);
    Route::post('/companies/{company}/projects/{project}/timesheets', [ProjectController::class, 'logTimesheet']);
    Route::get('/companies/{company}/projects/{project}/expenses', [ProjectController::class, 'expenses']);
    
    // ==================== FIXED ASSETS ====================
    Route::apiResource('companies/{company}/assets', FixedAssetController::class);
    Route::post('/companies/{company}/assets/{asset}/depreciate', [FixedAssetController::class, 'calculateDepreciation']);
    Route::get('/companies/{company}/assets/reports/depreciation', [FixedAssetController::class, 'depreciationReport']);
    
    // ==================== ACCOUNTING (General Ledger) ====================
    Route::apiResource('companies/{company}/journal-entries', JournalEntryController::class);
    Route::get('/companies/{company}/accounts', [JournalEntryController::class, 'chartOfAccounts']);
    Route::get('/companies/{company}/trial-balance', [JournalEntryController::class, 'trialBalance']);
    Route::get('/companies/{company}/balance-sheet', [JournalEntryController::class, 'balanceSheet']);
    Route::get('/companies/{company}/income-statement', [JournalEntryController::class, 'incomeStatement']);
    
    // ==================== BANKS & CASH ====================
    Route::apiResource('companies/{company}/bank-accounts', BankController::class);
    Route::post('/companies/{company}/bank-accounts/{account}/transactions', [BankController::class, 'addTransaction']);
    Route::get('/companies/{company}/bank-accounts/{account}/reconciliation', [BankController::class, 'reconciliation']);
    Route::get('/companies/{company}/cash-registers', [BankController::class, 'cashRegisters']);
    
    // ==================== REPORTS ====================
    Route::get('/companies/{company}/reports/{type}', [ReportController::class, 'generate']);
    Route::post('/companies/{company}/reports/custom', [ReportController::class, 'customReport']);
    Route::get('/companies/{company}/reports/templates', [ReportController::class, 'templates']);
});

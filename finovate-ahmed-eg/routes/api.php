<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\ItemController;

/*
|--------------------------------------------------------------------------
| API Routes - Finovate AHMED EG E-Invoice System
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// Public routes (no authentication required for demo)
Route::prefix('v1')->group(function () {
    
    // Companies (Multi-Tenant Management)
    Route::get('/companies', [CompanyController::class, 'index']);
    Route::post('/companies', [CompanyController::class, 'store']);
    Route::get('/companies/{company}', [CompanyController::class, 'show']);
    Route::put('/companies/{company}', [CompanyController::class, 'update']);
    Route::post('/companies/{company}/test-connection', [CompanyController::class, 'testConnection']);
    Route::get('/companies/{company}/stats', [CompanyController::class, 'stats']);
    
    // Items (per company)
    Route::get('/companies/{company}/items', [ItemController::class, 'index']);
    Route::post('/companies/{company}/items', [ItemController::class, 'store']);
    Route::get('/companies/{company}/items/{item}', [ItemController::class, 'show']);
    Route::put('/companies/{company}/items/{item}', [ItemController::class, 'update']);
    Route::delete('/companies/{company}/items/{item}', [ItemController::class, 'destroy']);
    Route::post('/companies/{company}/items/bulk-import', [ItemController::class, 'bulkImport']);
    
    // Invoices (per company)
    Route::get('/companies/{company}/invoices', [InvoiceController::class, 'index']);
    Route::post('/companies/{company}/invoices', [InvoiceController::class, 'store']);
    Route::get('/companies/{company}/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::post('/companies/{company}/invoices/{invoice}/submit', [InvoiceController::class, 'submit']);
    Route::post('/companies/{company}/invoices/{invoice}/sync', [InvoiceController::class, 'sync']);
    Route::post('/companies/{company}/invoices/{invoice}/cancel', [InvoiceController::class, 'cancel']);
    Route::get('/companies/{company}/invoices/stats', [InvoiceController::class, 'stats']);
});

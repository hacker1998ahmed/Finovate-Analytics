<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\TaxAudit;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\BankAccount;
use App\Models\Warehouse;
use App\Models\Project;
use App\Models\FixedAsset;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    /**
     * Display dashboard statistics for a company
     */
    public function index(Request $request, Company $company): JsonResponse
    {
        $stats = [
            'invoices' => [
                'total' => Invoice::where('company_id', $company->id)->count(),
                'submitted' => Invoice::where('company_id', $company->id)->where('status', 'SUBMITTED')->count(),
                'valid' => Invoice::where('company_id', $company->id)->where('status', 'VALID')->count(),
                'rejected' => Invoice::where('company_id', $company->id)->where('status', 'REJECTED')->count(),
                'total_amount' => Invoice::where('company_id', $company->id)
                    ->where('status', 'VALID')
                    ->sum('total_amount'),
            ],
            'tax_audits' => [
                'active' => TaxAudit::where('company_id', $company->id)
                    ->whereIn('status', ['OPEN', 'IN_PROGRESS'])->count(),
                'closed' => TaxAudit::where('company_id', $company->id)
                    ->where('status', 'CLOSED')->count(),
            ],
            'customers' => Customer::where('company_id', $company->id)->count(),
            'suppliers' => Supplier::where('company_id', $company->id)->count(),
            'employees' => Employee::where('company_id', $company->id)->count(),
            'pending_journal_entries' => JournalEntry::where('company_id', $company->id)
                ->where('posted', false)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
            'company' => [
                'id' => $company->id,
                'name' => $company->name,
                'tax_number' => $company->tax_number,
            ]
        ]);
    }

    /**
     * Get detailed statistics
     */
    public function stats(Request $request, Company $company): JsonResponse
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        $monthlyInvoices = Invoice::where('company_id', $company->id)
            ->whereYear('created_at', $currentYear)
            ->whereMonth('created_at', $currentMonth)
            ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as total')
            ->groupBy('status')
            ->get();

        $recentInvoices = Invoice::where('company_id', $company->id)
            ->with(['customer', 'items'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $topCustomers = Customer::where('company_id', $company->id)
            ->withCount(['invoices' => function ($query) {
                $query->where('status', 'VALID');
            }])
            ->orderBy('invoices_count', 'desc')
            ->limit(5)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'monthly_invoices' => $monthlyInvoices,
                'recent_invoices' => $recentInvoices,
                'top_customers' => $topCustomers,
                'period' => [
                    'month' => $currentMonth,
                    'year' => $currentYear,
                ]
            ]
        ]);
    }
}

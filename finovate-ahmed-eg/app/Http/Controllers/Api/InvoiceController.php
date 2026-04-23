<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\AuditLog;
use App\Services\EtaAuthService;
use App\Services\EtaInvoiceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    /**
     * Display a listing of invoices for the company.
     */
    public function index(Request $request, Company $company): JsonResponse
    {
        $query = Invoice::where('company_id', $company->id)
            ->with(['items']);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('invoice_type')) {
            $query->where('invoice_type', $request->invoice_type);
        }

        if ($request->has('buyer_name')) {
            $query->buyer($request->buyer_name);
        }

        if ($request->has('invoice_number')) {
            $query->invoiceNumber($request->invoice_number);
        }

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        $invoices = $query->orderBy('created_at', 'desc')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $invoices,
        ]);
    }

    /**
     * Store a newly created invoice.
     */
    public function store(Request $request, Company $company): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required|string|max:255',
            'invoice_type' => 'required|in:B2B,B2C',
            'issue_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'buyer_name' => 'required|string|max:255',
            'buyer_tax_id' => 'nullable|string|max:255',
            'buyer_vat_number' => 'nullable|string|max:255',
            'buyer_address' => 'nullable|string',
            'buyer_email' => 'nullable|email|max:255',
            'buyer_phone' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.item_code' => 'required|string|max:255',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.001',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
            'items.*.tax_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $company) {
                // Create invoice
                $invoice = Invoice::create([
                    'company_id' => $company->id,
                    'invoice_number' => $request->invoice_number,
                    'invoice_type' => $request->invoice_type,
                    'issue_date' => $request->issue_date,
                    'delivery_date' => $request->delivery_date,
                    'seller_name' => $company->name,
                    'seller_tax_id' => $company->tax_registration_number,
                    'seller_address' => $company->settings['address'] ?? null,
                    'buyer_name' => $request->buyer_name,
                    'buyer_tax_id' => $request->buyer_tax_id,
                    'buyer_vat_number' => $request->buyer_vat_number,
                    'buyer_address' => $request->buyer_address,
                    'buyer_email' => $request->buyer_email,
                    'buyer_phone' => $request->buyer_phone,
                    'status' => Invoice::STATUS_DRAFT,
                ]);

                // Add items
                foreach ($request->items as $itemData) {
                    $invoiceItem = $invoice->items()->create([
                        'item_code' => $itemData['item_code'],
                        'item_name' => $itemData['item_name'],
                        'item_description' => $itemData['description'] ?? null,
                        'quantity' => $itemData['quantity'],
                        'unit_type' => $itemData['unit_type'] ?? 'EA',
                        'unit_price' => $itemData['unit_price'],
                        'discount_percent' => $itemData['discount_percent'] ?? 0,
                        'discount_amount' => $itemData['discount_amount'] ?? 0,
                        'tax_type' => $itemData['tax_type'] ?? 'VAT',
                        'tax_rate' => $itemData['tax_rate'] ?? 14.00,
                        'subtotal' => 0, // Will be calculated
                        'total' => 0, // Will be calculated
                    ]);

                    $invoiceItem->calculateTotals();
                    $invoiceItem->save();
                }

                // Calculate invoice totals
                $invoice->calculateTotals();
                $invoice->save();

                // Log creation
                AuditLog::logCreate($invoice, [], $company->id);

                return $invoice;
            });

            return response()->json([
                'success' => true,
                'message' => 'Invoice created successfully',
                'data' => Invoice::where('company_id', $company->id)
                    ->latest()
                    ->first(),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating invoice',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified invoice.
     */
    public function show(Company $company, Invoice $invoice): JsonResponse
    {
        if ($invoice->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        $invoice->load(['items']);

        return response()->json([
            'success' => true,
            'data' => $invoice,
        ]);
    }

    /**
     * Submit invoice to ETA.
     */
    public function submit(Company $company, Invoice $invoice): JsonResponse
    {
        if ($invoice->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        if ($invoice->status !== Invoice::STATUS_DRAFT) {
            return response()->json([
                'success' => false,
                'message' => 'Only draft invoices can be submitted',
            ], 400);
        }

        $etaService = new EtaInvoiceService($company);
        $result = $etaService->submitInvoice($invoice);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Sync invoice status from ETA.
     */
    public function sync(Company $company, Invoice $invoice): JsonResponse
    {
        if ($invoice->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        $etaService = new EtaInvoiceService($company);
        $success = $etaService->syncInvoiceStatus($invoice);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Invoice status synced successfully',
                'data' => $invoice->fresh(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Failed to sync invoice status',
        ], 500);
    }

    /**
     * Cancel invoice.
     */
    public function cancel(Request $request, Company $company, Invoice $invoice): JsonResponse
    {
        if ($invoice->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $etaService = new EtaInvoiceService($company);
        $result = $etaService->cancelInvoice($invoice, $request->reason);

        if ($result['success']) {
            return response()->json($result);
        }

        return response()->json($result, 400);
    }

    /**
     * Get dashboard statistics.
     */
    public function stats(Company $company): JsonResponse
    {
        $stats = [
            'total_invoices' => Invoice::where('company_id', $company->id)->count(),
            'draft_invoices' => Invoice::where('company_id', $company->id)
                ->where('status', Invoice::STATUS_DRAFT)->count(),
            'submitted_invoices' => Invoice::where('company_id', $company->id)
                ->where('status', Invoice::STATUS_SUBMITTED)->count(),
            'valid_invoices' => Invoice::where('company_id', $company->id)
                ->where('status', Invoice::STATUS_VALID)->count(),
            'rejected_invoices' => Invoice::where('company_id', $company->id)
                ->where('status', Invoice::STATUS_REJECTED)->count(),
            'total_sales' => Invoice::where('company_id', $company->id)
                ->where('status', Invoice::STATUS_VALID)
                ->sum('total_amount'),
            'this_month_sales' => Invoice::where('company_id', $company->id)
                ->where('status', Invoice::STATUS_VALID)
                ->whereMonth('issue_date', now()->month)
                ->whereYear('issue_date', now()->year)
                ->sum('total_amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

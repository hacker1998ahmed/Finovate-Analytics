<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Item;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ItemController extends Controller
{
    /**
     * Display a listing of items for the company.
     */
    public function index(Request $request, Company $company): JsonResponse
    {
        $query = Item::where('company_id', $company->id);

        // Apply filters
        if ($request->has('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->has('tax_type')) {
            $query->where('tax_type', $request->tax_type);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $items = $query->orderBy('name')
            ->paginate($request->get('per_page', 50));

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Store a newly created item.
     */
    public function store(Request $request, Company $company): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'unit_price' => 'required|numeric|min:0',
            'tax_type' => 'required|in:VAT,TABLE_TAX',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'unit_type' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        // Check for duplicate code
        $exists = Item::where('company_id', $company->id)
            ->where('code', $request->code)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Item code already exists for this company',
            ], 422);
        }

        try {
            $item = Item::create([
                'company_id' => $company->id,
                'code' => $request->code,
                'name' => $request->name,
                'description' => $request->description,
                'unit_price' => $request->unit_price,
                'tax_type' => $request->tax_type,
                'tax_rate' => $request->tax_rate,
                'unit_type' => $request->unit_type ?? 'EA',
                'is_active' => $request->boolean('is_active', true),
            ]);

            AuditLog::logCreate($item, [], $company->id);

            return response()->json([
                'success' => true,
                'message' => 'Item created successfully',
                'data' => $item,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified item.
     */
    public function show(Company $company, Item $item): JsonResponse
    {
        if ($item->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $item,
        ]);
    }

    /**
     * Update the specified item.
     */
    public function update(Request $request, Company $company, Item $item): JsonResponse
    {
        if ($item->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'code' => 'sometimes|required|string|max:255|unique:items,code,' . $item->id . ',company_id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'unit_price' => 'sometimes|required|numeric|min:0',
            'tax_type' => 'sometimes|required|in:VAT,TABLE_TAX',
            'tax_rate' => 'sometimes|required|numeric|min:0|max:100',
            'unit_type' => 'nullable|string|max:10',
            'is_active' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $oldValues = $item->toArray();
            
            $item->update($request->only([
                'code',
                'name',
                'description',
                'unit_price',
                'tax_type',
                'tax_rate',
                'unit_type',
                'is_active',
            ]));

            AuditLog::logUpdate($item, $oldValues, $item->fresh()->toArray(), $company->id);

            return response()->json([
                'success' => true,
                'message' => 'Item updated successfully',
                'data' => $item->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified item.
     */
    public function destroy(Company $company, Item $item): JsonResponse
    {
        if ($item->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Item not found',
            ], 404);
        }

        // Check if item is used in any invoice
        if ($item->invoiceItems()->count() > 0) {
            // Soft delete by marking as inactive instead
            $item->update(['is_active' => false]);
            
            return response()->json([
                'success' => true,
                'message' => 'Item deactivated (cannot delete items with invoice history)',
                'data' => $item,
            ]);
        }

        try {
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Item deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting item',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk import items.
     */
    public function bulkImport(Request $request, Company $company): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.code' => 'required|string|max:255',
            'items.*.name' => 'required|string|max:255',
            'items.*.description' => 'nullable|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.tax_type' => 'required|in:VAT,TABLE_TAX',
            'items.*.tax_rate' => 'required|numeric|min:0|max:100',
            'items.*.unit_type' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $importedCount = 0;
            $failedCount = 0;
            $errors = [];

            foreach ($request->items as $index => $itemData) {
                try {
                    Item::create([
                        'company_id' => $company->id,
                        'code' => $itemData['code'],
                        'name' => $itemData['name'],
                        'description' => $itemData['description'] ?? null,
                        'unit_price' => $itemData['unit_price'],
                        'tax_type' => $itemData['tax_type'],
                        'tax_rate' => $itemData['tax_rate'],
                        'unit_type' => $itemData['unit_type'] ?? 'EA',
                        'is_active' => true,
                    ]);
                    $importedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    $errors[] = [
                        'index' => $index,
                        'code' => $itemData['code'] ?? 'N/A',
                        'error' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Imported {$importedCount} items, {$failedCount} failed",
                'data' => [
                    'imported' => $importedCount,
                    'failed' => $failedCount,
                    'errors' => $errors,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error importing items',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

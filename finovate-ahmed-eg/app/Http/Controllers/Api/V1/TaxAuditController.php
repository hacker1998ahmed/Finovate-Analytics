<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\TaxAudit;
use App\Models\AuditDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TaxAuditController extends Controller
{
    /**
     * Display a listing of tax audits for a company
     */
    public function index(Company $company): JsonResponse
    {
        $audits = TaxAudit::where('company_id', $company->id)
            ->with(['governmentEntity', 'documents'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $audits
        ]);
    }

    /**
     * Store a newly created tax audit
     */
    public function store(Request $request, Company $company): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'audit_type' => 'required|in:TAX_AUDIT,TABLE_AUDIT,RANDOM_AUDIT,SPECIAL_AUDIT',
            'government_entity_id' => 'required|exists:government_entities,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'status' => 'required|in:OPEN,IN_PROGRESS,CLOSED,CANCELLED',
            'assigned_auditor' => 'required|string|max:255',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $audit = TaxAudit::create([
            'company_id' => $company->id,
            'audit_number' => 'AUD-' . now()->format('Y') . '-' . str_pad(TaxAudit::count() + 1, 6, '0', STR_PAD_LEFT),
            'audit_type' => $request->audit_type,
            'government_entity_id' => $request->government_entity_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => $request->status ?? 'OPEN',
            'assigned_auditor' => $request->assigned_auditor,
            'notes' => $request->notes,
            'total_assessed_tax' => 0,
            'total_penalties' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tax audit created successfully',
            'data' => $audit->load('governmentEntity')
        ], 201);
    }

    /**
     * Display the specified tax audit
     */
    public function show(Company $company, TaxAudit $audit): JsonResponse
    {
        if ($audit->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Audit not found'
            ], 404);
        }

        $audit->load(['governmentEntity', 'documents', 'objections']);

        return response()->json([
            'success' => true,
            'data' => $audit
        ]);
    }

    /**
     * Update the specified tax audit
     */
    public function update(Request $request, Company $company, TaxAudit $audit): JsonResponse
    {
        if ($audit->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Audit not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'audit_type' => 'sometimes|in:TAX_AUDIT,TABLE_AUDIT,RANDOM_AUDIT,SPECIAL_AUDIT',
            'government_entity_id' => 'sometimes|exists:government_entities,id',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'status' => 'sometimes|in:OPEN,IN_PROGRESS,CLOSED,CANCELLED',
            'assigned_auditor' => 'sometimes|string|max:255',
            'notes' => 'nullable|string',
            'total_assessed_tax' => 'sometimes|numeric|min:0',
            'total_penalties' => 'sometimes|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $audit->update($request->only([
            'audit_type', 'government_entity_id', 'start_date', 'end_date',
            'status', 'assigned_auditor', 'notes', 'total_assessed_tax', 'total_penalties'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Tax audit updated successfully',
            'data' => $audit->fresh(['governmentEntity'])
        ]);
    }

    /**
     * Remove the specified tax audit
     */
    public function destroy(Company $company, TaxAudit $audit): JsonResponse
    {
        if ($audit->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Audit not found'
            ], 404);
        }

        if ($audit->status !== 'CANCELLED' && $audit->status !== 'CLOSED') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete an active audit. Please cancel or close it first.'
            ], 400);
        }

        $audit->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tax audit deleted successfully'
        ]);
    }

    /**
     * Submit objection for a tax audit
     */
    public function submitObjection(Request $request, Company $company, TaxAudit $audit): JsonResponse
    {
        if ($audit->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Audit not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'objection_text' => 'required|string',
            'supporting_documents' => 'nullable|array',
            'submission_date' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $objection = $audit->objections()->create([
            'objection_text' => $request->objection_text,
            'submission_date' => $request->submission_date,
            'status' => 'PENDING',
        ]);

        // Handle supporting documents if provided
        if ($request->has('supporting_documents')) {
            foreach ($request->supporting_documents as $doc) {
                AuditDocument::create([
                    'tax_audit_id' => $audit->id,
                    'document_type' => 'OBJECTION_SUPPORT',
                    'file_name' => $doc['file_name'] ?? 'document.pdf',
                    'file_path' => $doc['file_path'] ?? '/documents/objection_' . $objection->id . '.pdf',
                    'uploaded_by' => auth()->id() ?? 1,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Objection submitted successfully',
            'data' => $objection
        ], 201);
    }

    /**
     * Get all documents for a tax audit
     */
    public function documents(Company $company, TaxAudit $audit): JsonResponse
    {
        if ($audit->company_id !== $company->id) {
            return response()->json([
                'success' => false,
                'message' => 'Audit not found'
            ], 404);
        }

        $documents = AuditDocument::where('tax_audit_id', $audit->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $documents
        ]);
    }
}

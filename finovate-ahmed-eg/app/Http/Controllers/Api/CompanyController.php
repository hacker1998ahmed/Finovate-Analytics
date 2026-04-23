<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\EtaAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies.
     */
    public function index(): JsonResponse
    {
        $companies = Company::orderBy('name')->get();

        return response()->json([
            'success' => true,
            'data' => $companies,
        ]);
    }

    /**
     * Store a newly created company.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'tax_registration_number' => 'required|string|max:255|unique:companies',
            'eta_client_id' => 'required|string|max:255',
            'eta_client_secret' => 'required|string',
            'digital_certificate' => 'nullable|string',
            'certificate_password' => 'nullable|string',
            'api_base_url' => 'nullable|url|max:255',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $company = Company::create([
                'name' => $request->name,
                'tax_registration_number' => $request->tax_registration_number,
                'eta_client_id' => $request->eta_client_id,
                'eta_client_secret' => encrypt($request->eta_client_secret),
                'digital_certificate' => $request->digital_certificate,
                'certificate_password' => $request->certificate_password 
                    ? encrypt($request->certificate_password) 
                    : null,
                'api_base_url' => $request->api_base_url ?? 'https://api.invoicing.eta.gov.eg',
                'status' => 'active',
                'settings' => $request->settings ?? [],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Company registered successfully',
                'data' => $company,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating company',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified company.
     */
    public function show(Company $company): JsonResponse
    {
        // Don't expose sensitive credentials
        $companyData = $company->toArray();
        unset($companyData['eta_client_secret']);
        unset($companyData['certificate_password']);

        return response()->json([
            'success' => true,
            'data' => $companyData,
        ]);
    }

    /**
     * Update the specified company.
     */
    public function update(Request $request, Company $company): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'tax_registration_number' => 'sometimes|required|string|max:255|unique:companies,tax_registration_number,' . $company->id,
            'eta_client_id' => 'sometimes|required|string|max:255',
            'eta_client_secret' => 'sometimes|required|string',
            'digital_certificate' => 'nullable|string',
            'certificate_password' => 'nullable|string',
            'api_base_url' => 'nullable|url|max:255',
            'status' => 'sometimes|required|in:active,inactive',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $updateData = [];

            if ($request->has('name')) {
                $updateData['name'] = $request->name;
            }

            if ($request->has('tax_registration_number')) {
                $updateData['tax_registration_number'] = $request->tax_registration_number;
            }

            if ($request->has('eta_client_id')) {
                $updateData['eta_client_id'] = $request->eta_client_id;
            }

            if ($request->has('eta_client_secret')) {
                $updateData['eta_client_secret'] = encrypt($request->eta_client_secret);
            }

            if ($request->has('digital_certificate')) {
                $updateData['digital_certificate'] = $request->digital_certificate;
            }

            if ($request->has('certificate_password')) {
                $updateData['certificate_password'] = encrypt($request->certificate_password);
            }

            if ($request->has('api_base_url')) {
                $updateData['api_base_url'] = $request->api_base_url;
            }

            if ($request->has('status')) {
                $updateData['status'] = $request->status;
            }

            if ($request->has('settings')) {
                $updateData['settings'] = $request->settings;
            }

            $company->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Company updated successfully',
                'data' => $company->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating company',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test ETA connection for the company.
     */
    public function testConnection(Company $company): JsonResponse
    {
        try {
            $authService = new EtaAuthService($company);
            $token = $authService->getAccessToken();

            if ($token) {
                return response()->json([
                    'success' => true,
                    'message' => 'Connection to ETA successful',
                    'data' => $authService->getTokenInfo(),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to ETA',
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get company statistics.
     */
    public function stats(Company $company): JsonResponse
    {
        $stats = [
            'total_invoices' => $company->invoices()->count(),
            'valid_invoices' => $company->invoices()->where('status', 'VALID')->count(),
            'rejected_invoices' => $company->invoices()->where('status', 'REJECTED')->count(),
            'total_items' => $company->items()->count(),
            'active_items' => $company->items()->where('is_active', true)->count(),
            'total_sales' => $company->invoices()
                ->where('status', 'VALID')
                ->sum('total_amount'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }
}

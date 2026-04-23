<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;

class EtaInvoiceService
{
    private Company $company;
    private EtaAuthService $authService;
    private string $baseUrl;

    public function __construct(Company $company)
    {
        $this->company = $company;
        $this->authService = new EtaAuthService($company);
        $this->baseUrl = $company->api_base_url ?? 'https://api.invoicing.eta.gov.eg';
    }

    /**
     * Submit invoice to ETA.
     */
    public function submitInvoice(Invoice $invoice): array
    {
        try {
            // Prepare document for ETA
            $document = $this->prepareDocumentForEta($invoice);
            
            // Sign the document
            $signedDocument = $this->signDocument($document);
            
            // Submit to ETA
            $response = $this->authService->request('POST', 'api/documents', $signedDocument);
            
            if ($response->successful()) {
                $responseData = $response->json();
                
                // Update invoice with ETA response
                DB::transaction(function () use ($invoice, $responseData) {
                    $invoice->update([
                        'status' => Invoice::STATUS_SUBMITTED,
                        'eta_submission_id' => $responseData['submissionId'] ?? null,
                        'eta_response' => $responseData,
                        'is_synced' => true,
                        'synced_at' => now(),
                    ]);
                    
                    // Log the submission
                    AuditLog::log(
                        AuditLog::ACTION_SUBMITTED,
                        $invoice,
                        ['submission_id' => $responseData['submissionId'] ?? null],
                        $this->company->id
                    );
                });
                
                return [
                    'success' => true,
                    'message' => 'Invoice submitted successfully',
                    'submission_id' => $responseData['submissionId'] ?? null,
                    'uuid' => $responseData['uuid'] ?? $invoice->uuid,
                ];
            }
            
            // Handle error response
            $errorData = $response->json();
            $this->handleSubmissionError($invoice, $errorData);
            
            return [
                'success' => false,
                'message' => 'Failed to submit invoice to ETA',
                'errors' => $errorData,
            ];
            
        } catch (Exception $e) {
            Log::error('ETA Invoice Submission Error', [
                'company_id' => $this->company->id,
                'invoice_id' => $invoice->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return [
                'success' => false,
                'message' => 'Error submitting invoice: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare invoice document for ETA API format.
     */
    private function prepareDocumentForEta(Invoice $invoice): array
    {
        $items = $invoice->items->map(function (InvoiceItem $item) {
            return $item->toEtaFormat();
        })->toArray();
        
        return [
            'invoice' => [
                'issuer' => [
                    'name' => $invoice->seller_name,
                    'id' => $invoice->seller_tax_id,
                    'address' => $invoice->seller_address ?? '',
                ],
                'receiver' => [
                    'name' => $invoice->buyer_name,
                    'id' => $invoice->buyer_tax_id ?? '',
                    'vatNumber' => $invoice->buyer_vat_number ?? '',
                    'address' => $invoice->buyer_address ?? '',
                    'email' => $invoice->buyer_email ?? '',
                    'phone' => $invoice->buyer_phone ?? '',
                ],
                'documentType' => $invoice->invoice_type === Invoice::TYPE_B2B ? 'I' : 'R', // I=Invoice, R=Receipt
                'documentTypeVersion' => '0.9',
                'dateTimeIssued' => $invoice->issue_date->format('Y-m-d\TH:i:s'),
                'dateTimeReceived' => $invoice->delivery_date?->format('Y-m-d\TH:i:s') ?? $invoice->issue_date->format('Y-m-d\TH:i:s'),
                'purchaseOrderReference' => null,
                'purchaseOrderDescription' => null,
                'salesOrderReference' => null,
                'salesOrderDescription' => null,
                'deliveryNoteReference' => null,
                'deliveryNoteDescription' => null,
                'contractReference' => null,
                'contractDescription' => null,
                'billingReference' => null,
                'shipmentReference' => null,
                'additionalInformation' => null,
                'paymentMeans' => [
                    'type' => 'Cash', // Can be extended
                ],
                'prepayment' => [
                    'totalPrepaymentAmount' => 0,
                    'totalPrepaymentVAT' => 0,
                ],
                'allowanceChargeDocuments' => [],
                'billOfLading' => null,
                'fiscalPrinterSerialNumber' => null,
                'reason' => null,
                'currency' => 'EGP',
                'taxCalculationMethod' => 'Standard',
                'invoicesList' => [],
                'documentsReferences' => [],
                'invoiceLines' => $items,
                'totalDiscountAmount' => (float) $invoice->discount_total,
                'totalSalesAmount' => (float) $invoice->subtotal,
                'netAmount' => (float) $invoice->net_total,
                'taxTotals' => [
                    [
                        'taxableAmount' => (float) $invoice->net_total,
                        'taxType' => 'V', // VAT
                        'rate' => 14.00, // Default VAT rate
                        'taxAmount' => (float) $invoice->tax_total,
                    ]
                ],
                'totalAmount' => (float) $invoice->total_amount,
                'totalVATAmount' => (float) $invoice->tax_total,
                'countryCode' => 'EG',
                'uuid' => $invoice->uuid,
            ],
        ];
    }

    /**
     * Sign the document (placeholder for actual signing implementation).
     */
    private function signDocument(array $document): array
    {
        // In production, this should use the company's digital certificate
        // to create a cryptographic signature of the document
        
        $documentHash = hash('sha256', json_encode($document));
        
        // TODO: Implement actual digital signature using OpenSSL and company certificate
        // $signature = $this->createDigitalSignature($documentHash);
        
        $document['invoice']['signature'] = [
            'type' => 'Digital',
            'algorithm' => 'SHA256WithRSA',
            'value' => base64_encode($documentHash), // Placeholder
        ];
        
        $document['invoice']['hash'] = $documentHash;
        
        return $document;
    }

    /**
     * Handle submission errors.
     */
    private function handleSubmissionError(Invoice $invoice, array $errorData): void
    {
        $status = Invoice::STATUS_INVALID;
        $rejectionReasons = [];
        
        // Parse ETA error response
        if (isset($errorData['errors']) && is_array($errorData['errors'])) {
            foreach ($errorData['errors'] as $error) {
                $rejectionReasons[] = $error['message'] ?? json_encode($error);
            }
        } elseif (isset($errorData['message'])) {
            $rejectionReasons[] = $errorData['message'];
        }
        
        // Determine status based on response
        if (isset($errorData['code']) && in_array($errorData['code'], ['REJECTED', 'INVALID'])) {
            $status = Invoice::STATUS_REJECTED;
        }
        
        $invoice->update([
            'status' => $status,
            'rejection_reason' => json_encode($rejectionReasons),
            'eta_response' => $errorData,
        ]);
    }

    /**
     * Get invoice status from ETA.
     */
    public function getInvoiceStatus(string $uuid): array
    {
        try {
            $response = $this->authService->request('GET', "api/documents/{$uuid}");
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to get invoice status',
                'errors' => $response->json(),
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting invoice status: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sync invoice status from ETA.
     */
    public function syncInvoiceStatus(Invoice $invoice): bool
    {
        $result = $this->getInvoiceStatus($invoice->uuid);
        
        if (!$result['success']) {
            return false;
        }
        
        $etaData = $result['data'];
        
        // Map ETA status to local status
        $statusMap = [
            'Valid' => Invoice::STATUS_VALID,
            'Invalid' => Invoice::STATUS_INVALID,
            'Rejected' => Invoice::STATUS_REJECTED,
            'Submitted' => Invoice::STATUS_SUBMITTED,
            'Cancelled' => Invoice::STATUS_CANCELLED,
        ];
        
        $etaStatus = $etaData['status'] ?? null;
        $newStatus = $statusMap[$etaStatus] ?? $invoice->status;
        
        $invoice->update([
            'status' => $newStatus,
            'eta_response' => $etaData,
            'is_synced' => true,
            'synced_at' => now(),
        ]);
        
        // Log the sync
        AuditLog::log(
            AuditLog::ACTION_SYNCED,
            $invoice,
            ['eta_status' => $etaStatus, 'local_status' => $newStatus],
            $this->company->id
        );
        
        return true;
    }

    /**
     * Cancel invoice in ETA.
     */
    public function cancelInvoice(Invoice $invoice, string $reason): array
    {
        try {
            if ($invoice->status === Invoice::STATUS_CANCELLED) {
                return [
                    'success' => false,
                    'message' => 'Invoice is already cancelled',
                ];
            }
            
            $response = $this->authService->request('PUT', "api/documents/{$invoice->uuid}/cancel", [
                'reason' => $reason,
            ]);
            
            if ($response->successful()) {
                $invoice->update([
                    'status' => Invoice::STATUS_CANCELLED,
                    'eta_response' => $response->json(),
                ]);
                
                AuditLog::log(
                    AuditLog::ACTION_CANCELLED,
                    $invoice,
                    ['cancellation_reason' => $reason],
                    $this->company->id
                );
                
                return [
                    'success' => true,
                    'message' => 'Invoice cancelled successfully',
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to cancel invoice',
                'errors' => $response->json(),
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error cancelling invoice: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get recent documents from ETA.
     */
    public function getRecentDocuments(int $limit = 50, string $type = 'sent'): array
    {
        try {
            $endpoint = $type === 'sent' ? 'api/documents/sent' : 'api/documents/received';
            
            $response = $this->authService->request('GET', $endpoint, [
                'limit' => $limit,
            ]);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to get recent documents',
                'errors' => $response->json(),
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error getting recent documents: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Search documents in ETA.
     */
    public function searchDocuments(array $filters): array
    {
        try {
            $response = $this->authService->request('GET', 'api/documents/search', $filters);
            
            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }
            
            return [
                'success' => false,
                'message' => 'Failed to search documents',
                'errors' => $response->json(),
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error searching documents: ' . $e->getMessage(),
            ];
        }
    }
}

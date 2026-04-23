<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\EtaInvoiceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncInvoicesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public Company $company,
        public int $limit = 100
    ) {}

    public function handle(EtaInvoiceService $invoiceService): void
    {
        Log::info('بدء مزامنة الفواتير', ['company_id' => $this->company->id]);

        try {
            $invoices = $this->company->invoices()
                ->whereIn('status', ['SUBMITTED', 'PENDING'])
                ->limit($this->limit)
                ->get();

            foreach ($invoices as $invoice) {
                try {
                    $invoiceService->syncInvoiceStatus($invoice);
                    Log::info('تم تحديث حالة الفاتورة', [
                        'invoice_id' => $invoice->id,
                        'uuid' => $invoice->uuid,
                        'new_status' => $invoice->status
                    ]);
                } catch (\Exception $e) {
                    Log::error('فشل في مزامنة الفاتورة', [
                        'invoice_id' => $invoice->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            Log::info('اكتملت مزامنة الفواتير', [
                'company_id' => $this->company->id,
                'count' => $invoices->count()
            ]);

        } catch (\Exception $e) {
            Log::error('خطأ فادح في مزامنة الفواتير', [
                'company_id' => $this->company->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

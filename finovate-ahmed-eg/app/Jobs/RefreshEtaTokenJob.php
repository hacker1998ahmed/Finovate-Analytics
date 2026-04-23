<?php

namespace App\Jobs;

use App\Models\Company;
use App\Services\EtaAuthService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RefreshEtaTokenJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Company $company) {}

    public function handle(EtaAuthService $authService): void
    {
        Log::info('بدء تحديث توكن ETA', ['company_id' => $this->company->id]);

        try {
            $token = $authService->refreshToken($this->company);
            
            if ($token) {
                Log::info('تم تحديث توكن ETA بنجاح', [
                    'company_id' => $this->company->id,
                    'expires_at' => $token->expires_at
                ]);
            } else {
                Log::warning('فشل في تحديث توكن ETA', ['company_id' => $this->company->id]);
            }

        } catch (\Exception $e) {
            Log::error('خطأ في تحديث توكن ETA', [
                'company_id' => $this->company->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

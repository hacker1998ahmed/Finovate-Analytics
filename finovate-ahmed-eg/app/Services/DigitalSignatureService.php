<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Exception;

class DigitalSignatureService
{
    /**
     * توقيع فاتورة إلكترونياً باستخدام شهادة ETA
     */
    public function signInvoice(array $invoiceData, string $certificatePath, string $certificatePassword): array
    {
        try {
            // تحميل الشهادة
            $certificate = file_get_contents($certificatePath);
            
            if (!$certificate) {
                throw new Exception('فشل في تحميل الشهادة الرقمية');
            }

            // تحويل بيانات الفاتورة إلى JSON
            $jsonData = json_encode($invoiceData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            
            // إنشاء Hash للبيانات
            $hash = hash('sha256', $jsonData, true);
            
            // توقيع البيانات باستخدام المفتاح الخاص
            $signature = null;
            $result = openssl_sign($hash, $signature, $certificate, OPENSSL_ALGO_SHA256);
            
            if (!$result) {
                throw new Exception('فشل في التوقيع الرقمي: ' . openssl_error_string());
            }
            
            // تشفير التوقيع Base64
            $signatureBase64 = base64_encode($signature);
            
            // إضافة التوقيع إلى بيانات الفاتورة
            $invoiceData['signature'] = [
                'type' => 'http://www.w3.org/2001/04/xmldsig-more#rsa-sha256',
                'value' => $signatureBase64,
                'signed_properties' => $this->generateSignedProperties($invoiceData),
            ];
            
            Log::info('تم توقيع الفاتورة بنجاح', ['uuid' => $invoiceData['uuid'] ?? 'unknown']);
            
            return $invoiceData;
            
        } catch (Exception $e) {
            Log::error('خطأ في التوقيع الرقمي', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            throw $e;
        }
    }

    /**
     * توليد الخصائص الموقعة (Signed Properties)
     */
    private function generateSignedProperties(array $invoiceData): array
    {
        return [
            'signing_time' => now()->format('Y-m-d\TH:i:s\Z'),
            'signing_certificate_digest' => hash('sha256', $invoiceData['seller']['tax_number'] ?? ''),
            'invoice_hash' => hash('sha256', json_encode($invoiceData)),
        ];
    }

    /**
     * التحقق من صحة التوقيع
     */
    public function verifySignature(string $data, string $signature, string $publicKey): bool
    {
        try {
            $hash = hash('sha256', $data, true);
            $signatureDecoded = base64_decode($signature);
            
            $result = openssl_verify($hash, $signatureDecoded, $publicKey, OPENSSL_ALGO_SHA256);
            
            return $result === 1;
        } catch (Exception $e) {
            Log::error('خطأ في التحقق من التوقيع', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * استخراج الشهادة من ملف PFX/P12
     */
    public function extractCertificate(string $pfxPath, string $password): array
    {
        try {
            $pfxData = file_get_contents($pfxPath);
            
            if (!$pfxData) {
                throw new Exception('فشل في تحميل ملف الشهادة');
            }

            // استخراج الشهادة والمفتاح الخاص
            $certificates = [];
            $privateKey = null;
            
            openssl_pkcs12_read($pfxData, $certificates, $password);
            
            if (!isset($certificates['cert']) || !isset($certificates['pkey'])) {
                throw new Exception('فشل في استخراج الشهادة أو المفتاح الخاص');
            }

            return [
                'certificate' => $certificates['cert'],
                'private_key' => $certificates['pkey'],
                'extra_certs' => $certificates['extracerts'] ?? [],
            ];
            
        } catch (Exception $e) {
            Log::error('خطأ في استخراج الشهادة', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}

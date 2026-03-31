<?php

namespace App\Jobs;

use App\Helpers\CustomExceptionHandler;
use App\Services\POS\POSRateApiService;
use App\Services\POS\POSRateService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SyncPosRatesJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 60;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * @param POSRateApiService $posRateApiService
     * @param POSRateService $posRateService
     * @return void
     */
    public function handle(POSRateApiService $posRateApiService, POSRateService $posRateService)
    {
        Log::info('SyncPosRatesJob started');

        try {
            $response = $posRateApiService->fetchRates();
            if (is_array($response->data) && count($response->data) > 0) {
                $posRateServiceResult = $posRateService->upsert($response->data);
                if (!$posRateServiceResult->success) {
                    throw new CustomExceptionHandler($posRateServiceResult->message, 'INTERNAL_SERVER_ERROR');
                }
            } else {
                Log::error('SyncPosRatesJob@handle: No data received');
            }
        } catch (\Exception $exception) {
            Log::error('SyncPosRatesJob@handle Error::', [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            throw new CustomExceptionHandler('SyncPosRatesJob@handle Error::' . $exception->getMessage(), 'INTERNAL_SERVER_ERROR');
        }

        Log::info('SyncPosRatesJob finished');
    }
}

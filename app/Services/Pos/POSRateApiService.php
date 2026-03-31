<?php

namespace App\Services\POS;

use App\Helpers\BaseResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class POSRateApiService
{
    private string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.pos_rates_url');
    }

    public function fetchRates(): BaseResponse
    {
        try {
            $response = Http::timeout(10)->get($this->apiUrl);
            if ($response->failed()) {
                return BaseResponse::error("POSRateApiService@fetchRates Error::connectionFailed",400);
            }

            $data = $response->json();
            if (empty($data) || !is_array($data)) {
                return BaseResponse::error("POSRateApiService@fetchRates Error::invalidResponse",400);
            }

            return BaseResponse::success($data);
        } catch (\Exception $exception) {
            Log::error("POSRateApiService@fetchRates Error::" , [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            return BaseResponse::error("POSRateApiService@fetchRates Error::" . $exception->getMessage());
        }
    }
}

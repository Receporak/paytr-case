<?php

namespace App\Repositories;

use App\Helpers\BaseResponse;
use App\Helpers\CustomExceptionHandler;
use App\Models\POSRate;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class POSRateRepository
{

    public function __construct(private POSRate $model)
    {
    }

    public function getAll(): BaseResponse
    {
        try {
            $posRates = Cache::remember("pos_rates", 3600, function () {
                return POSRate::query()->get()->toArray();
            });
            return BaseResponse::success($posRates);
        } catch (\Exception $exception) {
            Log::error("POSRateRepository@getAll Error::" , [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            throw new CustomExceptionHandler("Something went wrong" ,"INTERNAL_SERVER_ERROR", 500);
        }
    }

    public function upsert($rates): BaseResponse
    {
        try {
            $posRates = $this->model->upsert(
                $rates,
                ['pos_name', 'card_type', 'card_brand', 'installment', 'currency'],
                ['commission_rate']
            );

            Cache::forget("pos_rates");
            return BaseResponse::success($posRates);
        } catch (\Exception $exception) {
            Log::error("POSRateRepository@insert Error::" , [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            throw new CustomExceptionHandler("Something went wrong" ,"INTERNAL_SERVER_ERROR", 500);
        }
    }
}

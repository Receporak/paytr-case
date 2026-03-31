<?php

namespace App\Services\POS\Strategies;

use App\DTOs\POS\POSSelectionResultDTO;
use App\DTOs\POS\POSSelectionDTO;
use App\Helpers\CustomExceptionHandler;
use App\Services\POS\Strategies\Contracts\POSSelectionStrategyInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class LowestCostStrategy implements POSSelectionStrategyInterface
{

    public function select(Collection $rates, POSSelectionDTO $posSelectionData): POSSelectionResultDTO
    {
        try {
            $filtered = $rates
                ->where('currency', $posSelectionData->currency)
                ->where('card_type', $posSelectionData->cardType)
                ->where('installment', $posSelectionData->installment);

            if ($filtered->isEmpty()) {
                throw new CustomExceptionHandler('Not found POS with this criteria', 'POS_NOT_FOUND', 404);
            }

            $pos = $filtered->sortBy('commission_rate')->first();

            return new POSSelectionResultDTO(
                posName: $pos['pos_name'],
                cardType: $pos['card_type'],
                cardBrand: $pos['card_brand'],
                installment: $pos['installment'],
                currency: $pos['currency'],
                commissionRate: (float)$pos['commission_rate']
            );
        } catch (\Exception $exception) {
            Log::error("LowestCostStrategy@select Error::", [
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ]);
            if ($exception instanceof CustomExceptionHandler) {
                throw $exception;
            }
            throw new CustomExceptionHandler('Something went wrong', 'INTERNAL_SERVER_ERROR', 500);
        }
    }
}

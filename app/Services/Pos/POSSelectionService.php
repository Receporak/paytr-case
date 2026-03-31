<?php

namespace App\Services\POS;

use App\DTOs\POS\POSSelectionDTO;
use App\DTOs\POS\POSSelectionResultDTO;
use App\Helpers\CustomExceptionHandler;
use App\Repositories\POSRateRepository;
use App\Services\POS\Strategies\Contracts\POSSelectionStrategyInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;

class POSSelectionService
{
    private POSRateService $POSRateService;
    private POSSelectionStrategyInterface $strategy;

    public function __construct(POSRateService $POSRateService, POSSelectionStrategyInterface $strategy)
    {
        $this->POSRateService = $POSRateService;
        $this->strategy = $strategy;
    }

    public function selectLowest(POSSelectionDTO $posSelectionDTO): POSSelectionResultDTO
    {
        $rates = $this->POSRateService->getAll();
        if (!$rates->success) {
            throw new CustomExceptionHandler($rates->message, 'INTERNAL_SERVER_ERROR', $rates->code);
        }
        return $this->strategy->select(Collection::make($rates->data), $posSelectionDTO);
    }

}

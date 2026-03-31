<?php

namespace App\Services\POS;

use App\Helpers\BaseResponse;
use App\Repositories\POSRateRepository;

class POSRateService
{
    private POSRateRepository $POSRateRepository;

    public function __construct(POSRateRepository $POSRateRepository)
    {
        $this->POSRateRepository = $POSRateRepository;
    }

    public function getAll(): BaseResponse
    {
        return $this->POSRateRepository->getAll();
    }

    public function upsert($rates): BaseResponse
    {
        return $this->POSRateRepository->upsert($rates);
    }
}

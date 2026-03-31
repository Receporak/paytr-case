<?php

namespace App\Services\POS\Strategies\Contracts;

use App\DTOs\POS\POSSelectionDTO;
use App\DTOs\POS\POSSelectionResultDTO;
use Illuminate\Database\Eloquent\Collection;

interface POSSelectionStrategyInterface
{
    public function select(Collection $rates,POSSelectionDTO $posSelectionData): POSSelectionResultDTO;

}

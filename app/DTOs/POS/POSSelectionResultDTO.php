<?php

namespace App\DTOs\POS;

class POSSelectionResultDTO
{
    public function __construct(
        public readonly string $posName,
        public readonly string $cardType,
        public readonly string $cardBrand,
        public readonly int    $installment,
        public readonly string $currency,
        public readonly float  $commissionRate,
    )
    {
    }

    public function toArray(): array
    {
        return [
            'pos_name' => $this->posName,
            'card_type' => $this->cardType,
            'card_brand' => $this->cardBrand,
            'installment' => $this->installment,
            'currency' => $this->currency,
            'commission_rate' => $this->commissionRate,
        ];
    }
}

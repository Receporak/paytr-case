<?php

namespace App\DTOs\POS;

class POSSelectionDTO
{
    public function __construct(
        public readonly string $currency,
        public readonly string $cardType,
        public readonly int    $installment,
    )
    {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            currency: $data['currency'],
            cardType: $data['card_type'],
            installment: $data['installment'],
        );
    }
}

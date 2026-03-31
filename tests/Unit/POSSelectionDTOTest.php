<?php

namespace Tests\Unit;

use App\DTOs\POS\POSSelectionDTO;
use Tests\TestCase;

class POSSelectionDTOTest extends TestCase
{
    public function test_from_array_creates_dto_correctly(): void
    {
        $dto = POSSelectionDTO::fromArray([
            'currency'    => 'TRY',
            'card_type'   => 'credit',
            'installment' => 3,
        ]);

        $this->assertEquals('TRY', $dto->currency);
        $this->assertEquals('credit', $dto->cardType);
        $this->assertEquals(3, $dto->installment);
    }

    public function test_properties_are_readonly(): void
    {
        $dto = POSSelectionDTO::fromArray([
            'currency'    => 'USD',
            'card_type'   => 'debit',
            'installment' => 1,
        ]);

        $this->expectException(\Error::class);

        $dto->currency = 'EUR';
    }

    public function test_from_array_maps_card_type_key_correctly(): void
    {
        $dto = POSSelectionDTO::fromArray([
            'currency'    => 'EUR',
            'card_type'   => 'unknown',
            'installment' => 6,
        ]);

        $this->assertEquals('unknown', $dto->cardType);
        $this->assertEquals(6, $dto->installment);
    }
}

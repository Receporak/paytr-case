<?php

namespace Tests\Unit;

use App\DTOs\POS\POSSelectionResultDTO;
use Tests\TestCase;

class POSSelectionResultDTOTest extends TestCase
{
    private function makeDTO(float $commissionRate = 1.5): POSSelectionResultDTO
    {
        return new POSSelectionResultDTO(
            posName: 'Test Bank',
            cardType: 'credit',
            cardBrand: 'visa',
            installment: 3,
            currency: 'TRY',
            commissionRate: $commissionRate,
        );
    }

    public function test_to_array_returns_all_fields(): void
    {
        $array = $this->makeDTO()->toArray();

        $this->assertArrayHasKey('pos_name', $array);
        $this->assertArrayHasKey('card_type', $array);
        $this->assertArrayHasKey('card_brand', $array);
        $this->assertArrayHasKey('installment', $array);
        $this->assertArrayHasKey('currency', $array);
        $this->assertArrayHasKey('commission_rate', $array);
    }

    public function test_to_array_values_are_correct(): void
    {
        $array = $this->makeDTO(2.75)->toArray();

        $this->assertEquals('Test Bank', $array['pos_name']);
        $this->assertEquals('credit', $array['card_type']);
        $this->assertEquals('visa', $array['card_brand']);
        $this->assertEquals(3, $array['installment']);
        $this->assertEquals('TRY', $array['currency']);
        $this->assertEquals(2.75, $array['commission_rate']);
    }

    public function test_properties_are_readonly(): void
    {
        $dto = $this->makeDTO();

        $this->expectException(\Error::class);

        $dto->posName = 'Other Bank';
    }
}

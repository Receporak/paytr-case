<?php

namespace Tests\Unit;

use App\DTOs\POS\POSSelectionDTO;
use App\DTOs\POS\POSSelectionResultDTO;
use App\Helpers\CustomExceptionHandler;
use App\Services\POS\Strategies\LowestCostStrategy;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class LowestCostStrategyTest extends TestCase
{
    private LowestCostStrategy $strategy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->strategy = new LowestCostStrategy();
    }

    private function makeRates(array $overrides = []): array
    {
        return array_merge([
            'pos_name'        => 'Test Bank',
            'card_type'       => 'credit',
            'card_brand'      => 'visa',
            'installment'     => 1,
            'currency'        => 'TRY',
            'commission_rate' => 1.5,
        ], $overrides);
    }

    private function makeDTO(array $overrides = []): POSSelectionDTO
    {
        return POSSelectionDTO::fromArray(array_merge([
            'currency'    => 'TRY',
            'card_type'   => 'credit',
            'installment' => 1,
        ], $overrides));
    }

    public function test_returns_lowest_commission_rate(): void
    {
        $rates = Collection::make([
            $this->makeRates(['pos_name' => 'Bank A', 'commission_rate' => 2.0]),
            $this->makeRates(['pos_name' => 'Bank B', 'commission_rate' => 1.0]),
            $this->makeRates(['pos_name' => 'Bank C', 'commission_rate' => 1.5]),
        ]);

        $result = $this->strategy->select($rates, $this->makeDTO());

        $this->assertInstanceOf(POSSelectionResultDTO::class, $result);
        $this->assertEquals('Bank B', $result->posName);
        $this->assertEquals(1.0, $result->commissionRate);
    }

    public function test_throws_exception_when_no_matching_rates(): void
    {
        $rates = Collection::make([
            $this->makeRates(['currency' => 'USD']),
        ]);

        $this->expectException(CustomExceptionHandler::class);
        $this->expectExceptionMessage('Not found POS with this criteria');

        $this->strategy->select($rates, $this->makeDTO(['currency' => 'TRY']));
    }

    public function test_throws_pos_not_found_error_code(): void
    {
        $rates = Collection::make([]);

        try {
            $this->strategy->select($rates, $this->makeDTO());
            $this->fail('Expected CustomExceptionHandler was not thrown');
        } catch (CustomExceptionHandler $e) {
            $this->assertEquals(404, $e->getCode());
        }
    }

    public function test_filters_by_currency(): void
    {
        $rates = Collection::make([
            $this->makeRates(['pos_name' => 'Bank TRY', 'currency' => 'TRY', 'commission_rate' => 1.0]),
            $this->makeRates(['pos_name' => 'Bank USD', 'currency' => 'USD', 'commission_rate' => 0.5]),
        ]);

        $result = $this->strategy->select($rates, $this->makeDTO(['currency' => 'TRY']));

        $this->assertEquals('Bank TRY', $result->posName);
    }

    public function test_filters_by_card_type(): void
    {
        $rates = Collection::make([
            $this->makeRates(['pos_name' => 'Credit Bank', 'card_type' => 'credit', 'commission_rate' => 2.0]),
            $this->makeRates(['pos_name' => 'Debit Bank',  'card_type' => 'debit',  'commission_rate' => 0.5]),
        ]);

        $result = $this->strategy->select($rates, $this->makeDTO(['card_type' => 'credit']));

        $this->assertEquals('Credit Bank', $result->posName);
    }

    public function test_filters_by_installment(): void
    {
        $rates = Collection::make([
            $this->makeRates(['pos_name' => 'Bank 1', 'installment' => 1, 'commission_rate' => 1.0]),
            $this->makeRates(['pos_name' => 'Bank 3', 'installment' => 3, 'commission_rate' => 0.5]),
        ]);

        $result = $this->strategy->select($rates, $this->makeDTO(['installment' => 3]));

        $this->assertEquals('Bank 3', $result->posName);
    }

    public function test_returns_correct_dto_fields(): void
    {
        $rates = Collection::make([
            $this->makeRates([
                'pos_name'        => 'My Bank',
                'card_brand'      => 'mastercard',
                'commission_rate' => 1.25,
            ]),
        ]);

        $result = $this->strategy->select($rates, $this->makeDTO());

        $this->assertEquals('My Bank', $result->posName);
        $this->assertEquals('credit', $result->cardType);
        $this->assertEquals('mastercard', $result->cardBrand);
        $this->assertEquals(1, $result->installment);
        $this->assertEquals('TRY', $result->currency);
        $this->assertEquals(1.25, $result->commissionRate);
    }

    public function test_returns_first_when_multiple_have_same_rate(): void
    {
        $rates = Collection::make([
            $this->makeRates(['pos_name' => 'Bank A', 'commission_rate' => 1.0]),
            $this->makeRates(['pos_name' => 'Bank B', 'commission_rate' => 1.0]),
        ]);

        $result = $this->strategy->select($rates, $this->makeDTO());

        // Aynı rate olduğunda ilk sıralanan döner — fiyat doğru olmalı
        $this->assertEquals(1.0, $result->commissionRate);
    }
}

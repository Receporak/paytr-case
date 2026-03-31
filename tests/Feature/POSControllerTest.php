<?php

namespace Tests\Feature;

use App\Models\POSRate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class POSControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createPosRate(array $overrides = []): POSRate
    {
        return POSRate::create(array_merge([
            'pos_name'        => 'Test Bank',
            'card_type'       => 'credit',
            'card_brand'      => 'visa',
            'installment'     => 1,
            'currency'        => 'TRY',
            'commission_rate' => 1.5,
            'min_fee'         => null,
            'priority'        => 0,
        ], $overrides));
    }

    // -------------------------------------------------------------------------
    // Başarılı istek
    // -------------------------------------------------------------------------

    public function test_select_returns_lowest_cost_pos(): void
    {
        $this->createPosRate(['pos_name' => 'Expensive Bank', 'commission_rate' => 2.50]);
        $this->createPosRate(['pos_name' => 'Cheap Bank',     'commission_rate' => 1.25]);

        $response = $this->postJson('/api/v1/pos/select', [
            'currency'    => 'TRY',
            'card_type'   => 'credit',
            'installment' => 1,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.overall_min.pos_name', 'Cheap Bank')
            ->assertJsonPath('data.overall_min.commission_rate', 1.25);
    }

    public function test_select_response_contains_filters(): void
    {
        $this->createPosRate(['card_brand' => 'mastercard']);

        $response = $this->postJson('/api/v1/pos/select', [
            'currency'    => 'TRY',
            'card_type'   => 'credit',
            'installment' => 1,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.filters.currency', 'TRY')
            ->assertJsonPath('data.filters.card_type', 'credit')
            ->assertJsonPath('data.filters.installment', 1);
    }

    public function test_select_response_structure(): void
    {
        $this->createPosRate();

        $response = $this->postJson('/api/v1/pos/select', [
            'currency'    => 'TRY',
            'card_type'   => 'credit',
            'installment' => 1,
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'code',
                'data' => [
                    'filters' => ['installment', 'currency', 'card_type', 'card_brand'],
                    'overall_min' => [
                        'pos_name', 'card_type', 'card_brand',
                        'installment', 'currency', 'commission_rate',
                    ],
                ],
            ]);
    }

    // -------------------------------------------------------------------------
    // Validation hataları
    // -------------------------------------------------------------------------

    public function test_select_requires_currency(): void
    {
        $response = $this->postJson('/api/v1/pos/select', [
            'card_type'   => 'credit',
            'installment' => 1,
        ]);

        $response->assertOk() // HTTP body success=false
            ->assertJsonPath('success', false);
    }

    public function test_select_rejects_invalid_currency(): void
    {
        $response = $this->postJson('/api/v1/pos/select', [
            'currency'    => 'GBP',
            'card_type'   => 'credit',
            'installment' => 1,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', false);
    }

    public function test_select_requires_card_type(): void
    {
        $response = $this->postJson('/api/v1/pos/select', [
            'currency'    => 'TRY',
            'installment' => 1,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', false);
    }

    public function test_select_rejects_invalid_card_type(): void
    {
        $response = $this->postJson('/api/v1/pos/select', [
            'currency'    => 'TRY',
            'card_type'   => 'prepaid',
            'installment' => 1,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', false);
    }

    public function test_select_requires_installment(): void
    {
        $response = $this->postJson('/api/v1/pos/select', [
            'currency'  => 'TRY',
            'card_type' => 'credit',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', false);
    }

    public function test_select_rejects_installment_less_than_one(): void
    {
        $response = $this->postJson('/api/v1/pos/select', [
            'currency'    => 'TRY',
            'card_type'   => 'credit',
            'installment' => 0,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', false);
    }

    public function test_select_rejects_non_integer_installment(): void
    {
        $response = $this->postJson('/api/v1/pos/select', [
            'currency'    => 'TRY',
            'card_type'   => 'credit',
            'installment' => 'abc',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', false);
    }

    // -------------------------------------------------------------------------
    // İş mantığı hataları
    // -------------------------------------------------------------------------

    public function test_select_returns_404_when_no_matching_pos(): void
    {
        $this->createPosRate(['currency' => 'USD']);

        $response = $this->postJson('/api/v1/pos/select', [
            'currency'    => 'TRY',
            'card_type'   => 'credit',
            'installment' => 1,
        ]);

        $response->assertStatus(404)
            ->assertJsonPath('success', false)
            ->assertJsonPath('error_code', 'POS_NOT_FOUND');
    }

    public function test_select_filters_correctly_by_all_criteria(): void
    {
        // Sadece USD için kayıt var
        $this->createPosRate(['currency' => 'USD', 'card_type' => 'debit', 'installment' => 3, 'commission_rate' => 0.75]);
        // Eşleşen kayıt
        $this->createPosRate(['currency' => 'TRY', 'card_type' => 'credit', 'installment' => 1, 'commission_rate' => 1.75]);

        $response = $this->postJson('/api/v1/pos/select', [
            'currency'    => 'TRY',
            'card_type'   => 'credit',
            'installment' => 1,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.overall_min.commission_rate', 1.75);
    }

    public function test_select_with_all_valid_currencies(): void
    {
        foreach (['TRY', 'USD', 'EUR'] as $currency) {
            $this->createPosRate(['currency' => $currency, 'pos_name' => "Bank {$currency}"]);

            $response = $this->postJson('/api/v1/pos/select', [
                'currency'    => $currency,
                'card_type'   => 'credit',
                'installment' => 1,
            ]);

            $response->assertOk()->assertJsonPath('success', true);
        }
    }

    public function test_select_with_all_valid_card_types(): void
    {
        foreach (['credit', 'debit', 'unknown'] as $cardType) {
            $this->createPosRate(['card_type' => $cardType, 'pos_name' => "Bank {$cardType}"]);

            $response = $this->postJson('/api/v1/pos/select', [
                'currency'    => 'TRY',
                'card_type'   => $cardType,
                'installment' => 1,
            ]);

            $response->assertOk()->assertJsonPath('success', true);
        }
    }
}

<?php

namespace Tests\Feature;

use App\Helpers\BaseResponse;
use App\Jobs\SyncPosRatesJob;
use App\Models\POSRate;
use App\Services\POS\POSRateApiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SyncPosRatesJobTest extends TestCase
{
    use RefreshDatabase;

    private array $sampleRates = [
        [
            'pos_name'        => 'Bank A',
            'card_type'       => 'credit',
            'card_brand'      => 'visa',
            'installment'     => 1,
            'currency'        => 'TRY',
            'commission_rate' => 1.5,
            'min_fee'         => null,
            'priority'        => 0,
        ],
        [
            'pos_name'        => 'Bank B',
            'card_type'       => 'debit',
            'card_brand'      => 'mastercard',
            'installment'     => 1,
            'currency'        => 'USD',
            'commission_rate' => 2.0,
            'min_fee'         => null,
            'priority'        => 0,
        ],
    ];

    public function test_job_upserts_rates_to_database(): void
    {
        $apiService = $this->createMock(POSRateApiService::class);
        $apiService->method('fetchRates')
            ->willReturn(BaseResponse::success($this->sampleRates));

        $this->app->instance(POSRateApiService::class, $apiService);

        SyncPosRatesJob::dispatchSync();

        $this->assertDatabaseCount('pos_rates', 2);
        $this->assertDatabaseHas('pos_rates', ['pos_name' => 'Bank A']);
        $this->assertDatabaseHas('pos_rates', ['pos_name' => 'Bank B']);
    }

    public function test_job_logs_error_when_api_returns_empty_data(): void
    {
        Log::spy();

        $apiService = $this->createMock(POSRateApiService::class);
        $apiService->method('fetchRates')
            ->willReturn(BaseResponse::success([]));

        $this->app->instance(POSRateApiService::class, $apiService);

        SyncPosRatesJob::dispatchSync();

        Log::shouldHaveReceived('error')
            ->with('SyncPosRatesJob@handle: No data received')
            ->once();
    }

    public function test_job_logs_error_when_api_fails(): void
    {
        Log::spy();

        $apiService = $this->createMock(POSRateApiService::class);
        $apiService->method('fetchRates')
            ->willReturn(BaseResponse::error('Connection failed', 400));

        $this->app->instance(POSRateApiService::class, $apiService);

        SyncPosRatesJob::dispatchSync();

        // API başarısız olunca data null gelir → "No data received" logu atılmalı
        Log::shouldHaveReceived('error')
            ->with('SyncPosRatesJob@handle: No data received')
            ->once();
    }

    public function test_job_does_not_insert_when_no_data(): void
    {
        $apiService = $this->createMock(POSRateApiService::class);
        $apiService->method('fetchRates')
            ->willReturn(BaseResponse::success([]));

        $this->app->instance(POSRateApiService::class, $apiService);

        SyncPosRatesJob::dispatchSync();

        $this->assertDatabaseCount('pos_rates', 0);
    }

    public function test_job_has_correct_retry_configuration(): void
    {
        $job = new SyncPosRatesJob();

        $this->assertEquals(3, $job->tries);
        $this->assertEquals(60, $job->backoff);
    }

    public function test_job_updates_existing_rate_on_upsert(): void
    {
        POSRate::create([
            'pos_name'        => 'Bank A',
            'card_type'       => 'credit',
            'card_brand'      => 'visa',
            'installment'     => 1,
            'currency'        => 'TRY',
            'commission_rate' => 9.9,
            'priority'        => 0,
        ]);

        $updatedRates = [[
            'pos_name'        => 'Bank A',
            'card_type'       => 'credit',
            'card_brand'      => 'visa',
            'installment'     => 1,
            'currency'        => 'TRY',
            'commission_rate' => 1.5,
            'min_fee'         => null,
            'priority'        => 0,
        ]];

        $apiService = $this->createMock(POSRateApiService::class);
        $apiService->method('fetchRates')
            ->willReturn(BaseResponse::success($updatedRates));

        $this->app->instance(POSRateApiService::class, $apiService);

        SyncPosRatesJob::dispatchSync();

        $this->assertDatabaseCount('pos_rates', 1);
        $this->assertDatabaseHas('pos_rates', [
            'pos_name'        => 'Bank A',
            'commission_rate' => 1.5,
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Jobs\SyncPosRatesJob;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class POSRateSyncTest extends TestCase
{
    public function test_rate_sync_dispatches_job(): void
    {
        Queue::fake();

        $this->postJson('/api/v1/pos/rates-sync')
            ->assertOk()
            ->assertJsonPath('success', true);

        Queue::assertPushed(SyncPosRatesJob::class);
    }

    public function test_rate_sync_returns_success_message(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/pos/rates-sync');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', 'Senkronizasyon kuyruğa alındı');
    }

    public function test_rate_sync_dispatches_job_exactly_once(): void
    {
        Queue::fake();

        $this->postJson('/api/v1/pos/rates-sync');

        Queue::assertPushed(SyncPosRatesJob::class, 1);
    }
}

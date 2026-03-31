<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CorrelationIdMiddlewareTest extends TestCase
{
    public function test_response_contains_correlation_id_header(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/pos/rates-sync');

        $response->assertHeader('X-Correlation-ID');
    }

    public function test_uses_provided_correlation_id(): void
    {
        Queue::fake();

        $correlationId = 'test-correlation-id-1234';

        $response = $this->postJson('/api/v1/pos/rates-sync', [], [
            'X-Correlation-ID' => $correlationId,
        ]);

        $response->assertHeader('X-Correlation-ID', $correlationId);
    }

    public function test_generates_uuid_when_no_correlation_id_provided(): void
    {
        Queue::fake();

        $response = $this->postJson('/api/v1/pos/rates-sync');

        $correlationId = $response->headers->get('X-Correlation-ID');

        // UUID formatını doğrula: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $correlationId
        );
    }

    public function test_different_requests_get_different_correlation_ids(): void
    {
        Queue::fake();

        $first  = $this->postJson('/api/v1/pos/rates-sync')->headers->get('X-Correlation-ID');
        $second = $this->postJson('/api/v1/pos/rates-sync')->headers->get('X-Correlation-ID');

        $this->assertNotEquals($first, $second);
    }
}

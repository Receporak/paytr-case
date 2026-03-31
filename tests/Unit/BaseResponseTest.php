<?php

namespace Tests\Unit;

use App\Helpers\BaseResponse;
use Tests\TestCase;

class BaseResponseTest extends TestCase
{
    public function test_success_creates_correct_response(): void
    {
        $response = BaseResponse::success(['key' => 'value']);

        $this->assertTrue($response->success);
        $this->assertEquals('Success', $response->message);
        $this->assertEquals(['key' => 'value'], $response->data);
        $this->assertEquals(200, $response->code);
    }

    public function test_success_with_custom_message_and_code(): void
    {
        $response = BaseResponse::success('ok', 'Created', 201);

        $this->assertTrue($response->success);
        $this->assertEquals('Created', $response->message);
        $this->assertEquals('ok', $response->data);
        $this->assertEquals(201, $response->code);
    }

    public function test_success_with_null_data(): void
    {
        $response = BaseResponse::success();

        $this->assertTrue($response->success);
        $this->assertNull($response->data);
    }

    public function test_error_creates_correct_response(): void
    {
        $response = BaseResponse::error('Something failed', 400);

        $this->assertFalse($response->success);
        $this->assertEquals('Something failed', $response->message);
        $this->assertNull($response->data);
        $this->assertEquals(400, $response->code);
    }

    public function test_error_default_code_is_400(): void
    {
        $response = BaseResponse::error('Oops');

        $this->assertEquals(400, $response->code);
    }

    public function test_to_array_returns_correct_structure(): void
    {
        $response = BaseResponse::success(['foo' => 'bar']);

        $array = $response->toArray();

        $this->assertArrayHasKey('success', $array);
        $this->assertArrayHasKey('message', $array);
        $this->assertArrayHasKey('data', $array);
        $this->assertArrayHasKey('code', $array);
        $this->assertTrue($array['success']);
        $this->assertEquals(['foo' => 'bar'], $array['data']);
        $this->assertEquals(200, $array['code']);
    }

    public function test_error_to_array_has_null_data(): void
    {
        $array = BaseResponse::error('fail')->toArray();

        $this->assertFalse($array['success']);
        $this->assertNull($array['data']);
    }
}

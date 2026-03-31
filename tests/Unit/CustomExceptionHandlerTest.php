<?php

namespace Tests\Unit;

use App\Helpers\CustomExceptionHandler;
use Illuminate\Http\Request;
use Tests\TestCase;

class CustomExceptionHandlerTest extends TestCase
{
    public function test_render_returns_json_response(): void
    {
        $exception = new CustomExceptionHandler('Not found', 'POS_NOT_FOUND', 404);
        $request   = Request::create('/api/v1/pos/select', 'POST');

        $response = $exception->render($request);

        $this->assertEquals(404, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertFalse($data['success']);
        $this->assertEquals('POS_NOT_FOUND', $data['error_code']);
        $this->assertEquals('Not found', $data['message']);
        $this->assertEquals(404, $data['code']);
        $this->assertNull($data['data']);
    }

    public function test_default_http_status_is_422(): void
    {
        $exception = new CustomExceptionHandler('Unprocessable', 'VALIDATION_ERROR');

        $this->assertEquals(422, $exception->getCode());
    }

    public function test_extends_runtime_exception(): void
    {
        $exception = new CustomExceptionHandler('Error', 'INTERNAL_SERVER_ERROR', 500);

        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function test_message_is_accessible(): void
    {
        $exception = new CustomExceptionHandler('Custom message', 'SOME_CODE', 400);

        $this->assertEquals('Custom message', $exception->getMessage());
    }
}

<?php

namespace App\Helpers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomExceptionHandler extends \RuntimeException
{
    public function __construct(string $message, private readonly string $errorCode, int $httpStatus = 422)
    {
        parent::__construct($message, $httpStatus);
    }

    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error_code' => $this->errorCode,
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'data' => null,
        ], $this->getCode());
    }
}

<?php

namespace App\Helpers;

class BaseResponse
{
    public bool $success;
    public string $message;
    public mixed $data;
    public int $code;

    private function __construct(bool $success, string $message, mixed $data, int $code)
    {
        $this->success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->code = $code;
    }

    public static function success(mixed $data = null, string $message = 'Success', int $code = 200): self
    {
        return new self(true, $message, $data, $code);
    }

    public static function error(string $message = 'Error', int $code = 400): self
    {
        return new self(false, $message, null, $code);
    }

    public function toArray(): array
    {
        return [
            'success' => $this->success,
            'message' => $this->message,
            'data' => $this->data,
            'code' => $this->code,
        ];
    }
}

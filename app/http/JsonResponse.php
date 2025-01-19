<?php

namespace app\http;

class JsonResponse
{

    public string $status = 'success';
    public string $message = '';
    public array $error = [];
    public array $data = [];

    public function __construct(string $status, string $message = '', array $error = [])
    {
        $this->status = $status;
        $this->message = $message;
        $this->error = $error;
    }

    public function set(string $status, string $message = ''): void
    {
        $this->status = $status;
        $this->message = $message;

        if ($status == 'success') {
            $this->error = [];
        }
    }

    public function addError(string $key, string $error): void
    {
        $this->status = 'error';
        $this->message = '';
        $this->error[$key] = $error;
    }

    public function removeError(string $key): void
    {
        unset($this->error[$key]);
    }

    public function json(int $statusCode = 200): array
    {
        return [
            'status' => $this->status,
            'message' => $this->message,
            'error' => $this->error,
            'statusCode' => $this->status == 'success' ? 200 : $statusCode,
            'data' => $this->data,
        ];
    }

}
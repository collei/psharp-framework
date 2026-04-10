<?php
namespace PSharp\Http\Exceptions;

class HttpException extends Exception
{
    protected $statusCode;
    protected $headers;

    public function __construct(int $statusCode, string $message = '', Throwable $previous = null, array $headers = null, int $code = 0)
    {
        parent::__construct($message, $code, $previous);

        $this->status = $statusCode;
        $this->headers = $headers ?? [];
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
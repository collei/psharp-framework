<?php
namespace PSharp\Http\Exceptions;

class NotFoundException extends HttpException
{
    protected $statusCode;
    protected $headers;

    public function __construct(string $message = '', Throwable $previous = null, array $headers = null)
    {
        parent::__construct(404, $message, $previous, $headers);
    }
}
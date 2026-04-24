<?php
namespace PSharp\Auth\Access;

class AuthResult
{
    protected $statusCode = null;

    public function __construct(
        protected bool $allowed,
        protected string $message = '',
        protected $code = null
    ) {
        //
    }

    public function __toString()
    {
        return (string) $this->message();
    }

    public static function allow(string $message = null, $code = null)
    {
        return new static(true, $message ?? '', $code ?? 200);
    }

    public static function deny(string $message = null, $code = null)
    {
        return new static(false, $message ?? '', $code ?? 401);
    }

    public function allowed()
    {
        return $this->allowed;
    }

    public function denied()
    {
        return ! $this->allowed();
    }

    public function message()
    {
        return $this->message;
    }

    public function code()
    {
        return $this->code;
    }

    public function status()
    {
        return $this->statusCode;
    }

    public function withStatus(int $status = null)
    {
        $this->statusCode = $status;

        return $this;
    }

    public function authorize()
    {
        if ($this->denied()) {
            
        }
    }

    public function toArray()
    {
        return [
            'aloowed' => $this->allowed(),
            'message' => $this->message(),
            'code' => $this->code(),
        ];
    }
}
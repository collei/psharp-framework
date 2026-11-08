<?php
namespace PSharp\Auth\Access;

/**
 * Auth Access result.
 */
class AuthResult
{
    /**
     * @var mixed
     */
    protected $statusCode = null;

    /**
     * Init.
     * 
     * @param bool $allowed
     * @param string $message = ''
     * @param ?mixed $code = null
     */
    public function __construct(
        protected bool $allowed,
        protected string $message = '',
        protected $code = null
    ) {
        //
    }

    /**
     * Converts to string.
     * 
     * @return string
     */
    public function __toString()
    {
        return (string) $this->message();
    }

    /**
     * Returns an allowed result.
     * 
     * @param ??string $message = null
     * @param ?mixed $code = null
     * @return static
     */
    public static function allow(?string $message = null, $code = null)
    {
        return new static(true, $message ?? '', $code ?? 200);
    }

    /**
     * Returns a denied result.
     * 
     * @param ??string $message = null
     * @param ?mixed $code = null
     * @return static
     */
    public static function deny(?string $message = null, $code = null)
    {
        return new static(false, $message ?? '', $code ?? 401);
    }

    /**
     * Tells if allowed.
     * 
     * @return bool
     */
    public function allowed()
    {
        return $this->allowed;
    }

    /**
     * Tells if denied.
     * 
     * @return bool
     */
    public function denied()
    {
        return ! $this->allowed();
    }

    /**
     * Tells the result message.
     * 
     * @return string
     */
    public function message()
    {
        return $this->message;
    }

    /**
     * Tells the result code.
     * 
     * @return mixed
     */
    public function code()
    {
        return $this->code;
    }

    /**
     * Tells the result status code.
     * 
     * @return mixed
     */
    public function status()
    {
        return $this->statusCode;
    }

    /**
     * Modifies and returns itself.
     * 
     * @return $this
     */
    public function withStatus(?int $status = null)
    {
        $this->statusCode = $status;

        return $this;
    }

    /**
     * Authorize something null if denied.
     * 
     * @return never
     */
    public function authorize()
    {
        if ($this->denied()) {
            //            
        }
    }

    /**
     * Returns the result as array.
     * 
     * @return array
     */
    public function toArray()
    {
        return [
            'aloowed' => $this->allowed(),
            'message' => $this->message(),
            'code' => $this->code(),
        ];
    }
}
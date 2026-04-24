<?php
namespace PSharp\Auth;

use RuntimeException;
use PSharp\Support\Traits\Redirects;

/**
 * An user failed authentication.
 */
class AuthenticationException extends RuntimeException
{
    use Redirects;

    /**
     * @var array - Guards checked
     */
    protected $guards;

    /**
     * Creates an instance.
     * 
     * @param string $message
     * @param array $guards
     * @param string|null $redirectTo
     */
    public function __construct($message = 'Unauthenticated', array $guards = [], $redirectTo = null)
    {
        parent::__construct($message);

        $this->guards = $guards;
        $this->redirectTo = $redirectTo;
    }

    /**
     * Retrieves the checked guards.
     * 
     * @return array
     */
    public function guards()
    {
        return $this->guards;
    }
}
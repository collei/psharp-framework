<?php
namespace PSharp\Auth\Middleware;

use Closure;
use PSharp\Http\Request;
use PSharp\Http\Session;
use PSharp\Core\{Application, Middleware\MiddlewareInterface};
use PSharp\Auth\AuthManager;
use Log;

/**
 * Process user authentication on request.
 */
class AuthenticatesRequests implements MiddlewareInterface
{
    /**
     * Initializes the middleware.
     * 
     * @property PSharp\Core\Application
     * @property PSharp\Auth\AuthManager
     * @param PSharp\Core\Application $app
     * @param PSharp\Auth\AuthManager $manager
     */
    public function __construct(protected Application $app, protected AuthManager $manager) {}

    /**
     * Handles requests.
     * 
     * @param PSharp\Http\Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->authenticate($request);

        return $next($request);
    }

    /**
     * Authenticates requests.
     * Override this method to implement your auth logic.
     * 
     * @param PSharp\Http\Request $request
     * @return void
     */
    protected function authenticate(Request $request)
    {
        Log::debug('[Guard] Access granted.');
    }
}
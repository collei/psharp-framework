<?php
namespace PSharp\Core\Middleware;

use Closure;

/**
 * Common ground of all middleware.
 */
interface MiddlewareInterface
{
    /**
     * Handles requests.
     * 
     * @param PSharp\Http\Request $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next);
}
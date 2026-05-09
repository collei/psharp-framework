<?php
namespace PSharp\Http;

use Closure;
use PSharp\Core\Container;
use PSharp\Http\Exceptions\HttpException;
use PSharp\Http\Exceptions\HttpNotFoundException;
use PSharp\Core\DI\ParameterReaper;

/**
 * The application router.
 */
class Router
{
    /**
     * @var PSharp\Core\Container
     */
    private $container;

    /**
     * @var PSharp\Http\RouteMapper
     */
    private $mapper;

    /**
     * @var PSharp\Core\DI\ParameterReaper
     */
    private $valueReaper;
    
    /**
     * Initializes the application router.
     * 
     * @param PSharp\Core\Container $container
     * @param PSharp\Http\RouteMapper $mapper
     * @param PSharp\Core\DI\ParameterReaper $reaper
     */
    public function __construct(Container $container, RouteMapper $mapper, ParameterReaper $reaper)
    {
        $this->container = $container;
        $this->mapper = $mapper;
        $this->valueReaper = $reaper;
    }

    /**
     * Returns an array with all mapped endpoints.
     * 
     * @return array
     */
    public function getEndpoints()
    {
        return $this->mapper->getEndpoints();
    }

    /**
     * Dispatch the request through the application stack.
     * 
     * @param PSharp\Http\Request $request
     * @return mixed
     * @throws PSharp\Http\Exceptions\HttpNotFoundException
     */
    public function dispatch(Request $request)
    {
        if ($this->matchesEndpoint($request, $uriParameters, $endpoint)) {
            return $this->dispatchToController($endpoint, $request, $uriParameters);
        }

        throw new HttpNotFoundException();
    }

    /**
     * Tells if the given request matches any endpoint.
     * 
     * @param PSharp\Http\Request $request
     * @param array|null &$out - Returns the URI parameters, if any
     * @param PSharp\Http\Endpoint|null &$endpoint - Returns the endpoint here, if matched
     * @return bool
     */
    protected function matchesEndpoint(Request $request, array &$out = null, Endpoint &$endpoint = null)
    {
        $requestUri = $request->getUri()->getPath();
        $requestMethod = $request->getMethod();

        foreach ($this->getEndpoints() as $end) {
            if ($end->matchesUri($requestUri, $out) && $end->matchesMethod($requestMethod)) {
                $endpoint = $end;

                foreach ($out as $k => $v) {
                    $out[$k] = urldecode($v);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Tells if the given request matches any endpoint.
     * 
     * @param PSharp\Http\Endpoint $endpoint
     * @param PSharp\Http\Request $request
     * @param array $uriParameters
     * @return mixed
     * @throws PSharp\Http\Exceptions\HttpException
     */
    protected function dispatchToController(Endpoint $endpoint, Request $request, array $uriParameters)
    {
        $action = $endpoint->getParsedAction();

        if (is_null($action)) {
            throw new HttpException(500, 'Action not implemented for endpoint '.$endpoint->getPath());
        }

        $values = $uriParameters;
        
        if ($action instanceof Closure) {
            // fetches closure dependencies
            $parameters = $this->valueReaper->reapClosureParameters($action, $values);
            // calls the closure
            return $action(...$parameters);
        }

        if ($this->isControllerAction($action)) {
            //
            list($controller, $method) = $action;
            // instantiates controller
            $controller = $this->container->make($controller);
            // fetches method dependencies
            $parameters = $this->valueReaper->reapMethodParameters($controller, $method, $values);
            // calls method
            return call_user_func_array(array($controller, $method), $parameters);
        }

        throw new HttpException(500, 'Malformed action for endpoint '.$endpoint->getPath());
    }

    /**
     * Tells if the given value is a controller action.
     * 
     * @param mixed $action
     * @return bool
     */
    protected function isControllerAction($action)
    {
        return is_array($action)
                && (count($action) === 2)
                && (is_object($action[0]) || is_string($action[0]))
                && is_string($action[1]);
    }
}
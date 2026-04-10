<?php
namespace PSharp\Http;

use PSharp\Http\Exceptions\HttpException;
use PSharp\Http\Exceptions\NotFoundException;

class Router
{
    private $mapper;
    
    public function __construct(RouteMapper $mapper)
    {
        $this->mapper = $mapper;
    }

    public function getEndpoints()
    {
        return $this->mapper->getEndpoints();
    }

    public function dispatch(Request $request)
    {
        if ($this->matchesEndpoint($request, $uriParameters, $endpoint)) {
            return $this->dispatchToController($endpoint, $request, $uriParameters);
        }

        throw new NotFoundException();
    }

    protected function matchesEndpoint(Request $request, array &$out = null, Endpoint &$endpoint = null)
    {
        $requestUri = $request->getUri()->toString();
        $requestMethod = $request->getMethod();

        foreach ($this->getEndpoints() as $end) {
            if ($end->matchesMethod($requestMethod) && $end->matchesUri($requestUri, $out)) {
                $endpoint = $end;

                return true;
            }
        }

        return false;
    }

    protected function dispatchToController(Endpoint $endpoint, Request $request, array $uriParameters)
    {
        $action = $endpoint->getParsedAction();

        if (is_null($action)) {
            throw new HttpException(500, 'Action not implemented for endpoint '.$endpoint->getPath());
        }
        
        if ($action instanceof Closure) {
            // fetches closure dependencies
            // calls the closure
        }

        if (is_callable($action)) {
            $controller = $container->make($action[0]);
            // fetches method dependencies
            // calls method
        }
    }
    
}
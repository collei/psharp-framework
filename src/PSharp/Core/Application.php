<?php
namespace PSharp\Core;

use Closure;
use PSharp\Support\Pipeline;
use PSharp\Support\Str;
use PSharp\Http\{RouteMapper,Router,Request,Response};
use PSharp\Http\Factories\RequestFactory;
use PSharp\Http\Actions\ControllerBase;

final class Application
{
    private $baseDir;
    private $container;
    private $config;
    private $routeMapper;
    private $router;
    private $middleware = [];

    public function __construct(string $baseDir, string $configFile = null)
    {
        $this->baseDir = $baseDir;
        $this->config = $this->loadConfig($configFile ?? 'appsettings.json');

        $this->initialize();
    }

    public function __get(string $name)
    {
        if (in_array($name, ['container','router'])) {
            return $this->$name;
        }

        return null;
    }

    protected function initialize()
    {
        $this->container = new Container();
        $this->container->instance($this);
        $this->container->instance($this->config);

        $this->routeMapper = $this->container->make(RouteMapper::class);
        $this->router = $this->container->make(Router::class);
    }

    protected function loadConfig(string $configFile)
    {
        $configFile = $this->path($configFile);

        return new Config($configFile);
    }

    public function container()
    {
        return $this->container;
    }

    public function router()
    {
        return $this->router;
    }

    public function path(string ...$segments)
    {
        $further = empty($segments) ? null : implode(DIRECTORY_SEPARATOR, $segments);

        if ($further) {
            return preg_replace('@[\\\/]+@', DIRECTORY_SEPARATOR, $this->baseDir . DIRECTORY_SEPARATOR . $further);
        }

        return $this->baseDir;
    }

    public function config(string $name, $default = null)
    {
        return $this->config->get($name, $default);
    }

    public function mapController(string|ControllerBase $namespace = null)
    {
        $this->routeMapper->mapController($controller);

        return $this;
    }

    public function mapControllers(string $namespace = null)
    {
        $this->routeMapper->mapControllers($this->baseDir, $namespace);

        return $this;
    }

    public function useMiddleware(string ...$middleware)
    {
        foreach ($middleware as $one) {
            if (! class_exists($one, true)) {
                throw new ApplicationException("Middleware {$one} not found !");
            }

            $this->middleware[] = $this->container->make($one);
        }

        return $this;
    }

    public function use(Closure $middleware)
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    public function run()
    {
        $request = $this->captureRequest();

        $response = $this->handleRequest($request);

        echo $response;
    }

    protected function captureRequest()
    {
        // conforma a REQUEST_URI à raiz da aplicação 
        $prefix = Str::commonPrefix($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']);
        $request_uri = '/' . Str::trimPrefix($_SERVER['REQUEST_URI'], $prefix);

        return (new RequestFactory())->createServerRequest(
            $_SERVER['REQUEST_METHOD'], $request_uri, $_SERVER
        );
    }

    protected function handleRequest(Request $request)
    {
        try {
            return $this->sendThroughRouter($request);
            //
        } catch (Throwable $t) {
            echo '<div>EXCEPTION: <hr>'.print_r($t,true).'<hr></div>';
        }

        return '<div><b>algum erro ocorreu</b></div>';
    }

    protected function sendThroughRouter(Request $request)
    {
        return (new Pipeline())->send($request)
                    ->through($this->middleware)
                    ->then($this->dispatchToRouter($request));
    }

    protected function dispatchToRouter()
    {
        return function (Request $request) {
            $this->container->instance($request);

            return $this->router->dispatch($request);
        };
    }

}
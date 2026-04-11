<?php
namespace PSharp\Core;

use Closure;
use PSharp\Support\Pipeline;
use PSharp\Support\Str;
use PSharp\Http\{RouteMapper, Router, Request, Response};
use PSharp\Http\Factories\RequestFactory;
use PSharp\Http\Actions\ControllerBase;

/**
 * The application main class.
 */
final class Application
{
    /**
     * @var string
     */
    private $baseDir;

    /**
     * @var PSharp\Core\Container
     */
    private $container;

    /**
     * @var PSharp\Core\Config
     */
    private $config;

    /**
     * @var PSharp\Http\RouteMapper
     */
    private $routeMapper;

    /**
     * @var PSharp\Http\Router
     */
    private $router;

    /**
     * @var array
     */
    private $middleware = [];

    /**
     * Initializes application.
     * 
     * @param string $baseDir - must be equivalent to the __DIR__ magic constant
     *                          in the calling file located at the very app root folder.
     * @param string|null $configFile - a JSON file name, relatively located to the app root folder.
     *                          Default is 'appsettings.json' 
     */
    public function __construct(string $baseDir, string $configFile = null)
    {
        $this->baseDir = $baseDir;
        $this->config = $this->loadConfig($configFile ?? 'appsettings.json');

        $this->initialize();
    }

    /**
     * Retrieves properties.
     * 
     * @property PSharp\Core\Container container
     * @property PSharp\Http\Router router
     * @property PSharp\Http\RouteMapper mapper
     */
    public function __get(string $name)
    {
        if (in_array($name, ['container','router'])) {
            return $this->$name;
        }

        if ('mapper' == $name) {
            return $this->routeMapper;
        }

        return null;
    }

    /**
     * Initializes the internal instances.
     * 
     * @return void
     */
    protected function initialize()
    {
        $this->container = new Container();
        $this->container->instance($this);
        $this->container->instance($this->config);

        $this->routeMapper = $this->container->make(RouteMapper::class);
        $this->router = $this->container->make(Router::class);
    }

    /**
     * Loads the configuration file from the disk.
     * 
     * @param string $configFile
     * @return PSharp\Core\Config
     */
    protected function loadConfig(string $configFile)
    {
        $configFile = $this->path($configFile);

        return new Config($configFile);
    }

    /**
     * Retrieves the container instance.
     * 
     * @return PSharp\Core\Container
     */
    public function container()
    {
        return $this->container;
    }

    /**
     * Retrieves the router instance.
     * 
     * @return PSharp\Http\Router
     */
    public function router()
    {
        return $this->router;
    }

    /**
     * Crafts a file or directory path relatively to the app rot folder, using segments.
     * 
     * Similar to C# Path.Combine() method, internally uses the PHP DIRECTORY_SEPARATOR constant.
     * 
     * @param string ...$segments
     * @return string
     */
    public function path(string ...$segments)
    {
        $further = empty($segments) ? null : implode(DIRECTORY_SEPARATOR, $segments);

        if ($further) {
            return preg_replace('@[\\\/]+@', DIRECTORY_SEPARATOR, $this->baseDir . DIRECTORY_SEPARATOR . $further);
        }

        return $this->baseDir;
    }

    /**
     * Return a valeu or a set of values from the config.
     * 
     * @param string $name - use dotted name for single values or smaller portions.
     * @param mixed $default - use to give a default if value not found on config.
     * @return string|array
     */
    public function config(string $name, $default = null)
    {
        return $this->config->get($name, $default);
    }

    /**
     * Maps all declared endpoints from the given controller
     * class or instance, if any.
     * 
     * @param string|PSharp\Http\Actions\ControllerBase|null $controller
     * @return $this
     */
    public function mapController(string|ControllerBase $namespace = null)
    {
        $this->routeMapper->mapController($controller);

        return $this;
    }

    /**
     * Maps all declared endpoints from every controller declared under
     * the given namespace, if any.
     * 
     * @param string|null $namespace
     * @return $this
     */
    public function mapControllers(string $namespace = null)
    {
        $this->routeMapper->mapControllers($this->baseDir, $namespace);

        return $this;
    }

    /**
     * Adds middleware to the application middleware stack.
     * 
     * @param stirng ...$middleware
     * @return $this
     */
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

    /**
     * Adds a Closure as middleware to the application middleware stack.
     * 
     * @param Closure $middleware
     * @return $this
     */
    public function use(Closure $middleware)
    {
        $this->middleware[] = $middleware;

        return $this;
    }

    /**
     * Run the application by processing the request and serving the response.
     * 
     * @return @this
     */
    public function run()
    {
        $request = $this->captureRequest();

        $response = $this->handleRequest($request);

        echo $response;

        return $this;
    }

    /**
     * Executes the request capture process.
     * 
     * @return PSharp\Http\Request
     */
    protected function captureRequest()
    {
        // conforma a REQUEST_URI à raiz da aplicação 
        $prefix = Str::commonPrefix($_SERVER['REQUEST_URI'], $_SERVER['SCRIPT_NAME']);
        $request_uri = '/' . Str::trimPrefix($_SERVER['REQUEST_URI'], $prefix);

        return (new RequestFactory())->createServerRequest(
            $_SERVER['REQUEST_METHOD'], $request_uri, $_SERVER
        );
    }

    /**
     * Handles the request and returns the response.
     * 
     * @param PSharp\Http\Request $request
     * @return mixed
     */
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

    /**
     * Sends forth the request through the middleware stack.
     * 
     * @param PSharp\Http\Request $request
     * @return mixed
     */
    protected function sendThroughRouter(Request $request)
    {
        return (new Pipeline())->send($request)
                    ->through($this->middleware)
                    ->then($this->dispatchToRouter($request));
    }

    /**
     * Sends forth the request to the router.
     * 
     * @param PSharp\Http\Request
     * @return Closure
     */
    protected function dispatchToRouter(Request $request)
    {
        return function () use ($request) {
            $this->container->instance($request);

            return $this->router->dispatch($request);
        };
    }
}
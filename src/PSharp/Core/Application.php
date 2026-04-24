<?php
namespace PSharp\Core;

use Closure;
use InvalidArgumentException;
use PSharp\Support\{Facade, Pipeline, Str};
use PSharp\Auth\AuthenticationException;
use PSharp\Auth\Access\AuthorizationException;
use PSharp\Http\{RouteMapper, Router, Request, Response, ResponsePreparator, Sessions\Session};
use PSharp\Http\Factories\{RequestFactory, CookieFactory, CookieFactoryInterface};
use PSharp\Http\Actions\ControllerBase;
use PSharp\Core\Exceptions\ApplicationException;
use PSharp\Core\Providers\DeferrableProvider;
use PSharp\Core\Middleware\MiddlewareInterface;

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
     * @var array
     */
    private $providers = [];

    /**
     * @var bool
     */
    private $bootedProviders = false;

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
     * Terminates application.
     * 
     * @return void
     */
    public function __destruct()
    {
        Session::getInstance()->destroy();
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
     * Retrieves the Application instance from the Container.
     * 
     * @return PSharp\Core\Application
     */
    public static function getInstance()
    {
        return Container::singleton()->make(static::class);
    }

    /**
     * Initializes the internal instances.
     * 
     * @return void
     */
    protected function initialize()
    {
        Session::start();

        Facade::setApplication($this);

        $this->container = Container::singleton();
        $this->container->instance($this);
        $this->container->instance($this->config);
        $this->container->instance(Session::getInstance());

        $this->container->addInterfaceImplementor(CookieFactory::class, CookieFactoryInterface::class);
        $this->container->make(CookieFactoryInterface::class);

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
     * Return a value or a set of values from the config.
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
     * Register a service provider for later booting.
     * 
     * @param PSharp\Core\Providers\ServiceProvider
     * @return $this
     */
    public function provide(string $providerClass, string $alias = null)
    {
        if (! empty($alias)) {
            $this->container->alias($alias, $providerClass);
        }

        $provider = $this->container->make($providerClass);

        $provider->register();

        $this->providers[$providerClass] = $provider;

        return $this;
    }

    /**
     * Boots providers right before running.
     * 
     * @return void
     * @throws PSharp\Core\Exceptions\ApplicationException - when an exception is thrown in provider context.
     */
    protected function bootProviders()
    {
        $name = '[Unknown]';

        try {
            foreach ($this->providers as $class => $provider) {
                $name = $class;
                $provider->boot();
            }

            $this->bootedProviders = true;
        }
        catch (Throwable $pre) {
            throw new ApplicationException("Provider $name failed booting.", $this, $pre);
        }
    }

    /**
     * Maps the closure $action to a $path endpoint.
     * 
     * @param string $method
     * @param string $path
     * @param Closure $action
     * @param string|null $name 
     * @return $this
     */
    public function endpoint(string $method, string $path, Closure $action, string $name = null)
    {
        $this->mapper->addClosure($method, $path, $action, $name);
        
        return $this;
    }

    /**
     * Maps the closure $action to a GET $path endpoint.
     * 
     * @param string $path
     * @param Closure $action
     * @param string|null $name 
     * @return $this
     */
    public function get(string $path, Closure $action, string $name = null)
    {
        return $this->endpoint('GET', $path, $action, $name);
    }

    /**
     * Maps the closure $action to a POST $path endpoint.
     * 
     * @param string $path
     * @param Closure $action
     * @param string|null $name 
     * @return $this
     */
    public function post(string $path, Closure $action, string $name = null)
    {
        return $this->endpoint('POST', $path, $action, $name);
    }

    /**
     * Maps the closure $action to a PUT $path endpoint.
     * 
     * @param string $path
     * @param Closure $action
     * @param string|null $name 
     * @return $this
     */
    public function put(string $path, Closure $action, string $name = null)
    {
        return $this->endpoint('PUT', $path, $action, $name);
    }

    /**
     * Maps the closure $action to a PATCH $path endpoint.
     * 
     * @param string $path
     * @param Closure $action
     * @param string|null $name 
     * @return $this
     */
    public function patch(string $path, Closure $action, string $name = null)
    {
        return $this->endpoint('PATCH', $path, $action, $name);
    }

    /**
     * Maps the closure $action to a DELETE $path endpoint.
     * 
     * @param string $path
     * @param Closure $action
     * @param string|null $name 
     * @return $this
     */
    public function delete(string $path, Closure $action, string $name = null)
    {
        return $this->endpoint('DELETE', $path, $action, $name);
    }

    /**
     * Maps the closure $action to a HEAD $path endpoint.
     * 
     * @param string $path
     * @param Closure $action
     * @param string|null $name 
     * @return $this
     */
    public function head(string $path, Closure $action, string $name = null)
    {
        return $this->endpoint('HEAD', $path, $action, $name);
    }

    /**
     * Maps the closure $action to a OPTIONS $path endpoint.
     * 
     * @param string $path
     * @param Closure $action
     * @param string|null $name 
     * @return $this
     */
    public function options(string $path, Closure $action, string $name = null)
    {
        return $this->endpoint('OPTIONS', $path, $action, $name);
    }

    /**
     * Maps the closure $action to a TRACE $path endpoint.
     * 
     * @param string $path
     * @param Closure $action
     * @param string|null $name 
     * @return $this
     */
    public function trace(string $path, Closure $action, string $name = null)
    {
        return $this->endpoint('TRACE', $path, $action, $name);
    }

    /**
     * Maps the closure $action to a $path endpoint for any HTTP method.
     * 
     * @param string $path
     * @param Closure $action
     * @param string|null $name 
     * @return $this
     */
    public function any(string $path, Closure $action, string $name = null)
    {
        return $this->endpoint('*', $path, $action, $name);
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

            if (! is_a($one, MiddlewareInterface::class, true)) {
                throw new InvalidArgumentException(
                    sprintf('%s does not implement the interface %s', $one, MiddlewareInterface::class)
                );
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
        $this->prepareRun();

        $request = $this->captureRequest();

        $response = $this->handleRequest($request);

        $this->prepareResponse($request, $response)->send();

        return $this;
    }

    /**
     * Performs preparation actions before running.
     * 
     * @return void
     */
    protected function prepareRun()
    {
        $this->bootProviders();
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

    /**
     * Prepares a PSharp\Http\Response instance.
     * 
     * @param PSharp\Http\Request $request
     * @param mixed $response
     * @return PSharp\Http\Response
     */
    protected function prepareResponse(Request $request, $response)
    {
        return $this->container
                    ->make(ResponsePreparator::class)
                    ->prepare($request, $response);
    }

    /**
     * Retrieves instance info for the internal functions.
     * 
     * @return array
     */
    public function __debugInfo(): array
    {
        return [
            'baseDir' => $this->baseDir,
            'container' => get_instance_id($this->container),
            'config' => get_instance_id($this->config),
            'routeMapper' => get_instance_id($this->routeMapper),
            'router' => get_instance_id($this->router),
            'middleware' => $this->middleware,
            'providers' => $this->providers,
            'bootedProviders' => $this->bootedProviders,
        ];
    }
}
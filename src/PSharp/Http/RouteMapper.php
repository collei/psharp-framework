<?php
namespace PSharp\Http;

use ReflectionClass;
use ReflectionMethod;
use Closure;
use PSharp\Http\Methods\{HttpGet,HttpPost,HttpPut,HttpPatch,HttpDelete,HttpHead,HttpOptions,HttpTrace,HttpAny};
use PSharp\Http\Methods\Base\HttpMethodBase;
use PSharp\Http\Actions\ControllerBase;
use PSharp\Support\Str;

/**
 * The app route mapper
 */
class RouteMapper
{
	/**
	 * List of supported HTTP methods
	 */
	private const HTTP_METHODS = [
		'GET' => HttpGet::class,
		'POST' => HttpPost::class,
		'PUT' => HttpPut::class,
		'PATCH' => HttpPatch::class,
		'DELETE' => HttpDelete::class,
		'HEAD' => HttpHead::class,
		'OPTIONS' => HttpOptions::class,
		'TRACE' => HttpTrace::class,
		'*' => HttpAny::class,
	];

	/**
	 * List of crafted endpoints
	 */
	private $endpoints = [];

	/**
	 * Empty constrcutor
	 */
	public function __construct()
	{
		//
	}

	/**
	 * Retrieve all crafted endpoints.
	 * 
	 * @return array
	 */
	public function getEndpoints()
	{
		$mapper = function ($item) {
			return $item->asEndpoint();
		};

		return array_combine(
			array_keys($this->endpoints),
			array_map($mapper, array_values($this->endpoints))
		);
	}

	/**
	 * Adds a Closure to the mapper.
	 * 
	 * @param string $method
	 * @param string $path
	 * @param Closure $action
	 * @param string|null $name
	 */
	public function addClosure(string $method, string $path, Closure $action, string $name = null)
	{
		$endpoint = Endpoint::fromClosure($method, $path, $action, $name);

		$this->endpoints[$endpoint->asString()] = $endpoint;

		return $this;
	}

	/**
	 * Map all controllers detected under $namespace from the specifed $appDir,
	 * assuming the project follows psr-4 guidelines.
	 * 
	 * @param string $appDir
	 * @param string $namespace = 'App\Controllers'
	 * @return $this
	 */
	public function mapControllers(string $appDir, string $namespace = 'App\Controllers')
	{
		$path = preg_replace('#[\\/]+#', DIRECTORY_SEPARATOR, $appDir . DIRECTORY_SEPARATOR . $namespace);

		$files = array_diff(scandir($path), array('.','..'));

		pretty_dump(compact('appDir','namespace','path','files'));

		foreach ($files as $file) {
			if (is_file($path . DIRECTORY_SEPARATOR . $file)) {
				if (Str::endsWith(strtolower($file), '.php')) {
					$class = $namespace . '\\' . substr($file, 0, -4);

					$this->mapController($class);
				}
			}
		}

		return $this;
	}

	/**
	 * Map the endpoints from the specified controller.
	 * 
	 * @param string|\PSharp\Http\Actions\ControllerBase $controller
	 * @return $this
	 */
	public function mapController(string|ControllerBase $controller)
	{
		$classReflect = new ReflectionClass($controller);
		
		$this->mapControllerEndpoints($classReflect);

		return $this;
	}

	/**
	 * Map all endpoints from the controller's \ReflectionClass instance.
	 * 
	 * @param \ReflectionClass $reflect
	 * @return void
	 */
	protected function mapControllerEndpoints(ReflectionClass $reflect)
	{
		$className = $reflect->getName();
		
		foreach ($reflect->getAttributes() as $classAttr) {
			$classAttrName = $classAttr->getName();

			// Ignore other attributes
			if (Route::class != $classAttrName) {
				continue;
			}

			$route = $classAttr->newInstance();

			// If route basename is not set,
			// craft it from controller name in kebab case.
			if (empty($route->getRootName())) {
				$kebabName = Str::kebab($reflect->getShortName());

				// strip the '-controller' part if any
				if (Str::endsWith($kebabName, '-controller')) {
					$kebabName = Str::trimSuffix($kebabName, '-controller');
				}

				$route->setRootNameIfEmpty($kebabName);
			}

			// Maps endpoints from class methods
			foreach ($reflect->getMethods() as $method) {
				$this->mapControllerMethodEndpoint($route, $method, $className);
			}
		}
	}

	/**
	 * Map the endpoint from the method's ReflectionMethod instance.
	 * 
	 * @param PSharp\Http\Route $route
	 * @param ReflectionMethod $reflect
	 * @param string $className
	 * @return void
	 */
	protected function mapControllerMethodEndpoint(Route $route, ReflectionMethod $reflect, string $className)
	{
		$methodName = $reflect->getName();

		$action = "{$className}::{$methodName}";

		$isDefault = count($reflect->getAttributes(NotFound::class)) === 1;
		
		foreach ($reflect->getAttributes() as $methodAttr) {
			$methodAttrName = $methodAttr->getName();

			// Ignore non-existent HTTP methods
			if (! in_array($methodAttrName, self::HTTP_METHODS, true)) {
				continue;
			}

			$endpoint = $methodAttr->newInstance();

			if ($isDefault) {
				$endpoint->setAsDefaultWhenNotFound();
			}

			// If name is omitted, take the method name in kebab case
			if (empty($endpoint->getNameSegment())) {
				$kebabName = Str::kebab($methodName);

				$endpoint->setNameSegment($kebabName);
			}

			// If path is omitted, take the method name in kebab case
			if (empty($endpoint->getPathSegment())) {
				$kebabPath = Str::kebab($methodName);

				$endpoint->setPathSegment($kebabPath);
			}

			$endpoint->setRoute($route)->setAction($action);

			$this->endpoints[$endpoint->asString()] = $endpoint;
		}
	}
}
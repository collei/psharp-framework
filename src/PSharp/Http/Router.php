<?php
namespace PSharp\Http;

use ReflectionClass;
use ReflectionMethod;
use PSharp\Http\Methods\{HttpGet,HttpPost,HttpPut,HttpPatch,HttpDelete,HttpHead,HttpOptions,HttpTrace,HttpAny};
use PSharp\Http\Methods\Base\HttpMethodBase;
use PSharp\Http\Actions\ControllerBase;
use PSharp\Support\Str;

/**
 * The app router
 */
class Router
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
	 * Map all controllers detected under $namespace,
	 * assuming the project follows psr-4 guidelines.
	 * 
	 * @param string $namespace = 'App\Controllers'
	 * @return $this
	 */
	public function mapControllers(string $namespace = 'App\Controllers')
	{
		$path = preg_replace('#[\\/]+#', DIRECTORY_SEPARATOR, dirname(__DIR__, 3) . DIRECTORY_SEPARATOR . $namespace);

		$files = array_diff(scandir($path), array('.','..'));

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
	 * Map the specified controller.
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

			if (Route::class != $classAttrName) {
				continue;
			}

			$route = $classAttr->newInstance();

			if (empty($route->getRootName())) {
				$kebabName = Str::kebab($reflect->getShortName());

				if (Str::endsWith($kebabName, '-controller')) {
					$kebabName = Str::trimSuffix($kebabName, '-controller');
				}

				$route->setRootNameIfEmpty($kebabName);
			}

			foreach ($reflect->getMethods() as $method) {
				$this->mapControllerMethodEndpoint($route, $method, $className);
			}
		}
	}

	/**
	 * Map the endpoint from the method's \ReflectionMethod instance.
	 * 
	 * @param \ReflectionMethod $reflect
	 * @param string $className
	 * @return void
	 */
	protected function mapControllerMethodEndpoint(Route $route, ReflectionMethod $reflect, string $className)
	{
		$methodName = $reflect->getName();

		$action = "{$className}::{$methodName}";
		
		foreach ($reflect->getAttributes() as $methodAttr) {
			$methodAttrName = $methodAttr->getName();

			if (! in_array($methodAttrName, self::HTTP_METHODS, true)) {
				continue;
			}

			$endpoint = $methodAttr->newInstance();

			if (empty($endpoint->getSimpleName())) {
				$kebabName = Str::kebab($methodName);

				$endpoint->setSimpleNameIfEmpty($kebabName);
			}

			$endpoint->setRoute($route)->setAction($action);

			$this->endpoints[$endpoint->asString()] = $endpoint;
		}
	}
}
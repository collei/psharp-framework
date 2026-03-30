<?php
namespace PSharp\Http;

use ReflectionClass;
use ReflectionMethod;
use PSharp\Http\Methods\{HttpGet,HttpPost,HttpPut,HttpPatch,HttpDelete,HttpHead,HttpOptions,HttpTrace,HttpAny};
use PSharp\Http\Methods\Base\HttpMethodBase;
use PSharp\Http\Actions\ControllerBase;

/**
 * The app router
 */
class Router
{
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

	private $endpoints = [];

	public function __construct()
	{
		//
	}

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

	public function mapControllers(string $namespace)
	{
		$classReflect = new ReflectionClass($controller);
		
		$this->mapControllerEndpoints($classReflect);
	}

	public function mapController(string|ControllerBase $controller)
	{
		$classReflect = new ReflectionClass($controller);
		
		$this->mapControllerEndpoints($classReflect);
	}

	protected function mapControllerEndpoints(ReflectionClass $reflect)
	{
		$className = $reflect->getName();
		
		foreach ($reflect->getAttributes() as $classAttr) {
			$classAttrName = $classAttr->getName();

			if (Route::class != $classAttrName) {
				continue;
			}

			$route = $classAttr->newInstance();

			foreach ($reflect->getMethods() as $method) {
				$this->mapControllerMethodEndpoint($route, $method, $className);
			}
		}
	}

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

			$endpoint->setRoute($route)->setAction($action);

			$this->endpoints[$endpoint->asString()] = $endpoint;
		}
	}
}
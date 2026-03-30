<?php
namespace Psharp\Http;

use Psharp\Http\Methods\Base\HttpMethodBase;
use Psharp\Http\Actions\ControllerBase;

/**
 * The app router
 */
class Router
{
	private const HTTP_METHODS = [
		'GET' => 'HttpGet',
		'POST' => 'HttpPost',
		'PUT' => 'HttpPut',
		'PATCH' => 'HttpPatch',
		'DELETE' => 'HttpDelete',
		'HEAD' => 'HttpHead',
		'OPTIONS' => 'HttpOptions',
		'TRACE' => 'HttpTrace',
		'*' => 'HttpAny',
	];

	private $endpoints = [];

	public function __construct()
	{
		//
	}

	public function mapControllerActions(string|ControllerBase $controller)
	{
		$classReflect = new ReflectionClass($controller);
		
		$this->mapControllerEndpoints($classReflect);
	}

	protected function mapControllerEndpoints(ReflectionClass $reflect)
	{
		$className = $reflect->getName();
		
		foreach ($reflect->getAttributes() as $attribute) {
			$route = $attribute->newInstance();
			$classAttrName = $attribute->getName();
			
			if ('Route' != $classAttrName) {
				continue;
			}

			foreach ($classAttr->getMethods() as $method) {
				$this->mapControllerMethodEndpoint($method, $className);
			}
		}
	}

	protected function mapControllerMethodEndpoint(ReflectionMethod $reflect, string $className)
	{
		$methodName = $reflect->getName();

		$action = "{$className}::{$methodName}";
		
		foreach ($reflect->getAttributes() as $methodAttribute) {
			$methodAttrName = $attribute->getName();

			if (! in_array($methodAttrName, self::HTTP_METHODS, true)) {
				continue;
			}

			$endpoint = $methodAttr->newInstance();

			$endpoint->setController($route)->setAction($action);

			$this->endpoints[$endpoint->getEndpoint()] = $endpoint;
		}
	}
}
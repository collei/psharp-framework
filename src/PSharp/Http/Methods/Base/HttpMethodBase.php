<?php
namespace PSharp\Http\Methods\Base;

use PSharp\Http\Route;
use PSharp\Http\Endpoint;
use PSharp\Http\EndpointInterface;
use PSharp\Http\Methods\HttpDelete;
use PSharp\Http\Methods\HttpGet;
use PSharp\Http\Methods\HttpHead;
use PSharp\Http\Methods\HttpOptions;
use PSharp\Http\Methods\HttpPatch;
use PSharp\Http\Methods\HttpPost;
use PSharp\Http\Methods\HttpPut;
use PSharp\Http\Methods\HttpTrace;

/**
 * Base class for route endpoints
 */
abstract class HttpMethodBase implements EndpointInterface
{
	private $path = null;
	private $name = null;
	private $route = null;
	private $action = null;
	private $method = null;

	/**
	 * Constructor.
	 * 
	 * @param string $path = "/"
	 * @param string $name = null
	 */
	public function __construct(string $path = null, string $name = null)
	{
		$this->path = $path ?? '';
		$this->name = $name;
		$this->method = $this->catterMethod();
	}

	/**
	 * Catter the HTTP method from the object instance.
	 * 
	 * @return string
	 */
	private function catterMethod()
	{
		if ($this instanceof HttpDelete) return 'DELETE';
		if ($this instanceof HttpGet) return 'GET';
		if ($this instanceof HttpHead) return 'HEAD';
		if ($this instanceof HttpOptions) return 'OPTIONS';
		if ($this instanceof HttpPatch) return 'PATCH';
		if ($this instanceof HttpPost) return 'POST';
		if ($this instanceof HttpPut) return 'PUT';
		if ($this instanceof HttpTrace) return 'TRACE';

		return '*';
	}

	/**
	 * Define the controller route for this endpoint.
	 * 
	 * @param Route $route
	 * @return $this
	 */
	public function setRoute(Route $route)
	{
		$this->route = $route;
		return $this;
	}

	/**
	 * Define the action for this endpoint.
	 * 
	 * @param string $action
	 * @return void
	 */
	public function setAction(string $action)
	{
		$this->action = $action;
		return $this;
	}

	/**
	 * Obtains the controller route from this endpoint.
	 * 
	 * @return Route|null
	 */
	public function getRoute()
	{
		return $this->route;
	}

	/**
	 * Obtains the action from this endpoint.
	 * 
	 * @return string|null
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * Return the full path of this endpoint.
	 * 
	 * @return string
	 */
	public function getPath()
	{
		return ($this->route)
			? str_replace('//','/',($this->route->getRootPath() . ($this->path ? ('/' . $this->path) : '')))
			: $this->path;
	}

	/**
	 * Return the full name of this endpoint.
	 * 
	 * @return string
	 */
	public function getName()
	{
		return ($this->route)
			? trim(str_replace(['...','..'],'.',($this->route->getRootName() . '.' . $this->name)), ' .')
			: $this->name;
	}

	/**
	 * Return the simple name of this endpoint.
	 * 
	 * @return string
	 */
	public function getSimpleName()
	{
		return $this->name;
	}

	/**
	 * Set the simple name only if empty.
	 * 
	 * @param string $name
	 * @return this
	 */
	public function setSimpleNameIfEmpty(string $name)
    {
		if (empty($this->name)) {
			$this->name = $name;
		}

		return $this;
	}

	/**
	 * Return the HTTP method of this endpoint.
	 * 
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * Return the method and full path of this endpoint.
	 * 
	 * @return string
	 */
	public function asString()
	{
		return ($this->getMethod() ?? '*') . ' ' . $this->getPath();
	}

	/**
	 * Set the HTTP method of this endpoint.
	 * 
	 * @return string
	 */
	protected function setMethod(string $method)
	{
		$this->method = strtoupper($method);
		return $this;
	}

	/**
	 * Return the object content as endpoint.
	 * 
	 * @return \PSharp\Http\Endpoint
	 */
	public function asEndpoint()
	{
		return (new Endpoint($this->route, $this->path, $this->name))
					->setAction($this->action)
					->setMethod($this->method);
	}
	
	/**
	 * Return the full endpoint of the endpoint.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->asString();
	}
}
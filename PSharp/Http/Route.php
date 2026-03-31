<?php
namespace PSharp\Http;

use PSharp\Http\Methods\Base\HttpMethodBase;
use Attribute;

/**
 * Base class for route endpoints
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Route
{
	private $rootPath = null;
	private $rootName = null;
	private $controller = null;

	/**
	 * Constructor.
	 * 
	 * @param string $rootPath = "/"
	 * @param string $rootName = null
	 */
	public function __construct(string $rootPath = "/", string $rootName = null)
	{
		$this->rootPath = $rootPath;
		$this->rootName = $rootName;
	}

	/**
	 * Define the action for this endpoint.
	 * 
	 * @param Route $controller
	 * @return void
	 */
	public function setController(ControllerBase $controller)
	{
		$this->controller = $controller;
	}

	/**
	 * Obtains the action from this endpoint.
	 * 
	 * @return \PSharp\Http\ControllerBase|null
	 */
	public function getController()
	{
		return $this->controller;
	}

	/**
	 * Return the root path of this endpoint.
	 * 
	 * @return string
	 */
	public function getRootPath()
	{
		return $this->rootPath;
	}

	/**
	 * Return the root name of this endpoint.
	 * 
	 * @return string|null
	 */
	public function getRootName()
	{
		return $this->rootName;
	}

	/**
	 * Set the root name only if empty.
	 * 
	 * @param string $rootName
	 * @return this
	 */
	public function setRootNameIfEmpty(string $rootName)
	{
		if (empty($this->rootName)) {
			$this->rootName = $rootName;
		}

		return $this;
	}
	
	/**
	 * Return the root path of the endpoint.
	 * 
	 * @return string
	 */
	public function __toString()
	{
		return $this->getRootPath();
	}
}
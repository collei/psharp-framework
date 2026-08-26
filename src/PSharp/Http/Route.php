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

	/**
	 * Constructor.
	 * 
	 * @param string $rootPath = null
	 * @param string $rootName = null
	 */
	public function __construct(?string $rootPath = null, ?string $rootName = null)
	{
		$this->rootPath = $rootPath;
		$this->rootName = $rootName;
	}

	/**
	 * Tells if the root path is empty.
	 * 
	 * @return bool
	 */
	public function isRootPathEmpty()
	{
		return empty($this->rootPath);
	}

	/**
	 * Tells if the root name is empty.
	 * 
	 * @return bool
	 */
	public function isRootNameEmpty()
	{
		return empty($this->rootName);
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
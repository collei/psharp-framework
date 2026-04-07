<?php
namespace PSharp\Support\Traits;

/**
 * Provides attribute features.
 * 
 */
trait HasAttributes
{
	/**
	 * @var array
	 */
	private $attributes = [];

	/**
	 * Check if a given attribute was set.
	 * 
	 * @param string $name
	 * @return bool
	 */
	public function hasAttribute(string $name)
	{
		return array_key_exists($name, $this->attributes);
	}

	/**
	 * Obtain this attribute if set.
	 * 
	 * @param string $name
	 * @param mixed $default = null
	 * @return mixed
	 */
	public function attribute(string $name, $default = null)
	{
		return $this->getAttribute($name, $default);
	}

	/**
	 * Obtains all attributes.
	 * 
	 * @return array
	 */
	public function attributes()
	{
		return $this->attributes ?: [];
	}

	/**
	 * Obtain this attribute if set.
	 * 
	 * @param string $name
	 * @param mixed $default = null
	 * @return mixed
	 */
	public function getAttribute(string $name, $default = null)
	{
		if ($this->hasAttribute($name)) {
			return $this->attributes[$name];
		}

		return $default;
	}

	/**
	 * Sets this attribute.
	 * 
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function setAttribute(string $name, $value)
	{
		$this->attributes[$name] = $value;

		return $this;
	}

	/**
	 * Sets all attributes.
	 * 
	 * @param array $attributes
	 * @return $this
	 */
	public function setAttributes(array $attributes)
	{
		$this->attributes = $attributes;

		return $this;
	}
}
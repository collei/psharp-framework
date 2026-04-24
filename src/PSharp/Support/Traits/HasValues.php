<?php
namespace PSharp\Support\Traits;

trait HasValues
{
	/**
	 * @var array
	 */
	private $values = [];

	/**
	 * Gets the given value.
	 *
	 * @param string $name
	 * @param mixed $default
	 * @return mixed
	 */
	public function getValue(string $name, $default = null)
	{
		return $this->values[$name] ?? $default;
	}

	/**
	 * Sets the given value.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function setValue(string $name, $value = null)
	{
		$this->values[$name] = $value;
		//
		return $this;
	}

	/**
	 * Adds a bunch of values to the custom values.
	 *
	 * @param array $values
	 * @return $this
	 */
	public function addValues(array $values)
	{
		$this->values = array_merge($this->values, $values);
		//
		return $this;
	}

	/**
	 * Removes the given name and value.
	 *
	 * @param string $name
	 * @return $this
	 */
	public function forgetValue(string $name)
	{
		unset($this->values[$name]);
		//
		return $this;
	}

	/**
	 * Returns all current values.
	 *
	 * @return array
	 */
	public function getValues()
	{
		return $this->values;
	}
}
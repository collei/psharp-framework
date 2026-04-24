<?php
namespace PSharp\Support;

/**
 * Emulates an Optional object instance value.
 *
 * @author alarido <alarido.su@gmail.com>
 */
class Optional
{
	/**
	 * @var mixed $value
	 */
	private $value;

	/**
	 * Creates a new Optional
	 *
	 * @param mixed $value
	 */
	public function __construct($value = null)
	{
		$this->value = $value;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get(string $name)
	{
		if (is_object($this->value) && property_exists($this->value, $name)) {
			return $this->value->$name;
		}

		return null;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set(string $name, $value)
	{
		if (is_object($this->value) && property_exists($this->value, $name)) {
			$this->value->$name = $value;
		}

		return null;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __call(string $name, array $arguments)
	{
		if (is_object($this->value) && method_exists($this->value, $name)) {
			return call_user_func_array([$this->value, $name], $arguments);
		}

		return null;
	}

	/**
	 * For PHP internal use
	 *
	 * @return array
	 */
	public function __debugInfo()
	{
		return [
			'value' => $this->value,
			'isPresent' => $this->isPresent(),
		];
	}

	/**
	 * Tells if is there any value.
	 *
	 * @return bool
	 */
	public function isPresent()
	{
		return isset($this->value) && !is_null($this->value);
	}

	/**
	 * Returns the value.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Returns true if the given value is same as the inner value.
	 *
	 * @return bool
	 */
	public function equals($anotherObject)
	{
		if ($anotherObject instanceof static) {
			return $this->value == $anotherObject->value;
		}

		if (is_a($anotherObject, get_class($this->value))) {
			return $anotherObject == $this->value;
		}

		return false;
	}

	/**
	 * Returns true if the Optional contains an instance of the given class name.
	 *
	 * @return bool
	 */
	public function is($className)
	{
		return is_object($this->value) && is_a($this->value, $className);
	}

	/**
	 * Creates a new Optional for the value.
	 *
	 * @static
	 * @param mixed $value
	 * @return \Zelatus\Support\Optionals\Optional
	 */
	public static function for($value)
	{
		return new self($value);
	}
}
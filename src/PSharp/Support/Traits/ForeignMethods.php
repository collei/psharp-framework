<?php
namespace PSharp\Support\Traits;

use Closure;
use BadMethodCallException;

/**
 * This trait allows classes to insert 'foreign methods', making possible
 * to do call chaining them among the own class methods. Useful for logging
 * inside a chained call without the need of breaking lines, and so on.
 *
 * Consider this example:
 *
 *		$person->setName('Tom')->setAge(36)->setSalary(1500)->save();
 *
 * So, this can be done...
 *
 *		$person->setName('Tom')->dump()->setAge(36)->dump()->setSalary(1500)->save();
 *
 * ...instead of this
 *
 *		$person->setName('Tom');
 *		dump($person);
 *		$person->setAge(36);
 *		dump($person);
 *		$person->setName(1500);
 *		$person->save();
 */
trait ForeignMethods
{
	/**
	 * @var \Closure[]
	 */
	private static $foreignMethodList = [];

	/**
	 * Adds a Closure and names it $method to be called as method.
	 *
	 * @param	string		$method
	 * @param	\Closure	$code
	 * @param	bool		$enabled = true
	 * @param	bool		$returnable = false
	 * @return	void
	 */
	private function configureMethod(
		string $method, Closure $code, bool $enabled = true, bool $returnable = false
	) {
		$returned = null;

		static::$foreignMethodList[$method] = compact(
			'code','enabled','returnable','returned'
		);
	}

	/**
	 * Enables or disables a foreign method.
	 *
	 * @param	string		$method
	 * @param	bool		$enabled
	 * @return	void
	 */
	private function setMethodEnabled(string $method, bool $enabled)
	{
		if (static::$foreignMethodList[$method]) {
			static::$foreignMethodList[$method]['enabled'] = $enabled;
		}
	}

	/**
	 * Returns whether the foreign method is enabled.
	 *
	 * @param	string		$method
	 * @return	bool
	 */
	private function isMethodEnabled(string $method)
	{
		return static::$foreignMethodList[$method]['enabled'] ?? false;
	}

	/**
	 * Enables or disables the direct return of values from a foreign method.
	 *
	 * With $returnable = true, the called method will be allowed to return
	 * its own value directly to the caller.
	 * With $returnable = false, the return value is stored for later retrieval
	 * and the __call() returns $this for method chaining.
	 *
	 * @param	string		$method
	 * @param	bool		$returnable
	 * @return	void
	 */
	private function setMethodReturnable(string $method, bool $returnable)
	{
		if (static::$foreignMethodList[$method]) {
			static::$foreignMethodList[$method]['returnable'] = $returnable;
		}
	}

	/**
	 * Retrieves the stored value of the last return of the given $method
	 *
	 * @param	string		$method
	 * @param	bool		$returnable
	 * @return	mixed
	 */
	private function getMethodLastReturn(string $method)
	{
		if (static::$foreignMethodList[$method]) {
			return static::$foreignMethodList[$method]['returned'];
		}

		return;
	}

	/**
	 * Calls the foreign method if it exists and is enabled. 
	 *
	 * When the method $name has direct return enabled, returns its own value.
	 * Otherwise, returns $this for chaining of the hosting class and stores
	 * the returned value for later retrieval. 
	 *
	 * @param	object		$anything
	 * @param	\Closure	$func = null
	 * @return	mixed
	 */
	public function __call(string $name, array $parameters)
	{
		if (($method = static::$foreignMethodList[$name] ?? null) && ($method['enabled'] ?? false)) {
			$code = $method['code'];

			if ($method['returnable'] ?? false) {
				return $code->call($this, ...$parameters);
			}

			$method['returned'] = $code->call($this, ...$parameters);

			return $this;
		}

		throw new BadMethodCallException(sprintf(
			'Method %s does not exist in the class %s', $name, get_class($this)
		));
	}
}
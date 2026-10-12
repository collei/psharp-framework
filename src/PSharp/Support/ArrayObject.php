<?php
namespace Zelatus\Support;

use ArrayObject as BaseArrayObject;
use JsonSerializable;
use PSharp\Support\Interfaces\Arrayable;
use Collei\Collections\Collection;

/**
 * Array as a class instance.
 *
 * Adapted from Laravel's Eloquent's Illuminate\Database\Eloquent\Casts\ArrayObject
 *
 */
class ArrayObject extends BaseArrayObject implements Arrayable, JsonSerializable
{
	/**
	 * Get a collection containing the underlying array.
	 *
	 * @return \Zelatus\Collections\Collection
	 */
	public function collect()
	{
		return Collection::for($this->getArrayCopy());
	}

	/**
	 * Get the instance as an array.
	 *
	 * @return array
	 */
	public function toArray()
	{
		return $this->getArrayCopy();
	}

	/**
	 * Get the array that should be JSON serialized.
	 *
	 * @return array
	 */
	public function jsonSerialize()
	{
		return $this->getArrayCopy();
	}
}
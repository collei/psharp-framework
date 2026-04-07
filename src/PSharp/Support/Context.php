<?php
namespace PSharp\Support;

use PSharp\Support\Interfaces;

/**
 * Object context to work with.
 */
class Context implements Arrayable
{
    /**
     * Repository of dynamic properties.
     */
    private $assets = [];

    /**
     * Initialize context with objects and values.
     * 
     * @param array|null $assets
     */
    public function __construct(array $assets = null)
    {
        $this->assets = $assets ?? [];
    }

    /**
     * Retrieves a value.
     * 
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        return $this->assets[$name] ?? null;
    }

    /**
     * Adds or changes a value.
     * 
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    public function __set(string $name, $value)
    {
        $this->assets[$name] = $value;
    }

    /**
     * Tells if a value named $name exists.
     * 
     * @param string $name
     * @return bool
     */
    public function __isset(string $name)
    {
        return array_key_exists($name, $this->assets);
    }

    /**
     * Removes a value by $name.
     * 
     * @param string $name
     * @return void
     */
    public function __unset(string $name)
    {
        unset($this->assets[$name]);
    }

    /**
     * Returns the object as array.
     * 
     * @return array
     */
    public function toArray(): array
    {
        return $this->assets;
    }
}
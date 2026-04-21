<?php
namespace PSharp\Core\Interfaces;

use Closure;

/**
 * Container of all instances shared across the application
 */
interface Container
{
    /**
     * Returns the corresponding instance, crafting it if not yet found.
     * 
     * @param mixed $class
     * @return object|null
     * @throws Psr\Container\ContainerExceptionInterface when class not found.
     */
    public function make($class);

    /**
     * Creates an alias for a given class.
     * 
     * @param string $alias
     * @param string|object $class
     * @return void
     */
    public function alias(string $alias, $class);

    /**
     * Check if $class is an alias and also exists.
     * 
     * @param string|object $class
     * @return bool
     */
    public function isAlias($class);

    /**
     * Check if such alias exists.
     * 
     * @param string $class
     * @return bool
     */
    public function hasAlias(string $class);

    /**
     * Set the builder closure for the given class
     * 
     * @param string $class
     * @param Closure $builder
     * @return void
     */
    public function configureBuilder(string $class, Closure $builder);

    /**
     * Adds an instance for the given class to the list.
     * 
     * @param object|null $instance
     * @return object|null
     */
    public function instance($instance);

    /**
     * Remove the resolved instance for this class.
     * 
     * @param string $class
     * @return void
     */
    public function forgetInstance(string $class);

    /**
     * Remove all resolved instances.
     * 
     * @return void
     */
    public function forgetInstances();

    /**
     * Adds interfaces implemented by the given class.
     * 
     * @param string $class
     * @param string ...$interfaces
     * @return void
     */
    public function addInterfaceImplementor(string $class, string ...$interfaces);

    /**
     * Returns the class implementing (if any) for the given interface.
     * 
     * @param string $interface
     * @return string|null
     */
    public function getInterfaceImplementor(string $interface);
}
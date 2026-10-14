<?php
namespace PSharp\Support;

use RuntimeException;
use PSharp\Core\{Application, Container};

/**
 * The root of all facades, the one itself incarnate.
 */
abstract class Facade
{
    /**
     * @var PSharp\Core\Application
     */
    protected static $app;

    /**
     * @var array
     */
    protected static $facadeInstances = [];

    /**
     * @var array
     */
    protected static $AVAILABLE = [];

    /**
     * Retrieves the application container instance.
     * 
     * @return PSharp\Core\Container
     */
    public static function getApplication()
    {
        return static::$app;
    }
    
    /**
     * Defines the application instance.
     * 
     * @param PSharp\Core\Application $application
     * @return void
     */
    public static function setApplication(Application $application)
    {
        static::$app = $application;
    }

    /**
     * Resolves the given instance.
     * 
     * @param string|object $instance
     * @return object
     */
    protected static function resolveInstance($instance)
    {
        if (is_object($instance)) {
            return $instance;
        }

        if (isset(static::$facadeInstances[$instance])) {
            return static::$facadeInstances[$instance];
        }

        $app = static::getApplication() ?? Application::getInstance();

        return static::$facadeInstances[$instance] = $app->container()->make($instance);
    }

    /**
     * Remove the resolved instance reference.
     * 
     * @param string $class
     * @return void
     */
    public static function clearResolvedInstance(string $class)
    {
        unset(static::$facadeInstances[$class]);
    }

    /**
     * Remove all resolved instance references.
     * 
     * @return void
     */
    public static function clearResolvedInstances()
    {
        static::$facadeInstances = [];
    }

    /**
     * Retrieves the root instance of the facade.
     * 
     * @return object
     */
    protected static function root()
    {
        return static::resolveInstance(static::getAcessor());
    }
    
    /**
     * Retrieves the available methods.
     * 
     * @return array
     */
    protected static function available()
    {
        return static::$AVAILABLE;
    }

    /**
     * Adds methods to the internal $AVAILABLE array.
     * 
     * @return void
     */
    public static function addMethod(string $name, ?string $alias = null)
    {
        if (! empty($alias)) {
            static::$AVAILABLE[$name] = $alias;
        }

        static::$AVAILABLE = $name;
    } 

    /**
     * Retrieves the facade acessor.
     * 
     * @return string
     */
    protected static function getAcessor()
    {
        throw new RuntimeException('Facade not implemented the getAcessor() method.');
    }

    /**
     * Redirects calls to the underlying instance.
     * 
     * @param string $method
     * @param array $args
     * @return mixed
     * @throws RuntimeException when method not available or facade root not set.
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::root();
        
        if (empty($instance)) {
            throw new RuntimeException('Facade root not set.');
        }

        $alias = $method;
        
        $available = static::available();

        if (array_key_exists($method)) {
            $method = $available[$method];
        } elseif (! in_array($method, $available)) {
            throw new RuntimeException(sprintf('Method %s not available for this Facade.', $method));
        }

        if (method_exists($instance, $method)) {
            return $instance->$method(...$args);
        }

        throw new RuntimeException(sprintf('Method alias %s not available for this Facade.', $alias));
    }
}
<?php
namespace PSharp\Core;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionMethod;
use ReflectionFunction;
use ReflectionType;
use ReflectionNamedType;
use ReflectionUnionType;
use ReflectionParameter;
use ReflectionException;
use PSharp\Core\DI\ParameterReaper;
use PSharp\Core\DI\ContainerException;
use PSharp\Core\DI\NotFoundException;

/**
 * Container of all instances shared across the application
 */
final class Container
{
    /**
     * Repository of living object instances.
     * 
     * @var array
     */
    private $instances = [];

    /**
     * Repository of builder closures.
     * 
     * @var array
     */
    private $builders = [];

    /**
     * Repository of class interfaces implemented by each class.
     * 
     * @var array
     */
    private $interfaces = [];

    /**
     * Repository of class interfaces implemented by each class.
     * 
     * @var PSharp\Core\DI\ParameterReaper
     */
    private $parameterReaper;

    /**
     * List of primitive default values.
     * 
     * @var array
     */
    private const PRIMITIVE_DEFAULTS = [
        'bool' => false,
        'int' => 0,
        'float' => 0.0,
        'string' => '',
        'array' => [],
        'object' => null,
        'null' => null,
        'mixed' => null,
    ];

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->parameterReaper = new ParameterReaper($this);

        $this->setInstance(static::class, $this);
        $this->setInstance(ParameterReaper::class, $this->parameterReaper);
    }

    /**
     * Tells if the type name is a primitive.
     * 
     * @param string|null $name
     * @return bool
     */
    public function isPrimitive(?string $name)
    {
        return array_key_exists($name, self::PRIMITIVE_DEFAULTS);
    }

    /**
     * Returns the default value for the given primitive type.
     * 
     * @param string $type
     * @return mixed
     */
    public function getPrimitiveDefault(string $name)
    {
        if ('object' == $name) {
            return new stdClass;
        }

        return self::PRIMITIVE_DEFAULTS[$name] ?? null;
    }

    /**
     * Returns the corresponding instance, crafting it if not yet found.
     * 
     * @param mixed $class
     * @return object|null
     * @throws Psr\Container\ContainerExceptionInterface when class not found.
     */
    public function make($class)
    {
        if (empty($class)) {
            return null;
        }

        if ($this->hasInstance($class)) {
            return $this->getInstance($class);
        }

        if ($builder = $this->getBuilder($class)) {
            $instance = $this->buildUsing($builder);

            return $this->setInstance($class, $instance);
        }

        if (class_exists($class)) {
            $instance = $this->build($class);

            return $this->setInstance($class, $instance);
        }

        throw new ContainerException("Class $class not found !");
    }

    /**
     * Set the builder closure for the given class
     * 
     * @param string $class
     * @param Closure $builder
     * @return void
     */
    public function configureBuilder(string $class, Closure $builder)
    {
        $this->builders[$class] = $builder;
    }

    /**
     * Get the builder closure (if any) for the given class
     * 
     * @param string $class
     * @return Closure|null
     */
    protected function getBuilder(string $class)
    {
        if (array_key_exists($class, $this->builders)) {
            return $this->builders[$class];
        }

        return null;
    }

    /**
     * Tells if it holds an instance for the given class.
     * 
     * @param string $class
     * @return bool
     */
    protected function hasInstance(string $class)
    {
        return array_key_exists($class, $this->instances);
    }

    /**
     * Adds an instance for the given class to the list.
     * 
     * @param string $class
     * @param object|null $instance
     * @return object|null
     */
    protected function setInstance(string $class, $instance)
    {
        $this->addInterfaceImplementors($class);

        return $this->instances[$class] = $instance;
    }

    /**
     * Adds an instance for the given class to the list.
     * 
     * @param object|null $instance
     * @return object|null
     */
    public function instance($instance)
    {
        $class = get_class($instance);
        
        return $this->setInstance($class, $instance);
    }

    /**
     * Returns the instance for the given class, if any.
     * 
     * @param string $class
     * @return object|null
     */
    protected function getInstance(string $class)
    {
        return $this->instances[$class] ?? null;
    }

    /**
     * Remove the resolved instance for this class.
     * 
     * @param string $class
     * @return void
     */
    public function forgetInstance(string $class)
    {
        unset($this->instances[$class]);
    }

    /**
     * Remove all resolved instances.
     * 
     * @return void
     */
    public function forgetInstances()
    {
        $this->instances = [];
    }

    /**
     * Adds interfaces implemented by the given class.
     * 
     * @param string $class
     * @param string ...$interfaces
     * @return void
     */
    public function addInterfaceImplementor(string $class, string ...$interfaces)
    {
        if (! array_key_exists($class, $this->interfaces)) {
            $this->interfaces[$class] = $interfaces;

            return;
        }

        $this->interfaces[$class] = array_merge($this->interfaces[$class], $interfaces);
    }

    /**
     * Adds interfaces implemented by the given class.
     * 
     * @param string|object $class
     * @return void
     */
    public function addInterfaceImplementors($class)
    {
        $interfaces = class_implements($class);

        if (is_object($class)) {
            $class = get_class($class);
        } elseif (! is_string($class)) {
            return;
        }
        
        if ($interfaces) {
            if (! array_key_exists($class, $this->interfaces)) {
                $this->interfaces[$class] = $interfaces;
            } else {
                $this->interfaces[$class] = array_merge($this->interfaces[$class], $interfaces);
            }
        }
    }

    /**
     * Returns the class implementing (if any) for the given interface.
     * 
     * @param string $interface
     * @return string|null
     */
    public function getInterfaceImplementor(string $interface)
    {
        foreach ($this->interfaces as $class => $interfaces) {
            if (array_key_exists($interface, $interfaces)) {
                return $class;
            }
        }

        return null;
    }

    /**
     * Instantiates the given class, resolving the constructor's dependencies.
     * 
     * @param string|Closure $concrete
     * @return object
     */
    protected function build($concrete)
    {
        if (is_callable($concrete)) {
            return $this->buildUsing($concrete);
        }

        if (! class_exists($concrete)) {
            if (! interface_exists($concrete)) {
                throw new ContainerException("There is no Class or Interface named '$concrete'.");
            }

            $concrete = $this->getInterfaceImplementor($concrete);
        }

        if (! class_exists($concrete)) {
            throw new ContainerException("There is no Class named '$concrete'.");
        }

        $reflector = new ReflectionClass($concrete);

        if (! $reflector->isInstantiable()) {
            throw new ContainerException("Class '$concrete' is not instantiable");
        }

        if ($reflConstructor = $reflector->getConstructor()) {
            if ($reflConstructor->getNumberOfParameters() == 0) {
                return $reflector->newInstance();
            }

            $parameters = $this->parameterReaper->reapFromMethodReflector($reflConstructor);

            return $reflector->newInstanceArgs((array) $parameters);
        }

        return $reflector->newInstance();
    }

    /**
     * Instantiates a class by running the given closure, resolving its dependencies.
     * 
     * @param Closure $concrete
     * @return object
     */
    protected function buildUsing(Closure $concrete)
    {
        $reflFunction = new ReflectionFunction($concrete);
        $reflParams = $reflFunction->getParameters();

        if (empty($reflParams)) {
            return $concrete();
        }

        $parameters = $this->parameterReaper->reapValues($reflParams);

        $args = (array) $parameters;

        return $concrete(...$args);
    }

    public function __debugInfo()
    {
        $instance_as_class = function($inst) {
            return $inst ? get_class($inst) : null;
        };

        $instances = array_map($instance_as_class, $this->instances);
        $builders = array_map($instance_as_class, $this->builders);
        $interfaces = $this->interfaces;

        return compact('instances','builders','interfaces');
    }
}
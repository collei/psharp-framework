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

final class Container
{
    /**
     * Repository of living object instances.
     */
    private $instances = [];

    /**
     * Repository of builder closures.
     */
    private $builders = [];

    /**
     * List of primitive default values.
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

    public function isPrimitive(?string $name)
    {
        return array_key_exists($name, self::PRIMITIVE_DEFAULTS);
    }

    public function getPrimitiveDefault(string $name)
    {
        if ('object' == $name) {
            return new stdClass;
        }

        return self::PRIMITIVE_DEFAULTS[$name] ?? null;
    }

    public function make($class)
    {
        if (empty($class)) {
            return null;
        }

        if ($instance = $this->getInstance($class)) {
            return $instance;
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

    public function configureBuilder(string $class, Closure $builder)
    {
        $this->builders[$class] = $builder;
    }

    protected function getBuilder(string $class)
    {
        if (array_key_exists($class, $this->builders)) {
            return $this->builders[$class];
        }

        return null;
    }

    protected function setInstance(string $class, $instance)
    {
        return $this->instances[$class] = $instance;
    }

    protected function getInstance(string $class)
    {
        if (array_key_exists($class, $this->instances)) {
            return $this->instances[$class];
        }

        return null;
    }

    protected function build($concrete)
    {
        if (is_callable($concrete)) {
            return $this->buildUsing($concrete);
        }

        if (! class_exists($concrete)) {
            throw new ContainerException("Class '$concrete' does not exist");
        }

        $reflector = new ReflectionClass($concrete);

        if (! $reflector->isInstantiable()) {
            throw new ContainerException("Class '$concrete' is not instantiable");
        }

        if ($reflConstructor = $reflector->getConstructor()) {
            if ($reflConstructor->getNumberOfParameters() == 0) {
                return $reflector->newInstance();
            }

            $parameters = ParameterReaper::reapFromMethodReflector($reflConstructor, $this);

            return $reflector->newInstanceArgs((array) $parameters);
        }

        return $reflector->newInstance();
    }

    protected function buildUsing(Closure $concrete)
    {
        $reflFunction = new ReflectionFunction($concrete);
        $reflParams = $reflFunction->getParameters();

        if (empty($reflParams)) {
            return $concrete();
        }

        $parameters = ParameterReaper::reapValues($reflParams, $this);

        $args = (array) $parameters;

        return $concrete(...$args);
    }
}
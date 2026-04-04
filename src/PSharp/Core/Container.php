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

final class Container
{
    private $instances = [];
    private $builders = [];

    public function make($class)
    {
        if ($instance = $this->getInstance($class)) {
            return $instance;
        }

        if ($builder = $this->getBuilder($class)) {
            $instance = $this->buildUsing($builder);

            $this->setInstance($class, $instance);

            return $instance;
        }

        if (class_exists($class)) {
            $instance = $this->build($class);

            $this->setInstance($class, $instance);

            return $instance;
        }

        throw new Exception("Class $class not found !");
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
        $this->instances[$class] = $instance;
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
            throw new \Exception("Class '$concrete' does not exist");
        }

        $reflClass = new ReflectionClass($concrete);

        if ($reflConstructor = $reflClass->getConstructor()) {
            $reflParams = $reflConstructor->getParameters();

            if (empty($reflParams)) {
                return $reflClass->newInstance();
            }

            $parameterTypes = [];
            $parameters = [];

            foreach ($reflParams as $reflPar) {
                $name = $reflPar->getName();
                $type = $reflPar->getType();

                if ($type instanceof ReflectionNamedType) {
                    $type = $type->getName();
                } elseif ($type instanceof ReflectionUnionType) {
                    $types = $type->getTypes();
                    $type = $types[0];
                    $type = ($type instanceof ReflectionNamedType) ? $type->getName() : null;
                } else {
                    $type = null;
                }

                $parameterTypes[$name] = $type;
            }

            foreach ($parameterTypes as $name => $type) {
                $parameters[$name] = $this->make($type);
            }

            return $reflClass->newInstanceArgs((array) $parameters);
        }

        return $reflClass->newInstance();
    }

    protected function buildUsing(Closure $concrete)
    {
        $reflFunction = new ReflectionFunction($concrete);
        $reflParams = $reflFunction->getParameters();

        if (empty($reflParams)) {
            return $concrete();
        }

        $parameterTypes = [];
        $parameters = [];

        foreach ($reflParams as $reflPar) {
            $name = $reflPar->getName();
            $type = $reflPar->getType();

            if ($type instanceof ReflectionNamedType) {
                $type = $type->getName();
            } elseif ($type instanceof ReflectionUnionType) {
                $types = $type->getTypes();
                $type = $types[0];
                $type = ($type instanceof ReflectionNamedType) ? $type->getName() : null;
            } else {
                $type = null;
            }

            $parameterTypes[$name] = $type;
        }

        foreach ($parameterTypes as $name => $type) {
            $parameters[$name] = $this->make($type);
        }

        $args = (array) $parameters;

        return $concrete(...$args);
    }
}
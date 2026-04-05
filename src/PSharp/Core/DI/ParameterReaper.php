<?php
namespace PSharp\Core\DI;

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
use PSharp\Core\Container;

/**
 * Reaps values for parameters, among container instances,
 * default values, and explicitly provided value list.
 */
class ParameterReaper
{
    /**
     * Reaps values for a given method of a living instance.
     * 
     * @param object $instance
     * @param string $method
     * @param PSharp\Core\Container $container
     * @param array $values = []
     * @return array
     */
    public static function reapMethodParameters(object $instance, string $method, Container $container, array $values = [])
    {
        $reflector = new ReflectionMethod($object, $method);

        return self::reapFromMethodReflector($reflector, $container, $values);
    }

    /**
     * Reaps values from the method reflector.
     * 
     * @param ReflectionMethod $reflector
     * @param PSharp\Core\Container $container
     * @param array $values = []
     * @return array
     */
    public static function reapFromMethodReflector(ReflectionMethod $reflector, Container $container, array $values = [])
    {
        $reflParams = $reflector->getParameters();

        if (empty($reflParams)) {
            return [];
        }

        $parameters = self::reapValues($reflParams, $container);

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $parameters) && is_null($parameters[$key])) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }

    /**
     * Reaps values for the closure.
     * 
     * @param Closure $closure
     * @param PSharp\Core\Container $container
     * @param array $values = []
     * @return array
     */
    public static function reapClosureParameters(Closure $closure, Container $container, array $values = [])
    {
        $reflector = new ReflectionFunction($closure);

        $reflParams = $reflector->getParameters();

        if (empty($reflParams)) {
            return [];
        }

        $parameters = self::reapValues($reflParams, $container);

        foreach ($values as $key => $value) {
            if (array_key_exists($key, $parameters) && is_null($parameters[$key])) {
                $parameters[$key] = $value;
            }
        }

        return $parameters;
    }

    /**
     * Reaps values for the parameter list.
     * 
     * @param ReflectionParameter[] $reflParams
     * @param PSharp\Core\Container $container
     * @return array
     */
    public static function reapValues(array $reflParams, Container $container)
    {
        if (empty($reflParams)) {
            return [];
        }

        $parameterTypes = [];
        $parameterDefaults = [];
        $parameters = [];

        // collect each parameter name and (if any) its respective type
        foreach ($reflParams as $reflPar) {
            $name = $reflPar->getName();
            $type = $reflPar->getType();
            $typeName = null;

            // if named, obtains its name
            if ($type instanceof ReflectionNamedType) {
                $typeName = $type->getName();

                if ($reflPar->isOptional()) if ($reflPar->isDefaultValueAvailable()) {
                    $parameterDefaults[$name] = $reflPar->getDefaultValue();
                }
            }
            // if union, obtains the name of the first one
            elseif ($type instanceof ReflectionUnionType) {
                $types = $type->getTypes();
                foreach ($types as $subtype) if ($subtype instanceof ReflectionNamedType) {
                    $typeName = $type->getName();

                    if ($reflPar->isOptional()) if ($reflPar->isDefaultValueAvailable()) {
                        $parameterDefaults[$name] = $reflPar->getDefaultValue();
                    }

                    break;
                }
            }

            $parameterTypes[$name] = $typeName;
        }

        // reap parameter values
        foreach ($parameterTypes as $name => $type) {
            // if a default value exists, use it and go next
            if (array_key_exists($name, $parameterDefaults)) {
                $parameters[$name] = $parameterDefaults[$name];

                continue;
            }

            // if primitive, gets its default; otherwise, let's get an instance
            $parameters[$name] = $container->isPrimitive($type)
                                ? $container->getPrimitiveDefault($type)
                                : $container->make($type);
        }

        return $parameters;
    }    
}
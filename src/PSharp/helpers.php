<?php

/**
 * Returns the object instance id in the format (namesplaced_class_name@id).
 * 
 * @param mixed $object
 * @param bool $returnItIfNotObject = false - Set it to true to return the first parameter unaltered.
 * @return mixed
 */
function get_instance_id($object = null, bool $returnItIfNotObject = false)
{
    if (is_object($object)) {
        return get_class($object) . '@' . spl_object_id($object);    
    }

    if ($returnItIfNotObject) {
        return $object;
    }

    return null;
}

/**
 * Returns a path to a file or directory, relative to the app basepath.
 * 
 * @param string ...$segments
 * @return string
 */
function path(string ...$segments)
{
    return PSharp\Core\Application::getInstance()->path(...$segments);
}

/**
 * Returns the current session instance.
 * 
 * @return PSharp\Http\Session
 */
function session()
{
    return PSharp\Http\Session::getInstance();
}

/**EOF**/
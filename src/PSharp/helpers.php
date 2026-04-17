<?php

/**
 * Returns the object instance id in the format (namesplaced_class_name@id).
 * 
 * @param object|null $object
 * @return string|null
 */
function get_instance_id(object $object = null)
{
    if ($object) {
        return get_class($object) . '@' . spl_object_id($object);    
    }

    return null;
}
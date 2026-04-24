<?php

if (! function_exists('get_instance_id')) {
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
}

if (! function_exists('path')) {
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
}

if (! function_exists('session')) {
    /**
     * Returns the current session instance.
     * 
     * @return PSharp\Http\Sessions\Session
     */
    function session()
    {
        return PSharp\Http\Sessions\Session::getInstance();
    }
}

if (! function_exists('app')) {
    /**
     * Returns the app instance.
     * 
     * @return PSharp\Core\Application
     */
    function app()
    {
        return PSharp\Core\Application::getInstance();
    }
}

if (! function_exists('auth')) {
    /**
     * Returns the auth manager instance.
     * 
     * @return PSharp\Auth\AuthManager
     */
    function auth()
    {
        return app()->container()->get(PSharp\Auth\AuthManager::class);
    }
}

if (! function_exists('e')) {
	/**
	 * Encodes HTML special characters. 
	 *
	 * @param	\Zelatus\Interfaces\Support\Htmlable|string	$value
	 * @param	bool	$doubleEncode = false
	 * @return	string
	 */
	function e($value, bool $doubleEncode = false)
	{
		if ($value instanceof Htmlable) {
			return $value->toHtml();
		}
		//
		return htmlspecialchars($value, ENT_QUOTES, 'UTF-8', $doubleEncode);
	}
}

/**EOF**/
<?php
/**
 * Helper functions through the framework.
 */

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
	 * @param \Zelatus\Interfaces\Support\Htmlable|string $value
	 * @param bool $doubleEncode = false
	 * @return string
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

if (! function_exists('view')) {
    /**
     * Returns the view OR the view factory instance.
     * 
     * @param string|null $view
     * @param array|Arrayable $data
     * @param array $mergeData
     * @return PSharp\View\View|PSharp\View\ViewFactory
     */
    function view(string $view = null, $data = [], array $mergeData = [])
    {
        $factory = app()->container()->get(PSharp\View\Factory::class);

        if (is_null($view)) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}

if (! function_exists('db')) {
    /**
     * Returns the auth manager instance.
     * 
     * @param string|null $connectionName
     * @return PSharp\DB\Connection
     */
    function db(string $connectionName = null)
    {
        return app()->container()
                    ->get(PSharp\DB\DatabaseManager::class)
                    ->connection($connectionName);
    }
}

if (! function_exists('route')) {
	/**
	 * Retrieves the route path, with replaced parameters when given.
	 * 
	 * @param string $name
	 * @param array $parameters = []
	 * @return string|null
	 */
	function route(string $name, array $parameters = [])
	{
        $app = app();

		foreach ($app->router()->getEndpoints() as $endpoint) {
			if ($endpoint->getName() == $name) {
                $path = PSharp\Support\Str::replaceVariables($endpoint->getPath(), $parameters);

				return rtrim($app->prefix(), '/') . $path;
			}
		}

		return null;
	}
}

if (! function_exists('coalesce')) {
	/**
	 * Retrieves the first non-null argument, or null if all are null.
	 * 
	 * @param mixed ...$arguments
	 * @return mixed
	 */
	function coalesce(...$arguments)
	{
		foreach ($arguments as $argument) if (! is_null($argument)) {
			return $argument;
		}

		return null;
	}
}

if (! function_exists('pretty_dump')) {
    /**
     * Dumps variable and formats dump content in a browseable manner
     * with toggleable levels.
     * 
     * @param mixed $value
     * @param bool $return_value = false
     * @param bool $open = false
     * @return string|never
     */
    function pretty_dump($value, bool $return_value = false, bool $open = false)
    {
        // unique dump ID
        static $dump_id = 0;

        // built-in necessary CSS code and some vanilla Javascript
        static $cssJscript = "
            <style>
            button.dumper-btn-toggle { height: 20px !important; border-top: 3px !important; font-family: Arial !important; font-size: 14px !important; line-height: 14px !important }
            button.dumper-btn-toggle[data-state=\"open\"]::before { content: '▲' }
            button.dumper-btn-toggle[data-state=\"open\"] + span + div.dumper-panel>div { display: block; }
            button.dumper-btn-toggle[data-state=\"closed\"]::before { content: '▼' }
            button.dumper-btn-toggle[data-state=\"closed\"] + span + div.dumper-panel>div { display: none; }
            span { color: #3ff !important; font-family: monospace; }
            div.dumper-main { background-color: #020 !important; line-height: 1.75em !important; }
            div.dumper-panel { background-color: #020 !important; padding: 0px 2px 0px 32px !important; }
            div.dumper-panel>div { color: #ff3 !important; white-space: pre; font-family: monospace; line-height: 1.75em !important; }
            </style>
            <script>
            function tggl(btnRef) { btnRef.setAttribute('data-state', ((btnRef.getAttribute('data-state') == 'open') ? 'closed' : 'open')); }
            </script>
        ";

        $divState = $open ? 'open' : 'closed';

        $lines = explode("\n", print_r($value, true));

        list($levels, $level_prior, $element_count) = array([], 0, 1);

        foreach ($lines as $k => $line) {
            if ($line !== ltrim($line)) {
                $line = str_repeat(' ', 4) . $line;
            }

            $line_cleaned = trim($line);

            if (empty($line_cleaned)) {
                unset($lines[$k]);
                continue;
            }

            if ($line_cleaned === '(' || $line_cleaned === ')') {
                unset($lines[$k]);
                continue;
            }

            // crafts level based on indentation (8 spaces per level)
            $level = (strlen($line) - strlen(ltrim($line))) / 8;

            $line_ready = ($line_cleaned !== $line) ? ltrim($line) : $line;

            if ($level < $level_prior) {
                // close any opened DIVs
                for ($co = $level_prior; $co > $level; --$co) {
                    $levels[] = '</div></div>';
                }
            } elseif ($level > $level_prior) {
                $last = count($levels) - 1;

                // define div state
                $state = ($level > 1) ? $divState : 'open';

                // HTML elements
                $btnName = sprintf('%sp%s', $dump_id, $element_count);
                $button = "<button id=\"btnDump{$btnName}\" class=\"dumper-btn-toggle\" data-state=\"{$state}\" onclick=\"tggl(this)\"></button>";
                $span = "<span>{$levels[$last]}</span>";
                $div = "<div class=\"dumper-panel dumper-level-{$level}\"><div id=\"panelDump{$btnName}\">";

                // append HTML elements
                $levels[$last] = $button.$span.$div;

                // update element count
                ++$element_count;
            }

            $levels[] = trim($line_ready);

            $level_prior = $level;
        }

        // closes any previous opened level DIVs
        for ($co = $level_prior; $co > 0; --$co) {
            $levels[] = '</div></div>';
        }

        // Only includes CSS and Javascript code on output
        // when calling the dump() function at first time.
        $dump_result = (0 === $dump_id) ? $cssJscript : '';

        // Remove unnecessary line breaks from undesired places,
        // so we can let css3 do the hard work accordingly.
        $dump_result .= "<div class=\"dumper-main\" data-id=\"{$dump_id}\"><span></span>"
            . str_replace(
                ["=>\x01\x03\x02\x04[", ">\x01\x03\x02\x04[", "\x01\x03\x02\x04</", ">\x01\x03\x02\x04<", "\x01\x03\x02\x04"],
                ["=>\n[", '>[', '</', '><', "\n"],
                implode("\x01\x03\x02\x04", $levels)
            ) . '</div>';

        // allows counting function calls
        ++$dump_id;

        if ($return_value) {
            return $dump_result;
        }

        echo $dump_result;
    }
}

if (! function_exists('pretty_dd')) {
    /**
     * Dumps variable and formats dump content in a browseable manner
     * with toggleable levels and, then, stops script execution.
     * 
     * @param mixed $value
     * @param bool $open = false
     * @return never
     */
    function pretty_dd($value, bool $open = false)
    {
        pretty_dump($value, false, $open);

        exit(0);
    }
}

/**EOF**/
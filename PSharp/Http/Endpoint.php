<?php
namespace PSharp\Http;

use PSharp\Http\Methods\Base\HttpMethodBase;

/**
 * Base class for route endpoints
 */
class Endpoint extends HttpMethodBase implements IEndpoint
{
	protected const REGEX_PARAM_SEGMENT = '@{([\w\-]+)(?>:(\w+))?(?>=([^}?]+))?(\?)?}@';

	protected const REGEX_ACCEPTED = [
		'int' => '\d+',
		'alpha' => '\w+',
		'alphanum' => '[\w\d]+',
		'float' => '\d+(.\d*)?',
		'slug' => '[\w\d]+(-[\w\d]+)*',
	];

	private $parameters = [];
	private $regex = null;

	/**
	 * Constructor.
	 * 
	 * @param string $path = "/"
	 * @param string $name = null
	 */
	public function __construct(Route $route, string $path = null, string $name = null)
	{
		parent::__construct($path, $name);

		$this->setRoute($route);

		$this->parseParameters();
		$this->compileRegex();
	}

	/**
	 * Parse parameter specifications from URI pattern.
	 * 
	 * @return void
	 */
	protected function parseParameters()
	{
		$this->parameters = [];

		$path = $this->getPath();
		$segments = explode('/', $path);

		foreach ($segments as $segment) if (1 == preg_match(self::REGEX_PARAM_SEGMENT, $segment, $spec)) {
			$name = $spec[1];
			$type = $spec[2] ?? null;
			$regex = self::REGEX_ACCEPTED[$type] ?? '[^/]+';
			$default = $spec[3] ?? null;
			$optional = '?' == ($spec[4] ?? null);

			$this->parameters[$name] = (object) compact('name','type','regex','default','optional');
		}
	}

	/**
	 * Compiles the URI pattern into regex.
	 * 
	 * @return void
	 */
	protected function compileRegex()
	{
		$this->regex = null;

		$path = $this->getPath();
		$segments = explode('/', $path);
		$regexSegments = [];

		foreach ($segments as $segment) {
			if (1 == preg_match(self::REGEX_PARAM_SEGMENT, $segment, $spec)) {
				$name = $spec[1];
				$type = $spec[2] ?? null;
				$regexSegment = self::REGEX_ACCEPTED[$type] ?? '[^/]+';
				$optional = $spec[4] ?? '';

				$regexSegments[] = '(?<' . $name . '>' . $regexSegment . ')' . $optional;
			} else {
				$regexSegments[] = $segment;
			}
		}

		$regex = implode('/', $regexSegments);

		$this->regex = '@' . str_replace(')?/', ')?/?', $regex) . '@';
	}

	/**
	 * Ask if the given request URI matches the regex, retrieving
	 * parameter values if any.
	 * 
	 * @param string $requestUri
	 * @param array &$out = []
	 * @return bool
	 */
	public function matchesUri(string $requestUri, array &$values = [])
	{
		if (preg_match($this->regex, $requestUri, $out) === 1) {
			$values = [];

			foreach ($out as $key => $value) if (is_string($key)) {
				$values[$key] = $value;
			}

			return true;
		}

		return false;
	}

	/**
	 * Ask if the given request method matches.
	 * 
	 * @param string $method
	 * @return bool
	 */
	public function matchesMethod(string $method)
	{
		$thisMethod = $this->getMethod();

		return ($thisMehtod == '*') || (strtoupper($method) == $thisMethod);
	}

	/**
	 * Called by var_dump, print_r and other debug functions.
	 * 
	 * @return array
	 */
	public function __debugInfo()
	{
		return [
			'name' => $this->getName(),
			'path' => $this->getPath(),
			'action' => $this->getAction(),
			'method' => $this->getMethod(),
			'regex' => $this->regex,
			'parameters' => $this->parameters,
		];
	}
}
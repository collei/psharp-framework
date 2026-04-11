<?php
namespace PSharp\Http;

use PSharp\Http\Methods\Base\HttpMethodBase;
use InvalidArgumentException;
use Closure;

/**
 * Base class for route endpoints
 */
class Endpoint extends HttpMethodBase implements EndpointInterface
{
	/**
	 * @var string
	 */
	protected const REGEX_PARAM_SEGMENT = '@{([\w\-]+)(?>:(\w+))?(?>=([^}?]+))?(\?)?}@';

	/**
	 * @var array
	 */
	protected const REGEX_ACCEPTED = [
		'int' => '\d+',
		'alpha' => '\w+',
		'alphanum' => '[\w\d]+',
		'float' => '\d+(.\d*)?',
		'slug' => '[\w\d]+(-[\w\d]+)*',
	];

	/**
	 * @var array
	 */
	private $parameters = [];

	/**
	 * @var string
	 */
	private $regex;

	/**
	 * Constructor.
	 * 
	 * @param Psharp\Http\Route $route
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
	 * Maps to closure.
	 * 
	 * @param string $method
	 * @param string $path
	 * @param Closure $action
	 * @param string $name = null
	 */
	public static function fromClosure(string $method, string $path, Closure $action, string $name = null)
	{
		$self = new static(new Route('', ''), $path, $name);

		$self->setMethod($method);
		$self->setAction($action);

		return $self;
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
			$hasDefault = isset($spec[3]) && !empty($spec[3]);
			$default = $hasDefault ? $spec[3] : null;
			$optional = empty($default) ? ('?' == ($spec[4] ?? '')) : false;

			$this->parameters[$name] = (object) compact('name','type','regex','hasDefault','default','optional');
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
		$segments = explode('/', trim($path, '/'));
		$regexSegments = [];

		foreach ($segments as $segment) {
			if (1 == preg_match(self::REGEX_PARAM_SEGMENT, $segment, $spec)) {
				$name = $spec[1];
				$type = isset($spec[2]) ? strtolower($spec[2]) : null;
				$regexSegment = self::REGEX_ACCEPTED[$type] ?? '[^/]+';
				$default = $spec[3] ?? null;
				$optional = empty($default) ? ($spec[4] ?? '') : '?';

				$regexSegments[] = '(/(?<' . $name . '>' . $regexSegment . '))' . $optional;
			} else {
				$regexSegments[] = '/' . $segment;
			}
		}

		$this->regex = '@^' . implode('', $regexSegments) . '$@';
	}

	/**
	 * Returns the controller class, if any. Otherwise, returns null
	 * 
	 * @return string|null
	 */
	public function getControllerClass()
	{
		return $this->getActionPart(0);
	}

	/**
	 * Returns the controller method, if any. Otherwise, returns null.
	 * 
	 * @return string|null
	 */
	public function getControllerMethod()
	{
		return $this->getActionPart(1);
	}

	/**
	 * Returns the action part, if any. Otherwise, returns null.
	 * 
	 * @param int $index - 0 for class name, 1 for method name.
	 * @return string|null
	 * @throws InvalidArgumentException unless $index be either 0 or 1.
	 */
	public function getActionPart(int $index)
	{
		if ($index < 0 || $index > 1) {
			throw new InvalidArgumentException('$index must be either 0 or 1');
		}

		if (is_string($action = $this->parseAction())) {
			return $action[$index];
		}

		return null;
	}

	/**
	 * Returns the parsed action.
	 * 
	 * @return array|Closure|null
	 */
	public function getParsedAction()
	{
		return $this->parseAction();
	}

	/**
	 * Parses the action.
	 * 
	 * @return array|Closure|null
	 */
	protected function parseAction()
	{
		$action = $this->getAction();

		if (is_string($action) && strpos($action, '::') !== false) {
			return explode('::', $action, 2);
		}

		if ($this->actionIsClosure()) {
			return $this->getAction();
		}

		return null;
	}

	/**
	 * Tells if the associated action is a Closure.
	 * 
	 * @return bool
	 */
	public function actionIsClosure()
	{
		return $this->getAction() instanceof Closure;
	}

	/**
	 * Ask if the given request URI matches the regex, retrieving
	 * parameter values if any.
	 * 
	 * @param string $requestUri
	 * @param array &$values = null
	 * @return bool
	 */
	public function matchesUri(string $requestUri, array &$values = null)
	{
		if (preg_match($this->regex, $requestUri, $out) === 1) {
			$values = [];

			foreach ($out as $key => $value) if (is_string($key)) {
				$values[$key] = $this->coerceType(
					$value, $this->parameters[$key]->type ?? null
				);
			}

			foreach ($this->parameters as $name => $spec) if ($spec->hasDefault) {
				if (! isset($values[$name])) {
					$values[$name] = $spec->default;
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * Coerces value to the specified type.
	 * 
	 * @param string $value
	 * @param string|null $type = null
	 * @return mixed
	 */
	private function coerceType(string $value, $type = null)
	{
		switch ($type) {
			case 'int':
				return (int) (float) $value;
			case 'float':
				return (float) $value;
		}

		if (preg_match('/\d+/', $value) == 1) {
			return (int) $value;
		}

		if (preg_match('/\d*[.,]\d+/', $value) == 1) {
			return (float) $value;
		}

		return $value;
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

		return ($thisMethod == '*') || (strtoupper($method) == $thisMethod);
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
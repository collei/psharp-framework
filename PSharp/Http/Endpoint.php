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

	protected $parameters = [];

	/**
	 * Constructor.
	 * 
	 * @param string $path = "/"
	 * @param string $name = null
	 */
	public function __construct(string $path = null, string $name = null)
	{
		parent::__construct($path, $name);

		$this->parseParameters();
	}


	protected function parseParameters()
	{
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
			'parameters' => $this->parameters,
		];
	}
}
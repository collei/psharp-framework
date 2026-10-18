<?php
namespace PSharp\Console\Output;

use PSharp\Support\Traits\ForwardsCalls;

/**
 *	Encapsulates console output.
 *
 *	@author alarido <alarido.su@gmail.com>
 */
class Output
{
	use ForwardsCalls;

	/**
	 * @static
	 * @var array
	 */
	protected static $passthrough = [
		'line', 'write', 'writeTo', 'writeIn', 'newLine',
		'info', 'comment', 'error', 'question', 'warn',
	];

	/**
	 * @var \PSharp\Console\Output\OutputFormatInterface
	 */
	protected $output;

	/**
	 * Dynamically handle calls to the class.
	 *
	 * @static
	 * @return \PSharp\Console\Output\Output
	 */
	public static function create()
	{
		return new static();
	} 	

	/**
	 * Dynamically handle calls to the class.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->output = OutputStyler::build([
			'line'		=> '<fg=white;bg=default>',
			'info'		=> '<fg=green;bg=default>',
			'comment'	=> '<fg=yellow;bg=default>',
			'error'		=> '<fg=white;bg=red>',
			'question'	=> '<fg=cyan;bg=default>',
			'warn'		=> '<fg=yellow;bg=blue>',
		]);
	}

	/**
	 * Defines the output styler.
	 *
	 * @param	\PSharp\Console\Output\Output	$output
	 * @return	$this
	 */
	public function setOutputStyler(OutputStyler $output)
	{
		$this->output = $output;
		//
		return $this;
	}

	/**
	 * Retrieves the current output styler.
	 *
	 * @return	\PSharp\Console\Output\Output
	 */
	public function getOutputStyler()
	{
		return $this->output;
	}

	/**
	 *	Outputs to the console with newline and default style
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function line(string $text)
	{
		$this->output->writeln($text);
	}

	/**
	 *	Outputs to the console
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function write(string $text)
	{
		if (!$this->silent()) {
			Formatter::make($text)->output();
		}
	}

	/**
	 *	Outputs to the console from the $x column and $y row
	 *
	 *	@param	int		$x
	 *	@param	int		$y
	 *	@param	string	$text	
	 *	@return void
	 */
	public function writeTo(int $x, int $y, string $text)
	{
		if (!$this->silent()) {
			Formatter::make($text)->outputTo($x, $y);
		}
	}

	/**
	 *	Outputs to the console inside a delimited Rect
	 *
	 *	@param	\PSharp\Geometry\Rect		$rect
	 *	@param	string	$text	
	 *	@return void
	 */
	public function writeIn(Rect $rect, string $text)
	{
		if (!$this->silent()) {
			Formatter::make($text)->outputToRect($rect);
		}
	}

	/**
	 *	Outputs to the console with newline
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function info(string $text)
	{
		$this->output->writeln("<info>$text</info>");
	}

	/**
	 *	Outputs to the console with newline
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function comment(string $text)
	{
		$this->output->writeln("<comment>$text</comment>");
	}

	/**
	 *	Outputs to the console with newline
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function error(string $text)
	{
		$this->output->writeln("<error>$text</error>");
	}

	/**
	 *	Outputs to the console with newline
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function question(string $text)
	{
		$this->output->writeln("<question>$text</question>");
	}

	/**
	 *	Outputs to the console with newline
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function warn(string $text)
	{
		$this->output->writeln("<warn>$text</warn>");
	}

	/**
	 *	Outputs one or more blank lines
	 *
	 *	@param	int		$count
	 *	@return void
	 */
	public function newLine(int $count = 1)
	{
		$this->output->newLine($count);
	}

	/**
	 * Dynamically handle calls to the class.
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return mixed
	 */
	public function __call($method, $parameters)
	{
		if (in_array($method, self::$passthrough)) {
			return $this->{$method}(...$parameters);
		}
		//
		return $this->forwardCallTo($this->output, $method, $parameters);
	}
}
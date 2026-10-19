<?php
namespace PSharp\Console;

use PSharp\Console\Input\Input;
use PSharp\Support\Str;
use PSharp\Support\Facades\App;

/**
 *	Encapsulates the command line
 *
 *	@author alarido <alarido.su@gmail.com>
 */
class CommandLine
{
	protected const COMMAND_NAME_PATTERN = '#^([^\\s:]+)(:([^\\s]+))?$#';

	/**
	 *	@var string
	 */
	private $line = '';

	/**
	 *	@var string
	 */
	private $command = '';

	/**
	 *	@var string
	 */
	private $subCommand = '';

	/**
	 *	@var array
	 */
	private $arrayedLine = [];

	/**
	 *	@var array
	 */
	private $arguments = [];

	/**
	 *	@var array
	 */
	private $options = [];

	/**
	 *	Creates a new instance of CommandLine from the given $commandLine.
	 *	If omitted, $_SERVER['argv'] will be used instead.
	 *
	 *	@param	string	$commandLine = null
	 */
	public function __construct(string $commandLine = null)
	{
		$this->arrayedLine = !is_null($commandLine)
			? Input::parseLine($commandLine)
			: Input::captureArgs();
		//
		$this->line = trim(implode(' ', $this->arrayedLine));
		//
		$this->doParseCommandAndOptions();
	}

	/**
	 *	Returns the command line as a string
	 *
	 *	@return	string
	 */
	protected function doParseCommandAndOptions()
	{
		$test = $args = $this->arrayedLine;

		if (array_shift($test) == 'cl') {
			array_shift($args);
		}

		$this->command = array_shift($args) ?? '';

		if (preg_match(self::COMMAND_NAME_PATTERN, $this->command, $match)) {
			$this->command = $match[1];
			$this->subCommand = $match[3] ?? null;
		}

		$options = Input::parseOptions($args);

		$this->arguments = $this->options = [];

		foreach ($options as $name => $value) {
			if (is_string($name)) {
				$this->options[$name] = $value;
			} else {
				$this->arguments[$name] = $value;
			}
		}
	}

	/**
	 *	Returns the command line as a string
	 *
	 *	@return	string
	 */
	public function __toString()
	{
		return $this->line;
	}

	/**
	 *	Returns if the specified value or option exists
	 *
	 *	@param	string	$name	
	 *	@return	bool
	 */
	public function has(string $name)
	{
		return $this->hasArgument($name) || $this->hasOption($name);
	}

	/**
	 *	Returns the specified value - if it exists - or the default
	 *
	 *	@param	string	$name	
	 *	@param	mixed	$default	
	 *	@return	mixed|null
	 */
	public function get(string $name, $default = null)
	{
		if ($this->hasArgument($name)) {
			return $this->getArgument($name, $default);
		}
		//
		if ($this->hasOption($name)) {
			return $this->getOption($name);
		}
		//
		return $default;
	}

	/**
	 *	Returns the command name.
	 *
	 *	@return	string
	 */
	public function getName()
	{
		if ($this->subCommand) {
			return $this->command.':'.$this->subCommand;
		}
		//
		return $this->command;
	}

	/**
	 *	Returns if the command matches $name
	 *
	 *	@param	string	$name	
	 *	@return	bool
	 */
	public function isName(string $name)
	{
		return ($name === $this->command)
			|| ($name === $this->command.':'.$this->subCommand);
	}

	/**
	 *	Returns the subcommand (if any).
	 *
	 *	@return	string|null
	 */
	public function getSubCommand()
	{
		return $this->subCommand;
	}

	/**
	 *	Returns if the subcommand (if any) matches $name
	 *
	 *	@param	string	$name	
	 *	@return	bool
	 */
	public function isSubCommand(string $name)
	{
		return $name === $this->subCommand;
	}

	/**
	 *	Returns if the specified argument exists
	 *
	 *	@param	string	$name	
	 *	@return	bool
	 */
	public function hasArgument(string $name)
	{
		return array_key_exists($name, $this->arguments);
	}

	/**
	 *	Returns the specified argument - if it exists - or the default
	 *
	 *	@param	string	$name	
	 *	@param	mixed	$default	
	 *	@return	mixed|null
	 */
	public function getArgument(string $name, $default = null)
	{
		return $this->arguments[$name] ?? $default;
	}

	/**
	 *	Returns an array of all arguments
	 *
	 *	@return	array
	 */
	public function getArguments()
	{
		return $copy = $this->arguments;
	}

	/**
	 *	Returns the specified option - if it exists
	 *
	 *	@param	string	$name	
	 *	@return	bool
	 */
	public function hasOption(string $name)
	{
		return array_key_exists($name, $this->options);
	}

	/**
	 *	Returns the specified option - if it exists
	 *
	 *	@param	string	$name	
	 *	@return	bool
	 */
	public function getOption(string $name)
	{
		return $this->options[$name] ?? false;
	}

	/**
	 *	Returns an array of all options
	 *
	 *	@return	array
	 */
	public function getOptions()
	{
		return $copy = $this->options;
	}

	/**
	 *	Returns an array of all arguments and of all options
	 *
	 *	@return	array
	 */
	public function getArgumentsAndOptions()
	{
		return array_merge($this->arguments ?? [], $this->options ?? []);
	}

	/**
	 *	Capture the command line from the server - ignoring the first 
	 *
	 *	@return	array
	 */
	public static function capture()
	{
		return new static();
	}

	/**
	 *	Parses the command line and join the arguments, if possible, and
	 *	then returns a new CommandLine instance 
	 *
	 *	@param	string	$command
	 *	@param	array	$arguments = null
	 *	@return	\PSharp\Console\CommandLine
	 */
	public static function parse(string $command, array $arguments = null)
	{
		$commandLine = new static($command);

		if (!is_null($arguments)) foreach ($arguments as $k => $v) {
			$commandLine->arguments[$k] = $v;
		}

		return $commandLine;
	}

	/**
	 *	Parses the command line and join the arguments, if possible, and
	 *	then returns a new CommandLine instance 
	 *
	 *	@param	string	$command
	 *	@param	array	$arguments
	 *	@return	\PSharp\Console\CommandLine
	 */
	protected static function parseAndMergeArguments(string $command, array $arguments = [])
	{
		$commandLine = new static($command);

		$values = $commandLine->arguments;

		foreach ($arguments as $name => $value) if (is_int($name)) {
			$values[] = $value;
			unset($arguments[$name]);
		}

		foreach ($arguments as $name => $value) {
			if (empty($values)) {
				break;
			}

			if (is_string($name)) {
				if ($given = array_shift($values)) {
					$arguments[$name] = $given;
				}
			}
		}

		$commandLine->arguments = $arguments;

		if ($values) foreach ($values as $value) {
			$commandLine->arguments[] = $value;
		}

		return $commandLine;
	}

	/**
	 *	Parses the command line and try extract the arguments from it by using
	 *	the given command $signature, so returns a new CommandLine instance.
	 *
	 *	@param	string	$command
	 *	@param	string|PSharp\Console\Signature	$signature
	 *	@return	\PSharp\Console\CommandLine
	 */
	public static function throughSignature(string $command, string|Signature $signature)
	{
		if (is_string($signature)) {
			$signature = new Signature($signature);
		}

		$arguments = static::parseSignature($signature);

		return static::parseAndMergeArguments($command, $arguments);
	}

	/**
	 *	Extracts argument defaults from the signature.
	 *
	 *	@param	string	$signature
	 *	@return	array
	 */
	protected static function parseSignature(Signature $signature)
	{
		return array_combine(
			array_map([self::class, 'parseSignatureMapperNames'], $signature->getArguments()),
			array_map([self::class, 'parseSignatureMapperDefaultValues'], $signature->getArguments())
		);
	}

	/**
	 *	In use by parseSignature() method.
	 *
	 *	@param	mixed	$item
	 *	@return	mixed
	 */
	private static function parseSignatureMapperNames($item)
	{
		return $item['name'];
	}

	/**
	 *	In use by parseSignature() method.
	 *
	 *	@param	mixed	$item
	 *	@return	mixed
	 */
	private static function parseSignatureMapperDefaultValues($item)
	{
		return $item['default'] ?? '';
	}
}
<?php
namespace PSharp\Console;

use PSharp\Core\Container;
use PSharp\Core\Application;
use PSharp\Core\DI\ParameterReaper;
use PSharp\Console\Input\Input;
use PSharp\Console\Output\Output;
use PSharp\Console\Output\OutputFormat;
use PSharp\Console\Output\OutputStyler;
use PSharp\Console\Output\OutputStylerInterface;
use PSharp\Console\Output\Rich\Formatter;
use PSharp\Support\Str;
use PSharp\Support\Geometry\Rect;
use Closure;
use BadMethodCallException;

/**
 *	Encapsulates the basic ground of a command
 *
 *	@author alarido <alarido.su@gmail.com>
 */
abstract class CommandBase
{
	/**
	 * @var int
	 */
	public const CA_MODE_NONE = 0;

	/**
	 * @var int
	 */
	public const CA_MODE_SILENT = 1;

	/**
	 * @var \PSharp\Core\Application
	 */
	protected $app;

	/**
	 * @var \PSharp\Console\Input\Input
	 */
	protected $input;

	/**
	 * @var \PSharp\Console\Output\Output
	 */
	protected $output;

	/**
	 * @var \PSharp\Console\CommandLine
	 */
	protected $commandLine;

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $signature;

	/**
	 * @var \PSharp\Console\Signature
	 */
	protected $parsedSignature;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var string
	 */
	protected $help;

	/**
	 * @var string
	 */
	protected $longHelp;

	/**
	 * @var \Closure|null
	 */
	protected $handle = null;

	/**
	 * @var array
	 */
	protected $handleArguments = [];

	/**
	 * Execute an action on the controller.
	 *
	 */
	public function __construct()
	{
		$this->app = app();

		if ($this->signature) {
			$this->setSignature($this->signature);
		}
	}

	/**
	 * Retrieves the underlying application.
	 *
	 * @return	\PSharp\Core\Application
	 */
	public function app()
	{
		return $this->app;
	}

	/**
	 * Retrieves the command name.
	 *
	 * @return	string
	 */
	public function name()
	{
		return $this->name;
	}

	/**
	 * Defines the command name.
	 *
	 * @param	string	$name
	 * @return	$this
	 */
	public function setName(string $name)
	{
		$this->name = $name;

		return $this;
	}

	/**
	 * Returns the command name.
	 *
	 * @return	string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Retrieves the command signature, if any.
	 *
	 * @return	\PSharp\Console\Signature|null
	 */
	public function signature()
	{
		return $this->parsedSignature;
	}

	/**
	 * Defines the command signature.
	 *
	 * @param	string	$signature
	 * @return	$this
	 */
	public function setSignature(string|Signature $signature)
	{
		if (is_string($signature)) {
			$this->signature = $signature;

			$this->parsedSignature = new Signature($signature);
		} else {
			$this->signature = $signature->getSignature();

			$this->parsedSignature = $signature;
		}

		$this->setName($this->parsedSignature->getCommand());

		return $this;
	}

	/**
	 * Retrieves the command description.
	 *
	 * @return	string
	 */
	public function description()
	{
		return $this->description;
	}

	/**
	 * Defines the command description.
	 *
	 * @param	string	$description
	 * @return	$this
	 */
	public function setDescription(string $description)
	{
		$this->description = $description;
		//
		return $this;
	}

	/**
	 * Retrieves the command help.
	 *
	 * @return	string
	 */
	public function help()
	{
		return $this->help;
	}

	/**
	 * Defines the command help string.
	 *
	 * @param	string	$help
	 * @return	$this
	 */
	public function setHelp(string $help)
	{
		$this->help = $help;
		//
		return $this;
	}

	/**
	 * Retrieves the command long string help.
	 *
	 * @return	string
	 */
	public function longHelp()
	{
		if (empty($this->longHelp)) {
			$this->longHelp = $this->generateLongHelp();
		}

		return $this->longHelp;
	}

	/**
	 * Defines the command long string help.
	 *
	 * @param	string	$longHelp
	 * @return	$this
	 */
	public function setLongHelp(string $longHelp)
	{
		$this->longHelp = $longHelp;
		//
		return $this;
	}

	/**
	 * Generates a long string help from the .
	 *
	 * @param	string	$longHelp
	 * @return	$this
	 */
	protected function generateLongHelp()
	{
		$elements = [
			$this->parsedSignature->getArguments(),
			$this->parsedSignature->getOptions(),
		];
		//
		$header = '  ' . substr(str_pad('ARGUMENT', 20), 0, 20)
				. ' ' . substr(str_pad('DESCRIPTION', 40), 0, 40)
				. ' ' . substr(str_pad('DEF.VALUE', 10), 0, 10)
				. ' ' . substr(str_pad('REQ', 3), 0, 3)
				. ' ' . substr(str_pad('MUL', 4), 0, 4);
		//
		$lines = [ $header ];
		//
		foreach ($elements as $element) {
			foreach ($element as $key => $arg) {
				$opt = $arg['isOptional'] ? '   ' : ' * ';
				$mult = $arg['isMultiple'] ? ' * ' : '   ';
				//
				$lines[] = '    ' . substr(str_pad($arg['name'], 20), 0, 20)
					. ' ' . substr(str_pad($arg['description'], 40), 0, 40)
					. ' ' . substr(str_pad($arg['default'] ?: '(none)', 10), 0, 10)
					. " {$opt}"
					. " {$mult}";
			}
		}
		//
		return implode("\r\n", $lines);
	}

	/**
	 * Retrieves the input object.
	 *
	 * @return	\PSharp\Console\Input\Input
	 */
	public function input()
	{
		if ($this->input) {
			return $this->input;
		}
		//
		return $this->input = new Input();
	}

	/**
	 * Retrieves the output object.
	 *
	 * @return	\PSharp\Console\Output\Output
	 */
	public function output()
	{
		if ($this->input) {
			return $this->output;
		}
		//
		return $this->output = new Output();
	}

	/**
	 * Retrieves the command line instance.
	 *
	 * @return	\PSharp\Console\CommandLine
	 */
	public function commandLine()
	{
		return $this->commandLine;
	}

	/**
	 * Defines the command line instance.
	 *
	 * @param	\PSharp\Console\CommandLine	$input
	 * @return	$this
	 */
	public function setCommandLine(CommandLine $input)
	{
		$this->commandLine = $input;
		//
		return $this;
	}

	/**
	 *	Enables or disables silent mode (output supression)
	 *
	 *	@param	bool	$silent
	 *	@return	void
	 */
	public final function setSilent(bool $silent)
	{
		$this->output()->setSilent($silent);
	}

	/**
	 *	Returns whether the silent mode is enabled
	 *
	 *	@return	bool
	 */
	public final function silent()
	{
		return $this->output()->silent();
	}

	/**
	 *	Diplays help info on the command
	 *
	 *	@param	string	$longHelp
	 *	@return	void
	 */
	public final function displayHelp(bool $longHelp = false)
	{
		$for = sprintf('is available for <fg=light-green>%s', $this->commandName);
		$whenEmpty = sprintf('<fg=yellow>No description %s<fg=yellow>.</>%s', $for, "\r\n");
		$whenLongEmpty = sprintf('<fg=yellow>No help text %s<fg=yellow>.</>%s', $for, "\r\n");
		//
		$content = $longHelp 
			? (empty($this->longHelp) ? $whenLongEmpty : $this->longHelp)
			: (empty($this->help) ? $whenEmpty : $this->help);
		//
		$this->write($content);
	}

	/**
	 *	Finds a command and returns its instance, if any
	 *
	 *	@param	string	$command	the command name
	 *	@return	\PSharp\Console\Command|false
	 */
	public final function findCommand(string $commandName)
	{
		return CommandEntries::findByName($commandName);
	}

	/**
	 *	Calls another command from here
	 *
	 *	@param	string	$command	the command name as from CLI.
	 *	@param	array	$arguments	arguments, if needed
	 *	@return	mixed
	 */
	public function call(string $command, array $arguments = null)
	{
		return $this->invokeAnother($command, $arguments);
	}

	/**
	 *	Calls another command from here, suppressing all its output
	 *
	 *	@param	string	$command	the command name as from CLI.	
	 *	@param	array	$arguments	arguments, if needed
	 *	@return	mixed
	 */
	public function callSilently(string $command, array $arguments = null)
	{
		return $this->invokeAnother($command, $arguments, self::CA_MODE_SILENT);
	}

	/**
	 *	Reads a char from the console input. Prompt is optional.s
	 *
	 *	@param	string	$question
	 *	@return	string
	 */
	public function readkey(string $prompt = '')
	{
		return ConsoleInput::readchar($prompt);
	}

	/**
	 *	Prompts for user input
	 *
	 *	@param	string	$question
	 *	@return	string
	 */
	public function ask(string $question, bool $arrow = true)
	{
		return ConsoleInput::prompt($question, null, $arrow);
	}

	/**
	 *	Prompts for user input without revealing it at screen
	 *
	 *	@param	string	$question
	 *	@return	string
	 */
	public function secret(string $question)
	{
		return ConsoleInput::silent($question);
	}

	/**
	 *	Prompts for user confirm
	 *
	 *	@param	string	$prompt
	 *	@param	bool	$default
	 *	@return	bool
	 */
	public function confirm(string $prompt, bool $default = false)
	{
		$resp = strtolower(trim(ConsoleInput::prompt($prompt)));
		
		if (in_array($resp, ['y','yes','1'])) {
			return true;
		} elseif (in_array($resp, ['n','no','0'])) {
			return false;
		}
		return $default;
	}

	/**
	 *	Prompts for user choice within the given option set
	 *
	 *	@param	string	$prompt
	 *	@param	array	$options
	 *	@param	int		$defaultIndex	Default choice
	 *	@param	int		$maxAttempts	how many attempts. Default is none.
	 *	@param	int		$maxSelections	how many selectable itens. Defualt is 1
	 *	@return	string
	 */
	public function choice(
		string $prompt, array $options, int $defaultIndex = 0,
		int $maxAttempts = 1, int $maxSelections = 1
	) {
		return ConsoleInput::choice(
			$prompt, $options, $defaultIndex, $maxAttempts, $maxSelections
		);
	}

	/**
	 *	Sets an environment variable in the current console window
	 *
	 *	@param	string	$name	
	 *	@param	string	$value	
	 *	@return	void
	 */
	public function setEnv(string $name, string $value = null)
	{
		$this->app->setEnv($name, $value);
	}

	/**
	 *	Gets an environment variable in the current console window
	 *
	 *	@param	string	$name	
	 *	@return	string
	 */
	public function getEnv(string $name)
	{
		return $this->app->getEnv($name);
	}

	/**
	 *	Lists all environment variable names, if any
	 *
	 *	@return	array
	 */
	public function listEnv()
	{
		return $this->app->listEnv();
	}

	/**
	 *	Lists all environment variables, if any
	 *
	 *	@return	array
	 */
	public function listEnvWithValues()
	{
		return $this->app->listEnvWithValues();
	}

	/**
	 *	Outputs to the console with newline and default style
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function line(string $text)
	{
		$this->output()->writeln($text);
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
	 *	Outputs to the console with padding
	 *
	 *	@param	string	$text
	 *	@param	int	$length
	 *	@return void
	 */
	public function writePad(
		string $text, int $maxLength, int $padding = null, string $padWith = ' '
	) {
		$padding = in_array($padding, OutputFormat::PADDINGS)
			? $padding
			: OutputFormat::PADDING_LEFT;
		//
		$remainder = $maxLength - strlen($text);
		//
		if ($remainder > 0) {
			switch ($padding) {
				case OutputFormat::PADDING_RIGHT:
					$text = str_repeat($padWith, $remainder) . $text;
					break;
				case OutputFormat::PADDING_CENTER:
					$text = str_repeat($padWith, $remainder / 2) . $text . str_repeat($padWith, $remainder / 2);
					$text = substr($text, 0, $maxLength);
					break;
				default:
					$text = $text . str_repeat($padWith, $remainder);
					break;
			}
		}
		//
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
		$this->output()->writeln("<info>$text</info>");
	}

	/**
	 *	Outputs to the console with newline
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function comment(string $text)
	{
		$this->output()->writeln("<comment>$text</comment>");
	}

	/**
	 *	Outputs to the console with newline
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function error(string $text)
	{
		$this->output()->writeln("<error>$text</error>");
	}

	/**
	 *	Outputs to the console with newline
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function question(string $text)
	{
		$this->output()->writeln("<question>$text</question>");
	}

	/**
	 *	Outputs to the console with newline
	 *
	 *	@param	string	$text	
	 *	@return void
	 */
	public function warn(string $text)
	{
		$this->output()->writeln("<warn>$text</warn>");
	}

	/**
	 *	Outputs one or more blank lines
	 *
	 *	@param	int		$count
	 *	@return void
	 */
	public function newLine(int $count = 1)
	{
		$this->output()->newLine($count);
	}

	/**
	 * Execute an action on the controller.
	 *
	 * @param  string  $method
	 * @param  array  $parameters
	 * @return int
	 */
	public function callAction($method, $parameters)
	{
		return call_user_func_array([$this, $method], $parameters);
	}

	/**
	 *	Builds a new instance of Command and binds the closure to it
	 *
	 *	@param	string		$signature
	 *	@param	\Closure	$closure
	 *	@return	\PSharp\Console\Command
	 */
	public static function makeFromClosure(string $signature, Closure $closure)
	{
		return new class($signature, $closure) extends CommandBase
		{
			protected $handle;

			public function __construct(string $signature, Closure $handle)
			{
				parent::__construct(app());
				$this->setSignature($signature);
				$this->handle = $handle;
				$this->handleArguments = app(ParameterReaper::class)->reapClosureParameters($handle);
				$this->setName($this->parsedSignature->getCommand());
			}

			public function handle()
			{
				$closure = $this->handle;
				$parameters = $this->handleArguments;
				return $closure->call($this, ...$parameters);
			}
		};
	}

	/**
	 *	Calls another command from here, suppressing all its output
	 *
	 *	@param	string	$name		the command name	
	 *	@param	array	$arguments	the arguments
	 *	@param	?bool	$silent		true or false (to define) or null (to keep the command default)
	 *	@return	mixed
	 */
	protected function invokeAnother(string $name, array $arguments = null, ?bool $silent = null) 
	{
		foreach ($arguments as $n => $arg) {
			if (strpos($arg, ' ') !== false) {
				$arguments[$n] = sprintf('"%s"', $arg);
			}
		}

		$input = $name . ' ' . implode(' ', $arguments);

		/**
		 * @todo verify exceptions on subroutine call. 
		 **/
		$holder = app(CommandHolder::class);

		if ($command = $holder->find($input)) {
			return $holder->callCommand($input, $command, $silent);
		}

		return false;
	}
}
<?php
namespace PSharp\Console;

use ReflectionClass;
use ReflectionMethod;
use Closure;
use PSharp\Support\Str;
use PSharp\Core\DI\ParameterReaper;

/**
 * The app command holder
 */
class CommandHolder
{
	/**
	 * List of commands
	 */
	private $commands = [];

	/**
	 * Adds a Closure to the mapper.
	 * 
	 * @param string $method
	 * @param string $path
	 * @param Closure $action
	 * @param string|null $name
	 */
	public function addClosure(string $commandSignature, Closure $action)
	{
		$signature = new Signature($commandSignature);

		$closure = true;
		$name = $signature->getCommand();
		$arguments = $signature->getArguments();
		$options = $signature->getOptions();

		$command = CommandBase::makeFromClosure($signature, $action);

		$this->commands[$name] = (object) compact('command','arguments','options','signature','closure');

		return $this;
	}

	/**
	 * Map all controllers detected under $namespace from the specifed $appDir,
	 * assuming the project follows psr-4 guidelines.
	 * 
	 * @param string $appDir
	 * @param string $namespace = 'App\Controllers\Console'
	 * @param bool $ignoreIfNotExists = false
	 * @return $this
	 * 
	 * @throws \PSharp\Console\ConsoleException if path not found
	 */
	public function mapCommands(string $appDir, string $namespace = 'App\Controllers\Console', bool $ignoreIfNotExists = false)
	{
		$path = preg_replace('#[\\/]+#', DIRECTORY_SEPARATOR, $appDir . DIRECTORY_SEPARATOR . $namespace);

		if ((! $ignoreIfNotExists) && (! is_dir($path))) {
			throw new ConsoleException(sprintf('Path not found on console command search: %s', $path));
		}

		$files = array_diff(scandir($path), array('.','..'));

		foreach ($files as $file) {
			if (is_file($path . DIRECTORY_SEPARATOR . $file)) {
				if (Str::endsWith(strtolower($file), '.php')) {
					$class = $namespace . '\\' . substr($file, 0, -4);

					$this->mapCommand($class);
				}
			}
		}

		return $this;
	}

	/**
	 * Map the specified command object.
	 * 
	 * @param string|\PSharp\Console\CommandBase $command
	 * @param bool $ignoreIfNotExists = false
	 * @return $this
	 */
	public function mapCommand(string|CommandBase $command, bool $ignoreIfNotExists = false)
	{
		if (! $ignoreIfNotExists && is_string($command) && ! class_exists($command, true)) {
			throw new ConsoleException(sprintf('Command class not found: %s', $command));
		}

		$reflect = new ReflectionClass($command);
		
		$className = $reflect->getName();
		
		foreach ($reflect->getAttributes() as $classAttr) {
			$classAttrName = $classAttr->getName();

			// Ignore other attributes
			if (Signature::class == $classAttrName) {
                $signature = $classAttr->newInstance();

				$closure = false;
                $name = $signature->getCommand();
                $arguments = $signature->getArguments();
                $options = $signature->getOptions();

                if (is_string($command) && class_exists($command, true)) {
                    $command = app($command);
                }

				if (empty($command->signature())) {
					$command->setSignature($signature);
				}

                $this->commands[$name] = (object) compact('command','arguments','options','signature','closure');
			}
		}
	}

	/**
	 * Calls the command internally.
	 * 
	 * @param string $commandInput
	 * @param \PSharp\Console\CommandBase $command
	 * @param bool $silent = false
	 * @return int|false
	 */
	public function callCommand(string $commandInput, CommandBase $command, ?bool $silent = null)
	{
		$input = CommandLine::throughSignature($commandInput, $command->signature());

		if (! is_null($silent)) {
			$command->setSilent($silent);
		}

		if ($returnCode = $this->processClassCommand($command, $input, $returnCode)) {
			return $returnCode;
		}

		return false;
	}

	/**
	 * Finds the command for the input line and, if command is found,
	 * runs it with dependency injection and returns code.
	 * 
	 * @param string $commandInput
	 * @param ?int &$returnCode = null
	 * @return bool
	 */
	public function processCommand(string $commandInput, ?int &$returnCode = mull, bool $showNotice = false)
	{
		$name = (new CommandLine($commandInput))->getName();

		$target = $this->commands[$name] ?? null;
		
		if (empty($target)) {
			if ($showNotice) {
				echo("-- Command not found !\n");
			}

			return false;
		}

		$command = $target->command;

		$input = CommandLine::throughSignature($commandInput, $command->signature());

		app()->container()->instance($input);

		if ($target->closure === true) {
			return $command->handle();
		}

		$expected = app(ParameterReaper::class)->reapMethodParameters($command, 'handle', []);

		$passed = $input->getArgumentsAndOptions();

		foreach ($expected as $name => $value) {
			if (array_key_exists($name, $passed)) {
				$expected[$name] = $passed[$name];
			}
		}

		return $command->handle(...$expected);
	}

	/**
	 *	Returns all available commands.
	 *
	 *	@return	array
	 */
	public function available(string $prefix = null)
	{
		if (empty($prefix)) {
			return array_keys($this->commands);
		}

		$list = [];

		foreach ($this->commands as $name => $item) {
			if (Str::startsWith($name, $prefix)) {
				$list[] = $name;
			}
		}

		return $list;
	}

	/**
	 *	Returns the given command, if any.
	 *
	 *	@param	string	$name
	 *	@return	array
	 */
	public function find(string $name)
	{
		if (empty($prefix)) {
			return $this->available();
		}

		if ($item = $this->commands[$name] ?? null) {
			return $item->command;
		}

		return null;
	}

	/**
	 *	Return the command with the given name, if any.
	 *
	 *	@return	\PSharp\Console\Command|null
	 */
	public function getCommand(string $name)
	{
		if (array_key_exists($name, $this->commands)) {
			return $this->commands[$name]->command;
		}

		return null;
	}

	/**
	 *	Return the help content for the command with the given name, if any.
	 *
	 *	@return	string|null
	 */
	public static function getCommandHelp(string $name, bool $long = false)
	{
		if ($command = $this->getCommand($name)) {
			return $long ? $command->longHelp() : $command->help();
		}

		return null;
	}
}
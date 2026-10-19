<?php
namespace PSharp\Console\Input;

use PSharp\Console\Terminal;

/**
 *	Encapsulates console input for a command.
 *
 *	@author alarido <alarido.su@gmail.com>
 */
class Input extends Terminal
{
	/**
	 *	the predefined choice messages
	 */
	protected const MESSAGE_CHOICE = [
		'askerMultiple'			=> 'Up to {many} options, separated by space',
		'askerUnique'			=> 'Just one option (enter 0 to leave)',
		'choiceErrorInvalid'	=> 'Wrong option! Must be an option number',
		'choiceLeave'			=> 'No option selected.',
		'choiceAttempt'			=> 'You have {chances} chances left. Try?',
	];

	/**
	 * @var string
	 */
	protected const RGX_LINE_PARSER = '#("((?:[^"\\\\]|\\\\.)*)"|\\\'((?:[^\\\'\\\\]|\\\\.)*)\\\'|([^\\\'"\s]+))#';

	/**
	 * @var string
	 */
	protected const RGX_OPTION_LONG_VALUED = '@\-\-([\w-].+)[:=](.+)@';

	/**
	 * @var string
	 */
	protected const RGX_OPTION_LONG_VALUELESS = '@\-\-([\w-]+)@';

	/**
	 * @var string
	 */
	protected const RGX_OPTION_SHORT_VALUED = '@\-([\w])[:=](.+)@';

	/**
	 * @var string
	 */
	protected const RGX_OPTION_SHORT_VALUELESS = '@\-([\w]+)@';

	/**
	 * @var array
	 */
	protected $argv = [];

	/**
	 * @var array
	 */
	protected $argvOptions = [];

	/**
	 * Captures args from PHP CLI environment as array.
	 *
	 * @return	array
	 */
	public static function captureArgs()
	{
		global $argv;

		return $argv ?? $_SERVER['argv'];
	}

	/**
	 * Captures args from PHP CLI environment as string.
	 *
	 * @return	string
	 */
	public static function captureCommandLine(bool $withCalleeScript = false)
	{
		$arguments = self::captureArgs();

		if (! $withCalleeScript) {
			array_shift($arguments);
		}

		foreach ($arguments as $n => $arg) {
			if (strpos($arg, ' ')) {
				$arguments[$n] = sprintf('"%s"', $arg);
			}
		}

		return implode(' ', $arguments);
	}

	/**
	 * Retrieves natural args from CLI.
	 *
	 * @return	string
	 */
	public function argv()
	{
		if (! empty($this->argv)) {
			return $this->argv;
		}
		//
		return $this->argv = self::captureArgs();
	}

	/**
	 * Retrieves natural args from CLI.
	 *
	 * @return	string
	 */
	public function argvOptions()
	{
		if (! empty($this->argvOptions)) {
			return $this->argvOptions;
		}
		//
		return $this->argvOptions = self::parseOptions($this->argv());
	}

	/**
	 * Parses a line into arguments in a CLI manner.
	 *
	 * @param	string	$line
	 * @return	array
	 */
	public static function parseLine(string $line)
	{
		$args = [];
		//
		if (false !== preg_match_all(static::RGX_LINE_PARSER, $line, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$piece = trim($match[4] ?? $match[3] ?? $match[2] ?? '');
				//
				if (! empty($piece)) {
					$args[] = $piece;
				}
			}
		}
		//
		return $args;
	}

	/**
	 * Parses an array of options from a CLI line parsed into an array.
	 *
	 * @param	array	$argv
	 * @return	array
	 */
	public static function parseOptions(array $argv)
	{
		$opts = [];
		//
		foreach($argv as $k => $a){
			if (preg_match(self::RGX_OPTION_LONG_VALUED, $a, $m)) {
				$name = $m[1];
				$value = $m[2];
				//
				if (isset($opts[$name])) {
					if (is_array($opts[$name])) {
						$opts[$name][] = $value;
					} else {
						$opts[$name] = [$opts[$name], $value];
					}
				} else {
					$opts[$name] = $value;
				}
			} elseif (preg_match(self::RGX_OPTION_LONG_VALUELESS, $a, $m)) {
				$name = $m[1];
				//
				$opts[$name] = true;
			} elseif (preg_match(self::RGX_OPTION_SHORT_VALUED, $a, $m)) {
				$name = $m[1];
				$value = $m[2];
				//
				if (isset($opts[$name])) {
					if (is_array($opts[$name])) {
						$opts[$name][] = $value;
					} else {
						$opts[$name] = [$opts[$name], $value];
					}
				} else {
					$opts[$name] = $value;
				}
			} elseif(preg_match(self::RGX_OPTION_SHORT_VALUELESS, $a, $m)) {
				$shorts = str_split($m[1], 1);
				//
				foreach ($shorts as $short) if (!empty($short)) {
					$opts[$short] = true;
				}
			} else {
				$opts[$k] = $a;
			}
		}
		//
		return $opts;
	}

	/**
	 * Parses options from PHP CLI.
	 *
	 * @static
	 * @param	array	$argv
	 * @return	array
	 */
	public static function parseNativeOptions()
	{
		return self::parseOptions(self::captureArgs());
	}

	/**
	 * Reads user input from CLI with an optional prompt.
	 *
	 * @param	array	$argv
	 * @return	array
	 */
	public function readLine(string $prompt = null)
	{
		$prompt = ($prompt ?? '::>') . ' ';
		//
		return readline($prompt);
	}

	/**
	 * Reads user input from CLI with an optional prompt
	 * and returns it as array.
	 *
	 * @param	string	$prompt = null
	 * @return	array
	 */
	public function readParsedLine(string $prompt = null)
	{
		return $this->parseLine($this->readLine($prompt));
	}

	/**
	 *	Reads a line of user input from the console
	 *
	 *	@return	string	the user input
	 */
	public function read()
	{
		return fgets(STDIN);
	}

	/**
	 *	Reads a single char from the console.
	 *
	 *	@param	string	$prompt = ""
	 *	@return	string
	 */
	public function readchar(string $prompt = "")
	{
		if (!empty($prompt)) {
			echo($prompt . ':');
		}
		//
		while (true) {
			//clear buffer - read all unwanted characters
			while (fgetc(STDIN) != "\n") {
				usleep(6250);
			}
			//get first character from STDIN
			return fgetc(STDIN);
		}
	}

	/**
	 *	Reads a line of user input from the console after prompting.
	 *
	 *	@return	string	the user input
	 */
	public function prompt(string $prompt, $color = null, bool $arrow = false)
	{
		if (!is_null($color) && self::valid($color)) {
			$this->setAnsiColor($color);
			//
			echo(trim($prompt) . ($arrow ? ': ' : ''));
			//
			$this->resetColor();
			//
			echo(' ');
		} else {
			echo(trim($prompt) . ($arrow ? ': ' : ''));
		}
		//
		$input = fgets(STDIN);
		//
		usleep(6250);
		//
		return $input;
	}

	/**
	 *	Reads a line of user input from the console - hiding it.
	 *
	 *	@return	string	the user input
	 */
	public function silent(string $prompt, $color = null)
	{
		$secret = [];
		//
		if (!is_null($color) && self::valid($color)) {
			self::setAnsiColor($color);
			//
			echo(trim($prompt) . ':');
			//
			self::resetColor();
			//
			echo(' ');
		} else {
			echo(trim($prompt) . ': ');
		}
		//
		exec('hiddeninput', $secret);
		//
		echo "\r\n";
		//
		return $secret[0];
	}

	/**
	 *	Reads a key from the keyboard.
	 *
	 *	@return	string	the user input
	 */
	public function readKey()
	{
		$input = fgetc(STDIN);
		//
		usleep(6250);
		//
		return $input;
	}

	/**
	 *	Ask for confirmation.
	 *
	 *	@param	string	$prompt		Text to display
	 *	@param	bool	$default	Default response. Defaults to false
	 *	@return	bool
	 */
	public function confirm(string $prompt, bool $default = false)
	{
		$resp = strtolower(trim($this->prompt($prompt)));
		//
		if (in_array($resp, ['y','yes','1'])) {
			return true;
		} elseif (in_array($resp, ['n','no','0'])) {
			return false;
		}
		//
		return $default;
	}

	/**
	 *	Reads choice from user, displaying available options.
	 *
	 *	@param	string	$prompt			the message to be displayed
	 *	@param	array	$options		list of options to be displayed
	 *	@param	int		$defaultIndex	default if no choice is made.
	 *	@param	int		$maxAttempts	how many attempts for invalid options.
	 *	@param	int		$maxSelections	how many options can bew selected.
	 *	@return	int
	 */
	public function choice(
		string $prompt,
		array $options,
		int $defaultIndex = null,
		int $maxAttempts = 1,
		int $maxSelections = 1
	) {
		$attempt = 1;
		$optionCount = count($options);
		$maxAttempts = ($maxAttempts < 1) ? 1 : $maxAttempts;
		$maxSelections = ($maxSelections < 1) ? 1 : $maxSelections;
		//
		if (!is_null($defaultIndex)) {
			$defaultIndex = ($defaultIndex < 0)
				? 0
				: $defaultIndex;
			$defaultIndex = ($defaultIndex >= $optionCount)
				? ($optionCount - 1) 
				: $defaultIndex;
		}
		//
		echo "\r\n- $prompt:";
		//
		foreach ($options as $i => $option) {
			$n = $i + 1;
			echo "\r\n\t[ $n ] : $option";
		}
		//
		$asker = self::MESSAGE_CHOICE['askerUnique'];
		//
		if ($maxSelections > 1) {
			$asker = str_replace(
				'{many}', $maxSelections, self::MESSAGE_CHOICE['askerMultiple']
			);
		}
		//
		while ($attempt <= $maxAttempts) {
			$num = 0;
			//
			echo "\r\n -" . $asker . ' --> '; 
			//
			$num = trim(Co::read());
			//
			if (!is_numeric($num)) {
				echo "\r\n- " . self::MESSAGE_CHOICE['choiceErrorInvalid'];
			} elseif ($num == 0) {
				echo "\r\n- " . self::MESSAGE_CHOICE['choiceLeave'];
				return $defaultIndex;
			} elseif ($num < 1 || $num > $optionCount) {
				echo "\r\n- " . self::MESSAGE_CHOICE['choiceErrorInvalid'];
			} else {
				return $num - 1;
			}
			//
			if ($attempt < $maxAttempts)
			{
				$leftCount = (string)($maxAttempts - $attempt);
				$message = "\r\n\r\n- " 
					. str_replace('{chances}', $leftCount, self::MESSAGE_CHOICE['choiceAttempt'])
					. " (Y/N): ";
				//
				if (!self::confirm($message)) {
					break;
				}
			}
			//
			++$attempt;
		}
		//
		return $defaultIndex;
	}
}
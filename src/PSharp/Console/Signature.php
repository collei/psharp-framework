<?php
namespace PSharp\Console;

use Attribute;
use PSharp\Support\Str;

/**
 * Class attribute for endpoints responding upon all verbs
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Signature
{
	/**
	 * @var string
	 */
	protected const REGEX_PATTERN = '/\\{(?>(?<option>--)?(?>(?<shortcut>\\w)\\|)?(?<name>\\w[\\w:]*\\w?)?)(?<optional>\\?)?(?<multiple>\\*)?(?>=(?<default>[^}]*))?\\}/i';

	/**
	 * @var string
	 */
	protected $signature;

	/**
	 * @var string
	 */
	protected $command;

	/**
	 * @var array
	 */
	protected $arguments = [];

	/**
	 * @var array
	 */
	protected $options = [];

	/**
	 *	Creates a new instance and initializes it by parsing
	 *	the given command signature.
	 *
	 *	@param	string	$signature
	 *	@return	void
	 */
	public function __construct(string $signature)
	{
		$this->signature = $signature;

		$this->parseSignature($signature);
	}

	/**
	 *	Get the command signature.
	 *
	 *	@return	string
	 */
	public function getSignature()
	{
		return $this->signature;
	}

	/**
	 *	Get the command name.
	 *
	 *	@return	string
	 */
	public function getCommand()
	{
		return $this->command;
	}

	/**
	 *	Get all parsed arguments.
	 *
	 *	@return	array
	 */
	public function getArguments()
	{
		return $this->arguments;
	}

	/**
	 *	Get all parsed options.
	 *
	 *	@return	array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 *	Get all parsed content.
	 *
	 *	@return	array
	 */
	public function getAll()
	{
		$command = $this->getCommand();
		$arguments = $this->getArguments();
		$options = $this->getOptions();

		return compact('command','arguments','options');
	}

	/**
	 *	Get the option name for the given shortcut, if any.
	 *
	 *	@param	string|null $shortcut
	 *	@return string|null
	 */
	public function getOptionByShortcut(?string $shortcut = null)
	{
		foreach ($this->options as $item) {
			if (($shortcut ?? '') == $item['shortcut']) {
				return $item['name'];
			}
		}

		return null;
	}

	/**
	 *	Parses the command signature into an array info about the parameters
	 *	the command expects
	 *
	 *	@param	string	$signature
	 *	@param	array	&$info
	 *	@return	bool
	 */
	protected function parseSignature(string $signature)
	{
		[$line, $list] = [str_replace(["\r","\n"], '', trim($signature)), ($args = [])];

		[$command, $signature] = explode(' ', $signature . ' ', 2);

		$this->command = $command;

		if (preg_match_all(self::REGEX_PATTERN, $line, $list, PREG_SET_ORDER)) {
			$first = true;

			foreach ($list as $item) {
				$isOptional = ($item['optional'] ?? null) === '?';
				$isMultiple = ($item['multiple'] ?? null) === '*';

				if ($default = trim($item['default'] ?? null)) {
					if (Str::isQuoted($default)) {
						$default = trim($default, '\'"');
					} elseif (is_numeric($default)) {
						$number = (float) $default;
						$default = ($number == (int) $number) ? (int) $number : $number;
					} else {
						$lowered = strtolower($default);
						$default = ($lowered === 'true')
							? true
							: (($lowered === 'false') ? false : $default);
					}
				} else {
					$default = null;
				}

				// it's an option
				if (! empty($item['option'])) {
					$shortcut = $item['shortcut'] ?? '';

					$this->addOption($item['name'], '', $shortcut, $default, true, $isMultiple);
				}
				// it's an argument
				else {
					$this->addArgument($item['name'], '', $default, $isOptional, $isMultiple);
				}

				$first = false;
			}

			return true;
		}

		return preg_match('#^([^\\s\\:]+)(\\:([^\\s]+))?\\s*#i', trim($signature));
	}

	/**
	 *	Adds an Option with the following constraints.
	 *
	 *	@param	string	$name
	 *	@param	string	$description
	 *	@param	string	$shortcut
	 *	@param	string	$default
	 *	@param	bool	$isOptional
	 *	@param	bool	$isMultiple
	 *	@return	bool
	 */
	protected function addOption(
		$name, $description, $shortcut, $default, $isOptional, $isMultiple
	) {
		$default = empty($default)
				? false
				: ($isMultiple ? [] : $default);
		
		$this->options[] = compact(
			'name','description','shortcut','default','isOptional','isMultiple'
		);
	}

	/**
	 *	Adds an Option with the following constraints.
	 *
	 *	@param	string	$name
	 *	@param	string	$description
	 *	@param	string	$default
	 *	@param	bool	$isOptional
	 *	@param	bool	$isMultiple
	 *	@return	bool
	 */
	protected function addArgument(
		$name, $description, $default, $isOptional, $isMultiple
	) {
		$this->arguments[] = compact(
			'name','description','default','isOptional','isMultiple'
		);
	}
}
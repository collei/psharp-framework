<?php
namespace PSharp\Console;

use Attribute;

/**
 * Class attribute for endpoints responding upon all verbs
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Signature
{
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
		$this->parseSignature($signature);
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
	 *	Parses the command signature into an array info about the parameters
	 *	the command expects
	 *
	 *	@param	string	$signature
	 *	@param	array	&$info
	 *	@return	bool
	 */
	protected function parseSignature(string $signature)
	{
		$pattern = '#\\{\\s*((\\w[\\w:\\-]*)(\\?\\*|[?*])?(=("[^"]*"|\''
			. '[^\']*\'|[^\\s}]*)?)?|(-(\\w)\\|)?(--(\\w[\\w\\-]*\\w))(=("'
			. '[^"]*"|\'[^\']*\'|[^\\s}]*)?)?)(\\s*:\\s*([^}]+))?\\s*\\}#i';

		$line = str_replace(["\r","\n"], '', trim($signature));
		$list = ($args = []);

		[$command, $signature] = explode(' ', $signature . ' ', 2);

		$this->command = $command;

		if (preg_match_all($pattern, $line, $list, PREG_SET_ORDER)) {
			$first = true;

			foreach ($list as $item) {
				// it's an option
				if (!empty($item[9] ?? '')) {
					$default = trim($item[11] ?? '');

					$this->addOption(
						$item[9],
						trim($item[13] ?? ''),
						trim($item[7] ?? ''),
						$default,
						true,
						($default == '*')
					);
				}

				// it's an argument
				elseif (!empty($item[2] ?? '')) {
					$signal = trim($item[3] ?? '');
					//
					$this->addArgument(
						$item[2],
						trim($item[13] ?? ''),
						trim($item[5] ?? ''),
						(strpos($signal, '?') !== false),
						(strpos($signal, '*') !== false)
					);
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
				: (($default == '*') ? [] : $default);
		
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
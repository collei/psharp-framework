<?php
namespace PSharp\Console\Output;

/**
 *	This class represents a console format for writing
 *
 *	@author	alarido <alarido.su@gmail.com>
 */
class OutputFormat implements OutputFormatInterface
{
	/**
	 *	console write padding constants
	 */
	public const PADDING_LEFT = 0;
	public const PADDING_CENTER = 1;
	public const PADDING_RIGHT = 2;
	public const PADDINGS = [
		self::PADDING_LEFT,
		self::PADDING_CENTER,
		self::PADDING_RIGHT
	];

	/**
	 *	@var	string	$name
	 */
	protected $name = '';

	/**
	 *	@var	string	$foreground
	 */
	protected $foreground = '';

	/**
	 *	@var	string	$background
	 */
	protected $background = '';

	/**
	 *	@var	array	$effects
	 */
	protected $effects = [];

	/**
	 *	Builds a new instance of OutputFormat with its settings
	 *
	 *	@param	string	$name		name of the format
	 *	@param	string	$fgColor	foreground color (either color name, int or hex format)	
	 *	@param	string	$bgColor	background color (either color name, int or hex format)	
	 *	@param	array	$effects	one or more of these effects (bold, underline, reversed)	
	 */	
	public function __construct(string $name, string $fgColor, string $bgColor, array $effects = [])
	{
		$this->name = $name;
		$this->foreground = $fgColor;
		$this->background = $bgColor;
		$this->effects = $effects;
	}

	/**
	 *	Returns the style name
	 *
	 *	@return	string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 *	Outputs $text according to the style definitions
	 *
	 *	@param	string	$content
	 *	@return	void
	 */
	public function write(string $content)
	{
		ConsoleOutput::write(
			$content,
			$this->foreground,
			$this->background,
			$this->effects
		);
	}

	/**
	 *	Outputs $text according to the style definitions, starting
	 *	from ($x,$y) screen coordinates
	 *
	 *	@param	string	$content
	 *	@param	int		$x			console column position	
	 *	@param	int		$y			console row position
	 *	@return	void
	 */
	public function writeTo(string $content, int $x, int $y)
	{
		ConsoleOutput::writeTo(
			$x,
			$y,
			$content,
			$this->foreground,
			$this->background,
			$this->effects
		);
	}

	/**
	 *	Outputs $text according to the style definitions,
	 *	into the defined rect
	 *
	 *	@param	string	$content
	 *	@param	int		$left		console first column position	
	 *	@param	int		$top		console first row position
	 *	@param	int		$right		console last column position
	 *	@param	int		$bottom		console last row position
	 *	@return	void
	 */
	public function writeInRect(
		string $content,
		int $left,
		int $top,
		int $right,
		int $bottom
	) {
		ConsoleOutput::writeInRect(
			$left,
			$top,
			$right,
			$bottom,
			$content,
			$this->foreground,
			$this->background,
			$this->effects
		);
	}

	/**
	 *	Outputs $text according to the style definitions, plus a newline
	 *
	 *	@param	string	$content
	 */
	public function writeln(string $content)
	{
		$this->write($content);

		ConsoleOutput::write("\r\n");
	}

	/**
	 *	Builds a new instance of OutputFormat from a tag spec like
	 *		<fg=color;bg=color;options=bold,underline,reverse>
	 *	with the same values as the ones accepted by the constructor
	 *
	 *	@param	string	$name		name of the format
	 *	@param	string	$spec		a valid tag	as described above
	 *	@return	\PSharp\Console\Output\OutputFormat
	 */	
	public static function fromTag(string $name, string $spec)
	{
		$spec = trim($spec);
		$params = [];

		if (OutputStyleParser::parse($spec, $params))
		{
			if (in_array($params['type'], ['start','full']))
			{
				return new self(
					$name,
					$params['fg'] ?? 'default',
					$params['bg'] ?? 'default',
					$params['options'] ?? []
				);
			}
		}
		else
		{
			return new self($name, 'default', 'default', []);
		}
	}
}
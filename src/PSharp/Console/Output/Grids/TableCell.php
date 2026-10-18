<?php
namespace PSharp\Console\Output\Grids;

use PSharp\Support\Geometry\Rect;
use PSharp\Support\Geometry\Point;
use PSharp\Console\Output\OutputFormatInterface;
use PSharp\Console\Console;
use PSharp\Console\Co;

/**
 *	Encapsulates a Table cell.
 *
 *	@author alarido <alarido.su@gmail.com>
 */
class TableCell
{
	private const IN_RECT = ['left','top','width','height','right','bottom'];
	private const IN_SELF = ['text','format','rect'];

	/**
	 *	@var \PSharp\Geometry\Rect $rect
	 */
	private $rect;

	/**
	 *	@var string $left
	 */
	private $text;

	/**
	 *	@var \PSharp\Console\Output\OutputFormatInterface $format
	 */
	private $format;

	/**
	 *	Creates a new TableCell instance
	 *
	 *	@param	\PSharp\Geometry\Rect	$rect
	 */
	public function __construct(string $text = null, Rect $rect = null)
	{
		$this->rect = $rect ?? Rect::new(0, 0, 0, 0);
		$this->text = $text ?? "";
	}

	/**
	 *	@property int $left
	 *	@property int $top
	 *	@property int $width
	 *	@property int $height
	 *	@property int $right
	 *	@property int $bottom
	 *	@property \PSharp\Geometry\Rect $rect
	 *	@property \PSharp\Console\Output\OutputFormatInterface $format
	 *	@property string $text
	 */
	public function __get(string $name)
	{
		if (in_array($name, self::IN_SELF, TRUE)) {
			return $this->$name;
		}
		if (in_array($name, self::IN_RECT, TRUE)) {
			return $this->rect->$name;
		}
	}

	/**
	 *	Defines a new text to the cell
	 *
	 *	@param	string	$text
	 *	@return	self
	 */
	public function setText(string $text)
	{
		$this->text = $text;
		return $this;
	}

	/**
	 *	Defines a new rectangle format to the cell
	 *
	 *	@param	\PSharp\Geometry\Rect	$rect
	 *	@return	self
	 */
	public function setRect(Rect $rect)
	{
		$this->rect = $rect;
		return $this;
	}

	/**
	 *	Defines a new format to the cell
	 *
	 *	@param	\PSharp\Console\Output\OutputFormatInterface	$format
	 *	@return	self
	 */
	public function setFormat(OutputFormatInterface $format)
	{
		$this->format = $format;
		return $this;
	}

	/**
	 *	Renders the cell
	 *
	 *	@return	self
	 */
	public function render(Point $origin)
	{
		$this->format->writeInRect(
			$this->text,
			$origin->x,
			$origin->y,
			$origin->x + $this->width,
			$origin->y + $this->height
		);
		return $this;
	}
}
<?php
namespace PSharp\Console\Output;

/**
 *	Outlines a basic set of methods for console output styling
 *
 *	@author alarido <alarido.su@gmail.com>
 */
interface OutputStylerInterface
{
	/**
	 *	Returns a style from its name, or the default one if not found.
	 *
	 *	@param	string	$name
	 *	@return	\PSharp\Console\Output\OutputFormat
	 */
	public function getStyle(string $name);

	/**
	 *	Sets a style from a OutputFormat instance or a tag.
	 *
	 *	@param	string	$name
	 *	@param	string|\PSharp\Console\Output\OutputFormat $outputStyle
	 *	@return	void
	 */
	public function setStyle(string $name, $outputStyle);

	/**
	 *	Outputs a text to the console. It accepts styler tags too.
	 *
	 *	@param	string	$content	text to be written
	 *	@return	void
	 */
	public function write(string $content);

	/**
	 *	Outputs a text to the console, plus a newline. It accepts styler tags too.
	 *
	 *	@param	string	$content	text to be written
	 *	@return	void
	 */
	public function writeln(string $content);
}
<?php
namespace PSharp\Console;

/**
 *	This encapsulates the desired behaviour of environment variables
 *	that may be kept along several command sessions and runnings.
 *	Now available (coming soon) at command initiator 
 *
 *	@author alarido <alarido.su@gmail.com>
 */
class Environment
{
	/**
	 *	@var array
	 */
	private $variables;

	/**
	 *	@var string
	 */
	private $fileName;

	/**
	 *	Parses the very straightforward-and-simple internal format
	 *	and fills the variables (again?)
	 *
	 *	@param	array	lines coming from, e.g., a file	
	 */
	protected function parse(array $lines)
	{
		foreach ($lines as $line)
		{
			$halves = explode(':', trim($line), 2);

			$this->variables[$halves[0]] = $halves[1] ?? '';
		}
	}

	/**
	 *	Retrieves all variables in several lines in the internal format
	 *	to be written to a file
	 *
	 *	@return	array	
	 */
	protected function unparse()
	{
		list($lines, $values) = array([], $this->variables);

		foreach ($values as $n => $v) {
			$lines[] = $n . ':' . $v;
		}

		return $lines;
	}

	/**
	 *	Instantiates a new Environment and loads previous variables
	 *
	 *	@param	string	$fileName
	 */
	public function __construct(string $fileName = null)
	{
		$this->fileName = $fileName ?? '';
		$this->variables = new Properties();

		$this->load();
	}

	/**
	 *	Saves all variables and terminates
	 *
	 *	@return void	
	 */
	public function __destruct()
	{
		$this->save();
	}

	/**
	 *	Loads from the file
	 *
	 *	@return	void
	 */
	public function load()
	{
		if (!empty($this->fileName))
		{
			$lines = file($this->fileName, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

			$this->parse($lines);
		}
	}

	/**
	 *	Saves to the file
	 *
	 *	@return	void
	 */
	public function save()
	{
		if (! empty($this->fileName))
		{
			$lines = $this->unparse();

			file_put_contents($this->fileName, $lines);
		}
	}

	/**
	 *	Sets a variable
	 *
	 *	@param	string	$name
	 *	@param	mixed	$value		
	 *	@return	void
	 */
	public function set(string $name, $value)
	{
		$this->variables[$name] = $value;
	}

	/**
	 *	Gets the variable, if any
	 *
	 *	@param	string	$name
	 *	@return	string|null
	 */
	public function get(string $name)
	{
		return $this->variables[$name];
	}

	/**
	 *	Returns the names set in the environment
	 *
	 *	@return	array
	 */
	public function names()
	{
		return array_keys($this->variables);
	}
}
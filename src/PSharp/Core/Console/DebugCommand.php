<?php
namespace Psharp\Core\Console;

use PSharp\Console\CommandBase;
use PSharp\Console\CommandLine;
use PSharp\Console\Signature;

/**
 * Debug command.
 */
#[Signature('debug {first?} {second?} {third?}')]
class DebugCommand extends CommandBase
{
	/**
	 * Command handler.
	 * 
	 * @param PSharp\Console\CommandLine $input
	 * @param mixed $first = null
	 * @param mixed $second = null
	 * @param mixed $third = null
	 * @return mixed
	 */
	public function handle(CommandLine $input, $first = null, $second = null, $third = null)
	{
		$this->line(sprintf('<fg=yellow>%s</>', compact('first','second','third','input')));

		return 0;
	}
}
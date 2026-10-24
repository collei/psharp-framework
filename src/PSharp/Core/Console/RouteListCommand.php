<?php
namespace Psharp\Core\Console;

use PSharp\Console\CommandBase;
use PSharp\Console\CommandLine;
use PSharp\Console\Signature;

/**
 * RouteList command.
 */
#[Signature('route-list {--D|detailed=false}')]
class RouteListCommand extends CommandBase
{
	/**
	 * Command handler.
	 * 
	 * @param PSharp\Console\CommandLine $input
	 * @param mixed $spec = null
	 * @return mixed
	 */
	public function handle(CommandLine $input)
	{
		print_r(compact('input'));
	}

	/**
	 * List available commands matching the given filter, or all if no filter.
	 * 
	 * @param ?string $filter = null
	 * @return void
	 */
	protected function list(?string $filter = null)
	{
		$list = $this->holder()->available($filter ?: null);

		if ($count = count($list)) {
			$this->line("<fg=yellow>-- Found $count commands:</>");

			foreach ($list as $item) {
				$this->line("<fg=yellow>\t* $item</>");
			}
		} else {
			$this->line("<fg=yellow>-- Found no corresponding commands.</>");
		}
	}
}
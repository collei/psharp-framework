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
	public function handle(CommandLine $input, $detailed = false)
	{
		$routes = app(\PSharp\Http\RouteMapper::class)->getEndpoints();

		$this->writeLine("<fg=green>-- Available routes:</>");
		$this->line('');

		foreach ($routes as $route) {
			$action = $route->getAction();
			$name = $route->getName();
			$endpoint = $route->asString();

			if ($detailed) {
				$this->writeLine("\t* <fg=yellow>{$endpoint}</>");
				$this->writeLine("\t\tname:<fg=green>\t{$name}</>");
				$this->writeLine("\t\taction:<fg=green>\t{$action}</>");
				$this->line('');
			} else {
				$this->writeLine("\t* <fg=yellow>{$endpoint}</> --> <fg=green>{$action}</>");
			}
		}
	}
}
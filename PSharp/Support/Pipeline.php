<?php
namespace PSharp\Support;

use Closure;
use Throwable;
use RuntimeException;

/**
 * Pipeline pattern for payload processing.
 *
 * Adapted from Laravel's Illuminate\Pipeline\Pipeline
 * @link https://laravel.com/api/8.x/Illuminate/Pipeline/Pipeline.html
 *
 */
class Pipeline
{
	/**
	 * The object being passed through the pipeline.
	 *
	 * @var mixed
	 */
	protected $passable;

	/**
	 * The array of class pipes.
	 *
	 * @var array
	 */
	protected $pipes = [];

	/**
	 * The method to call on each pipe.
	 *
	 * @var string
	 */
	protected $method = 'handle';

	/**
	 * Set the object being sent through the pipeline.
	 *
	 * @param  mixed  $passable
	 * @return $this
	 */
	public function send($passable)
	{
		$this->passable = $passable;
		//
		return $this;
	}

	/**
	 * Add several pipes after removing any existing.
	 *
	 * @param  array|mixed  $pipes
	 * @return $this
	 */
	public function through($pipes)
	{
		$pipes = is_array($pipes) ? $pipes : func_get_args();

		$this->pipes = [];

		foreach ($pipes as $pipe) {
			$this->pipe($pipe);
		}

		return $this;
	}

	/**
	 * Add a single pipe.
	 *
	 * @param  Closure|object  $pipe
	 * @return $this
	 */
	public function pipe($pipe)
	{
		$this->pipes[] = $pipe;

		return $this;
	}

	/**
	 * Set the method to call on the pipes.
	 *
	 * @param  string  $method
	 * @return $this
	 */
	public function via($method)
	{
		$this->method = $method;
		//
		return $this;
	}

	/**
	 * Run the pipeline with a final destination callback.
	 *
	 * @param  \Closure  $destination
	 * @return mixed
	 */
	public function then(Closure $destination)
	{
		$pipeline = array_reduce(
			array_reverse($this->pipes()), $this->carry(), $this->prepareDestination($destination)
		);

		return $pipeline($this->passable);
	}

	/**
	 * Run the pipeline and return the result.
	 *
	 * @return mixed
	 */
	public function thenReturn()
	{
		return $this->then(function ($passable) {
			return $passable;
		});
	}

	/**
	 * Get the final piece of the Closure onion.
	 *
	 * @param  \Closure  $destination
	 * @return \Closure
	 */
	protected function prepareDestination(Closure $destination)
	{
		return function ($passable) use ($destination) {
			try {
				return $destination($passable);
			}
			catch (Throwable $e) {
				return $this->handleException($passable, $e);
			}
		};
	}

	/**
	 * Get a Closure that represents a slice of the application onion.
	 *
	 * @return \Closure
	 */
	protected function carry()
	{
		return function ($stack, $pipe) {
			return function ($passable) use ($stack, $pipe) {
				try {
					// The pipe is a callable, let us call it.
					if (is_callable($pipe)) {
						return $pipe($passable, $stack);
					} 
					// Neither a callable, nor an object
					// So we craft a callable to silently ignore it
					elseif (! is_object($pipe)) {
						$pipe = function($payload, $next) {
							return $next($payload);
						};
						
						return $pipe($passable, $stack);
					}

					// If we reached here, it means
					// The pipe is a object
					$parameters = [$passable, $stack];
					
					$carry = method_exists($pipe, $this->method)
							? $pipe->{$this->method}(...$parameters)
							: $pipe(...$parameters);

					return $this->handleCarry($carry);
				}
				catch (Throwable $e) {
					return $this->handleException($passable, $e);
				}
			};
		};
	}

	/**
	 * Get the array of configured pipes.
	 *
	 * @return array
	 */
	protected function pipes()
	{
		return $this->pipes;
	}

	/**
	 * Handle the value returned from each pipe before passing it to the next.
	 *
	 * @param  mixed  $carry
	 * @return mixed
	 */
	protected function handleCarry($carry)
	{
		return $carry;
	}

	/**
	 * Handle the given exception.
	 *
	 * @param  mixed  $passable
	 * @param  \Throwable  $e
	 * @return mixed
	 *
	 * @throws \Throwable
	 */
	protected function handleException($passable, Throwable $e)
	{
		throw $e;
	}
}
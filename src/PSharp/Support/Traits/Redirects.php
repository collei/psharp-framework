<?php
namespace PSharp\Support\Traits;

/**
 * Provides capabilities for working with HTTP redirection.
 */
trait Redirects
{
	/**
	 * The path the user should be redirected to.
	 *
	 * @var string
	 */
	protected $redirectTo;

	/**
	 * The message for the user about the redirection.
	 *
	 * @var string
	 */
	protected $redirectMessage;

	/**
	 * Set the path the user should be redirected to.
	 *
	 * @param string $to
	 * @return $this
	 */
	public function setRedirectTo($to)
	{
		if (! empty($to)) {
			$this->redirectTo = $to;
		}
		//
		return $this;
	}

	/**
	 * Set the message displayed to the user while redirecting.
	 *
	 * @param string $redirect
	 * @return $this
	 */
	public function setRedirectMessage($message)
	{
		$this->redirectMessage = $message;
		//
		return $this;
	}

	/**
	 * Get the path the user should be redirected to.
	 *
	 * @return string
	 */
	public function redirectTo()
	{
		return $this->redirectTo;
	}

	/**
	 * Get the message displayed to the user while redirecting.
	 *
	 * @return string
	 */
	public function redirectMessage()
	{
		return $this->redirectMessage;
	}
}
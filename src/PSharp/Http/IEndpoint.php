<?php
namespace PSharp\Http;

/**
 * Base class for route endpoints
 */
interface IEndpoint
{
	/**
	 * Obtains the action from this endpoint.
	 * 
	 * @return string|null
	 */
	public function getAction();

	/**
	 * Return the full path of this endpoint.
	 * 
	 * @return string
	 */
	public function getPath();

	/**
	 * Return the full name of this endpoint.
	 * 
	 * @return string
	 */
	public function getName();

	/**
	 * Return the HTTP method of this endpoint.
	 * 
	 * @return string
	 */
	public function getMethod();

	/**
	 * Return the method and full path of this endpoint as string.
	 * 
	 * @return string
	 */
	public function asString();
}
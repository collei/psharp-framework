<?php
namespace PSharp\Auth;

/**
 * Holds an user repository, i.e., the data storage in which information
 * about users lives.
 */
interface UserRepositoryInterface
{
	/**
	 * Retrieves an user by $id 
	 *
	 * @param mixed $id
	 * @return \PSharp\Auth\Authenticatable|null
	 */
	public function retrieveById($id);

	/**
	 * Retrieves an user that matches the given associative array.
	 * Keys must holder the field names and values must hold the
	 * desired values to check against. 
	 *
	 * @param array $fields
	 * @return \PSharp\Auth\Authenticatable|null
	 */
	public function retrieveByFields(array $fields);

	/**
	 * Retrieves an user by $token 
	 *
	 * @param mixed $id
	 * @param string $token
	 * @return \PSharp\Auth\Authenticatable|null
	 */
	public function retrieveByToken($id, string $token);

	/**
	 * Updates the $user's $token. 
	 *
	 * @param \PSharp\Auth\Authenticatable $user
	 * @param string $token
	 * @return void
	 */
	public function updateToken(Authenticatable $user, string $token);

	/**
	 * Retrieves an user by $credentials
	 *
	 * @param array $credentials
	 * @return \PSharp\Auth\Authenticatable
	 */
	public function retrieveByCredentials(array $credentials);

	/**
	 * Validates $user against $credentials 
	 *
	 * @param \PSharp\Auth\Authenticatable $user
	 * @param array $credentials
	 * @return bool
	 */
	public function validateCredentials(Authenticatable $user, array $credentials);

	/**
	 * Retrieves a reference to the authentication manager.
	 *
	 * @return \Zelatus\Interfaces\Auth\AuthManager
	 */
	public function getManager();
}
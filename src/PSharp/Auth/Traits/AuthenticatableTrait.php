<?php
namespace PSharp\Auth\Traits;

/**
 * Trait to implement Authenticatable interface.
 */
trait AuthenticatableTrait
{
	/**
	 * @var string
	 */
	protected $identifier_field = 'id';

	/**
	 * @var string
	 */
	protected $username_field = 'username';

	/**
	 * @var string
	 */
	protected $password_field = 'password';

	/**
	 * @var string
	 */
	protected $token_field = 'token';

	/**
	 * Retrieves the user identifier field value.
	 *
	 * @return	mixed
	 */
	public function getUserID()
	{
		$field = $this->getUserIDField();
		//
		return $this->$field;
	}

	/**
	 * Retrieves the user identifier field name.
	 *
	 * @return	string
	 */
	public function getUserIDField()
	{
		return $this->identifier_field;
	}

	/**
	 * Retrieves the username value.
	 *
	 * @return	mixed
	 */
	public function getUserName()
	{
		$field = $this->getUserNameField();

		return $this->$field;
	}

	/**
	 * Retrieves the username field name.
	 *
	 * @return	string
	 */
	public function getUserNameField()
	{
		return $this->username_field;
	}

	/**
	 * Retrieves the user password.
	 *
	 * @return	string
	 */
	public function getUserPassword()
	{
		$field = $this->getUserPasswordField();

		return $this->$field;
	}

	/**
	 * Retrieves the user password field name.
	 *
	 * @return	string
	 */
	public function getUserPasswordField()
	{
		return $this->password_field;
	}

	/**
	 * Retrieves the user remember token field value.
	 *
	 * @return	string
	 */
	public function getUserToken()
	{
		$field = $this->getUserTokenField();

		return $this->$field;
	}

	/**
	 * Retrieves the user remember token field name.
	 *
	 * @return	string
	 */
	public function getUserTokenField()
	{
		return $this->token_field;
	}

	/**
	 * Defines the user remember token field value.
	 *
	 * @return	string
	 */
	public function setUserToken(string $token = null)
	{
		$this->Token = $token;
	}
}
<?php
namespace PSharp\Auth\Traits;

/**
 * User trait with basic fields.
 *
 */
trait UserTrait
{
	/**
	 * @var int|string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $username;

	/**
	 * @var string
	 */
	protected $password;

	/**
	 * @var string
	 */
	protected $token;

	/**
	 * Serializes data.
	 *
	 * @return array
	 */
	public function __serialize(): array
	{
		return [
			'id' => $this->id,
			'username' => $this->username,
			'password' => $this->password,
			'token' => $this->token,
		];
	}

	/**
	 * Unserializes data.
	 *
	 * @param array $data
	 * @return void
	 */
	public function __unserialize(array $data)
	{
		$this->id = $data['id'];
		$this->username = $data['username'];
		$this->password = $data['password'];
		$this->token = $data['token'];
	}

	/**
	 * Retrieves the field values.
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if (in_array($name, ['id','username','password','token'])) {
			return $this->$name;
		}
	}

	/**
	 * Sets the field values.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return $this
	 */
	public function set($name, $value)
	{
		if (in_array($name, ['username','password','token'])) {
			$this->$name = $value;
		}
		//
		return $this;
	}
}
<?php
namespace PSharp\Auth;

/**
 *	Embodies authorization capabilities.
 */
class Guard
{
	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @var \PSharp\Auth\Authenticatable
	 */
	protected $user;

	/**
	 * @var \PSharp\Auth\UserRepositoryInterface
	 */
	protected $repository;

	/**
	 * Instantiate me.
	 *
	 * @param string $name
	 * @param \PSharp\Auth\UserRepositoryInterface $repository
	 * @return void
	 */
	public function __construct($name, UserRepositoryInterface $repository)
	{
		$this->name = $name;
		$this->repository = $repository;
	}

	/**
	 * Returns user name.
	 *
	 * @return string
	 */
	public function name()
	{
		return $this->name;
	}

	/**
	 * Returns whether this user is logged.
	 *
	 * @return bool
	 */
	public function check()
	{
		return !is_null($this->user());
	}

	/**
	 * Returns whether this user is not logged.
	 *
	 * @return bool
	 */
	public function guest()
	{
		return !$this->check();
	}

	/**
	 * Retrieves the user instance.
	 *
	 * @return \PSharp\Auth\Authenticatable
	 */
	public function user()
	{
		return $this->user;
	}

	/**
	 * Retrieves the user's id.
	 *
	 * @return int|string|null
	 */
	public function id()
	{
		if ($user = $this->user()) {
			return $user->getAuthIdentifier();
		}
	}

	/**
	 * Validates the user against $credentials.
	 *
	 * @return bool
	 */
	public function validate(array $credentials = [])
	{
		if ($user = $this->user()) {
			return $this->repository->validateCredentials($user, $credentials);
		}
		//
		return false;
	}

	/**
	 * Sets the user instance.
	 *
	 * @param \PSharp\Auth\Authenticatable $user
	 * @return void
	 */
	public function setUser(Authenticatable $user)
	{
		$this->user = $user;
	}

	/**
	 * Logon the user on this guard.
	 *
	 * @param \PSharp\Auth\Authenticatable $user
	 * @return void
	 */
	public function logon(Authenticatable $user)
	{
		$this->setUser($user);
	}

	/**
	 * Logoff this guard.
	 *
	 * @return void
	 */
	public function logoff()
	{
		$this->user = null;
	}

    /**
     * Retrieves info for the internal PHP functions.
     * 
     * @return array
     */
    public function __debugInfo()
	{
		return [
			'name' => $this->name,
			'user' => get_instance_id($this->user),
			'repository' => get_instance_id($this->repository),
		];
	}
}
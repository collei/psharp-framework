<?php
namespace PSharp\Auth\Repositories;

use PSharp\Core\Application;
use PSharp\Auth\AuthManager;
use PSharp\Auth\UserRepositoryInterface;
use PSharp\Auth\Authenticatable;
use PSharp\Http\Sessions\Session;

/**
 * User repository from file.
 */
class SessionRepository implements UserRepositoryInterface
{
    /**
     * @var array
     */
    protected $userInstance = null;

    /**
     * @var array
     */
    protected $session = null;

    /**
     * Initialize the instance.
     * 
     * @param PSharp\Core\Application $application
     * @param PSharp\Auth\AuthManager $manager
     */
    public function __construct(protected Application $application, protected AuthManager $manager)
    {
		$this->session = Session::getInstance();

        $this->save();
    }

    /**
     * Loads from file.
     * 
     * @return void
     */
    public function user()
    {
        if ($this->userInstance) {
            return $this->userInstance;
        }

        $id = $this->session->get('user_id', null);

		$username = $this->session->get('username', null)
            ?? $_SERVER['AUTH_USER']
            ?? $_SERVER['LOGON_USER']
            ?? $_SERVER['REMOTE_USER']
            ?? 'ANONYMOUS';

        if (strpos($username, '\\') !== false) {
			list($domain, $username) = explode('\\', $username, 2);
		}

        return $this->userInstance = new User(
			$id, $username, null, $this->session->token()
		);
    }

    /**
     * Saves to file.
     * 
     * @return void;
     */
    protected function save()
    {
        if ($user = $this->user()) {
            $this->session->set('user_id', $user->getUserID());
            $this->session->set('username', $user->getUserName());
        }
    }

	/**
	 * Retrieves an user by $id 
	 *
	 * @param	mixed	$id
	 * @return	\PSharp\Auth\Authenticatable|null
	 */
	public function retrieveById($id)
    {
        return $this->user();
    }

	/**
	 * Retrieves an user that matches the given associative array.
	 * Keys must holder the field names and values must hold the
	 * desired values to check against. 
	 *
	 * @param	array	$fields
	 * @return	\PSharp\Auth\Authenticatable|null
	 */
	public function retrieveByFields(array $fields)
    {
        return $this->user();
    }

	/**
	 * Retrieves an user by $token 
	 *
	 * @param	mixed	$id
	 * @param	string	$token
	 * @return	\PSharp\Auth\Authenticatable|null
	 */
	public function retrieveByToken($id, string $token)
    {
        return $this->user();
    }

	/**
	 * Updates the $user's $token. 
	 *
	 * @param	\PSharp\Auth\Authenticatable	$user
	 * @param	string	$token
	 * @return	void
	 */
	public function updateToken(Authenticatable $user, string $token)
    {
        $this->user()->setUserToken($token);

        $this->save();
    }

	/**
	 * Retrieves an user by $credentials
	 *
	 * @param	array	$credentials
	 * @return	\PSharp\Auth\Authenticatable
	 */
	public function retrieveByCredentials(array $credentials)
    {
        return null;
    }

	/**
	 * Validates $user against $credentials 
	 *
	 * @param	\PSharp\Auth\Authenticatable	$user
	 * @param	array	$credentials
	 * @return	bool
	 */
	public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $user = $this->user();

        $matched = ($user->getUserName() == ($credentials['username'] ?? null))
                && ($user->getUserPassword() == ($credentials['password'] ?? null));

        return $matched || ($user->getUserToken() == ($credentials['token'] ?? null));
    }

	/**
	 * Retrieves a reference to the authentication manager.
	 *
	 * @return	\Zelatus\Interfaces\Auth\AuthManager
	 */
	public function getManager()
    {
        return $this->manager;
    }

    /**
     * Retrieves info for the internal PHP functions.
     * 
     * @return array
     */
    public function __debugInfo()
	{
		return [
			'application' => get_instance_id($this->application),
			'manager' => get_instance_id($this->manager),
			'userInstance' => $this->userInstance,
			'session' => $this->session,
		];
	}
}
<?php
namespace PSharp\Auth\Repositories;

use PSharp\Core\Application;
use PSharp\Auth\AuthManager;
use PSharp\Auth\UserRepositoryInterface;
use PSharp\Auth\Authenticatable;

/**
 * User repository from file.
 */
class FileRepository implements UserRepositoryInterface
{
    /**
     * @var array
     */
    protected $users = [];

    /**
     * Initialize the instance.
     * 
     * @param PSharp\Core\Application $application
     * @param PSharp\Auth\AuthManager $manager
     * @param string $path
     */
    public function __construct(protected Application $application, protected AuthManager $manager, protected string $path)
    {
        $this->path = $this->application->path($path);
    }

    /**
     * Loads from file.
     * 
     * @return void
     */
    protected function load()
    {
        if ($data = file_get_contents($this->path)) {
            $users = unserialize($data);
            
            if ($users !== false) {
                $this->users = $users;
            }
        }
    }

    /**
     * Saves to file.
     * 
     * @return void;
     */
    protected function save()
    {
        $data = serialize(($this->users));

        file_put_contents($this->path, $data);
    }

    /**
     * Retrieves the file path.
     * 
     * @return string
     */
    public function getFilePath()
    {
        return $this->path;
    }

	/**
	 * Retrieves an user by $id 
	 *
	 * @param mixed $id
	 * @return \PSharp\Auth\Authenticatable|null
	 */
	public function retrieveById($id)
    {
        return $this->users[$id] ?? null;
    }

	/**
	 * Retrieves an user that matches the given associative array.
	 * Keys must holder the field names and values must hold the
	 * desired values to check against. 
	 *
	 * @param array $fields
	 * @return \PSharp\Auth\Authenticatable|null
	 */
	public function retrieveByFields(array $fields)
    {
		foreach ($this->users as $user) {
			$yes = true;
			//
			foreach ($fields as $field => $value) {
                if ($user->getUserIDField() == $field)
    				$yes = $yes && ($user->getUserID() == $value);
                elseif ($user->getUserNameField() == $field)
    				$yes = $yes && ($user->getUserName() == $value);
                elseif ($user->getUserPasswordField() == $field)
    				$yes = $yes && ($user->getUserPassword() == $value);
                elseif ($user->getUserTokenField() == $field)
    				$yes = $yes && ($user->getUserToken() == $value);
			}
			//
			if ($yes) {
				return $user;
			}
		}
		//
		return null;
    }

	/**
	 * Retrieves an user by $token 
	 *
	 * @param mixed $id
	 * @param string $token
	 * @return \PSharp\Auth\Authenticatable|null
	 */
	public function retrieveByToken($id, string $token)
    {
		foreach ($this->users as $user) {
			if ($user->getUserID() == $id && $user->getUserToken() == $token) {
				return $user;
			}
		}
		//
		return null;
    }

	/**
	 * Updates the $user's $token. 
	 *
	 * @param \PSharp\Auth\Authenticatable $user
	 * @param string $token
	 * @return void
	 */
	public function updateToken(Authenticatable $user, string $token)
    {
		foreach ($this->users as $key => $repoUser) {
			if ($repoUser->getUserID() == $user->getUserID()) {
				$this->users[$key]->setUserToken($token);

                $this->save();

                break;
			}
		}
    }

	/**
	 * Retrieves an user by $credentials
	 *
	 * @param array $credentials
	 * @return \PSharp\Auth\Authenticatable
	 */
	public function retrieveByCredentials(array $credentials)
    {
        return null;
    }

	/**
	 * Validates $user against $credentials 
	 *
	 * @param \PSharp\Auth\Authenticatable $user
	 * @param array $credentials
	 * @return bool
	 */
	public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $matched = ($repoUser->getUserName() == ($credentials['username'] ?? null))
                && ($repoUser->getUserPassword() == ($credentials['password'] ?? null));

        return $matched || ($repoUser->getUserToken() == ($credentials['token'] ?? null));
    }

	/**
	 * Retrieves a reference to the authentication manager.
	 *
	 * @return \Zelatus\Interfaces\Auth\AuthManager
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
			'path' => $this->path,
			'users' => $this->users,
		];
	}
}
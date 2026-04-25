<?php
namespace PSharp\Auth;

use PSharp\Core\{Application, Config};
use PSharp\Support\{Arr, Str};
use PSharp\DB\DatabaseManager;
use Stringable;
use DateTime;
use InvalidArgumentException;
use RuntimeException;

/**
 * Authentication and Authorization management.
 */
class AuthManager
{
    /**
     * @var PSharp\Core\Application
     */
    protected $application;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var PSharp\Auth\UserRepositoryInterface
     */
    protected $repository = null;

    /**
     * @var array
     */
    protected $drivers = [];

    /**
     * @var array
     */
    protected $guards = [];

    /**
     * @var string
     */
    protected $defaultGuard = null;

    /**
     * @var string
     */
    protected $guestGuard = null;

    /**
     * Authentication and authorization manager.
     * 
     * @param PSharp\Core\Application
     * @param PSharp\Core\Config
     */
    public function __construct(Application $application, Config $config)
    {
        $this->application = $application;
        $this->config = $config->get('auth');

        $this->initialize();
    }

    /**
     * Performs manager initialization.
     * 
     * @return void
     * @throws RuntimeException
     * @throws InvalidArgumentException
     */
    protected function initialize()
    {
        $this->loadDrivers();
        $this->initializeRepositories();
        $this->initializeGuards();
    }

    /**
     * Loads drivers.
     * 
     * @return void
     */
    protected function loadDrivers()
    {
        if ($drivers = $this->config['drivers']) {
            $this->drivers = $drivers;
        }
    } 

    /**
     * Initialize the configured default repository.
     * 
     * @return void
     * @throws RuntimeException if no repository configured in 'appsettings.json'
     */
    protected function initializeRepositories()
    {
        $default = $this->config['default']['repository'] ?? 'file';
        $found = false;
        $driver = '[none]';

        foreach ($this->config['repositories'] as $name => $conf) {
            $found = ($default == $name);
            
            if ($found) {
                $driver = $conf['driver'] ?? null;
                $this->repository = $this->initializeRepository($default, $conf);
                break;
            }
        }

        if (! $found) {
            throw new RuntimeException(sprintf('Auth user repository not found: %s', $default));
        }

        if (empty($this->repository)) {
            throw new RuntimeException(sprintf('Missing Auth user repository driver: %s', $driver));
        }
    }

    /**
     * Initialize the given file repository.
     * 
     * @param string $name
     * @param array $conf
     * @return PSharp\Auth\UserRepositoryInterface
     * @throws RuntimeException
     * @throws InvalidArgumentException for any missing auth driver 
     */
    protected function initializeRepository(string $name, array $conf)
    {
        if (! isset($conf['driver'])) {
            throw new InvalidArgumentException(sprintf('Auth driver not defined in the "%s" repository', $name));
        }

        $driverName = $conf['driver'];

        if ($driver = $this->drivers[$driverName] ?? null) {
            switch ($driverName) {
                case 'database':
                    return $this->createDatabaseRepository($driver, $conf);
                case 'orm':
                    return null;
                case 'file':
                    return $this->createFileRepository($driver, $conf);
                case 'session':
                    return $this->createSessionRepository($driver, $conf);
                case 'ldap':
                    return null;
            }
        }

        return null;
    }

    /**
     * Retrieves the application instance.
     * 
     * @return PSharp\Core\Application
     */
    public function getApplication()
    {
        return $this->application;
    }

    /**
     * Retrieves the repository.
     * 
     * @return PSharp\Auth\UserRepositoryInterface
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Create the user database repository.
     * 
     * @param array $driver
     * @param array $conf
     * @return PSharp\Auth\UserRepositoryInterface
     * @throws RuntimeException for missing connection details.
     */
    protected function createDatabaseRepository(array $driver, array $conf)
    {
        $connection = $conf['connection'] ?? null;
        $table = $conf['table'] ?? null;
        $fields = $conf['fields'] ?? null;

        $database = $this->application->container->make(DatabaseManager::class);

        if (empty($database)) {
            throw new RuntimeException('No database manager configured.');
        }

        if ($connection && $table && !empty($fields)) {
            if ($class = $driver['repository'] ?? null) {
                return new $class($this->application, $this, $database, $connection, $table, $fields);
            }

            throw new RuntimeException('Missing driver class for the auth driver \'database\'');
        }

        throw new RuntimeException('Missing connection, table name or fields for auth driver \'database\'');
    }

    /**
     * Create the user file repository.
     * 
     * @param array $driver
     * @param array $conf
     * @return PSharp\Auth\UserRepositoryInterface
     * @throws RuntimeException for missing file path.
     */
    protected function createFileRepository(array $driver, array $conf)
    {
        if ($filePath = $conf['path'] ?? null) {
            if ($class = $driver['repository'] ?? null) {
                return new $class($this->application, $this, $filePath);
            }

            throw new RuntimeException('Missing driver class for the auth driver \'file\'');
        }

        throw new RuntimeException('Missing file path for the auth driver \'file\'');
    }

    /**
     * Create the user session repository.
     * 
     * @param array $driver
     * @param array $conf
     * @return PSharp\Auth\UserRepositoryInterface
     * @throws RuntimeException
     */
    protected function createSessionRepository(array $driver, array $conf)
    {
        if ($class = $driver['repository'] ?? null) {
            return new $class($this->application, $this);
        }

        throw new RuntimeException('Missing driver class for the auth driver \'session\'');
    }

    /**
     * Initialize all guards specified in 'appsettings.json'.
     * 
     * @return void
     */
    protected function initializeGuards()
    {
        $this->defaultGuard = $this->config['default']['guard'] ?? 'user';

        foreach ($this->config['guards'] as $name) {
            $this->guards[$name] = new Guard($name, $this->repository);
        }

        $this->initializeGuest();
    }

    /**
     * Initialize a guest guard as specified in 'appsettings.json'.
     * 
     * @return void
     */
    protected function initializeGuest()
    {
        $this->guestGuard = $this->config['default']['guest'] ?? 'guest';

        if (! array_key_exists($name = $this->guestGuard, $this->guards)) {
            $this->guards[$name] = new Guard($name, $this->repository);
        }
    }

    /**
     * Retrieves the given guard by its name, if any.
     * May retrieve the default guard if configured.
     * 
     * @param string $name = null
     * @return PSharp\Auth\Guard|null
     */
    public function guard(string $name = null)
    {
        if ($name) if ($guard = $this->guards[$name] ?? null) {
            return $guard;
        }

        if ($this->defaultGuard) if ($guard = $this->guards[$this->defaultGuard] ?? null) {
            return $guard;
        } 

        return null;
    }

    /**
     * Retrieves the existing guards.
     * 
     * @return array
     */
    public function guards()
    {
        return $guards = $this->guards; 
    }

    /**
     * Retrieves the guest guard, if any.
     * 
     * @return PSharp\Auth\Guard|null
     */
    public function guest()
    {
        return $this->guards[$this->guestName()] ?? null;
    }

    /**
     * Tells if the given guard is a guest one.
     * 
     * @param string $name
     * @return bool
     */
    public function isGuest(string $name)
    {
        return $name == $this->guestName();
    }

    /**
     * Retrieves the guest guard name, if any.
     * 
     * @return string|null
     */
    public function guestName()
    {
        return $this->guestGuard;
    }

    /**
     * Logoff the given guard.
     * 
     * @param string $name
     * @param PSharp\Auth\Authenticatable $user
     * @return void
     */
    public function logon(string $name, Authenticatable $user)
    {
        if ($guard = $this->guard($name)) {
            $guard->logon($user);
        }
    }

    /**
     * Logoff the given guard.
     * 
     * @param string $name
     * @return void
     */
    public function logoff(string $name)
    {
        if ($guard = $this->guard($name)) {
            $guard->logoff();
        }
    }

	/**
	 * Returns true if at least one of the given guards' check() -- but
	 * NOT including the default guard -- returns true,
	 * false otherwise.
	 *
	 * @param string|null ...$names
	 * @return bool
	 */
	public function authorizes(...$names)
	{
		foreach ($names as $name) {
			if (! is_string($name)) continue;

			if ($guard = $this->guards[$name] ?? null) if ($guard->check()) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns true if at least one of the given guards' check() -- including
	 * the default guard -- returns true,
	 * false otherwise.
	 *
	 * @param string|null ...$names
	 * @return bool
	 */
	public function authorizesWithDefault(...$names)
	{
		foreach ($names as $name) {
			if (! is_string($name)) continue;

			if ($guard = $this->guards[$name] ?? null) if ($guard->check()) {
				return true;
			}
		}

		return $this->resolveGuard(null)->check();
	}

    /**
     * Retruns true if the current user possesses the given attribute
     * with one of the given values, false if no logged or no value is found.
     * 
     * @param string $attribute
     * @param mixed ...$values
     * @return bool
     */
    public function userHas(string $attribute, ...$values)
    {
        if ($user = $this->guard()->user()) {
            if ($original = $user->$attribute ?? null) {
                foreach ($values as $value) if ($value == $original) {
                    return true;
                }
            }
        }

        return false;
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
			'repository' => $this->repository,
			//'repository' => get_instance_id($this->repository),
            'defaultGuard' => $this->defaultGuard,
            'guestGuard' => $this->guestGuard,
			'guards' => $this->guards,
			'drivers' => $this->drivers,
			'config' => $this->config,
		];
	}
}
<?php
namespace PSharp\DB;

use PSharp\Core\{Application, Config};
use PSharp\Support\{Arr, Str};
use PSharp\DB\Connections\Connection;
use Stringable;
use DateTime;
use InvalidArgumentException;
use RuntimeException;

/**
 * Authentication and Authorization management.
 */
class DatabaseManager
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
     * @var string
     */
    protected $defaultConnection = null;

    /**
     * @var array
     */
    protected $connections = [];

    /**
     * Database manager.
     * 
     * @param PSharp\Core\Application
     * @param PSharp\Core\Config
     */
    public function __construct(Application $application, Config $config)
    {
        $this->application = $application;
        $this->config = $config->get('db');

        $this->initialize();
    }

    /**
     * Retrieves a connection by its name, or the default, if any.
     * 
     * @param string $name = null
     * @return PSharp\DB\Connections\Connection|null
     */
    public function connection(string $name = null)
    {
        if (isset($this->connections[$name])) {
            return $this->connections[$name];
        }

        if (isset($this->connections[$this->defaultConnection])) {
            return $this->connections[$this->defaultConnection];
        }

        return null;
    }

    /**
     * Initialize the database manager.
     * 
     * @return void
     */
    protected function initialize()
    {
        foreach ($this->config['connections'] as $name => $conf) {
            if (is_string($conf)) {
                $this->defaultConnection = $conf;
                continue;
            }

            if ($connection = $this->createConnection($name, $conf)) {
                $this->connections[$name] = $connection;
            }
        }

        if (empty($this->connections)) {
            throw new RuntimeException('No available database connections found!');
        }
    }

    /**
     * Create the connection with the configuration parameters.
     * 
     * @param string $name
     * @param array $conf
     * @return PSharp\Database\Connections\Connection|null
     */
    protected function createConnection(string $name, array $conf)
    {
        if (! isset($conf['type'])) {
            throw new InvalidArgumentException(sprintf('DB connection type (e.g., "mysql", "sqlsrv" etc) not defined in the connection "%s"', $name));
        }

        list($dsn, $type, $server, $database, $user, $pass, $port, $charset)
            = Arr::select($conf, 'dsn', 'type', 'server', 'database', 'username', 'password', 'port', 'charset');

        if (Connection::isSupportedType($type)) {
            if (empty($dsn)) {
                $dsn = Connection::buildDsn($type, $server, $database, $user, $pass, $port, $charset);
            }

            return new Connection($type, $dsn, $database, $user, $pass);
        }

        return null;
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
			'config' => $this->config,
			'defaultConnection' => $this->defaultConnection,
			'connections' => $this->connections,
		];
	}
}
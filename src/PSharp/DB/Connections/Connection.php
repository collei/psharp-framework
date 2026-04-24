<?php
namespace PSharp\DB\Connections;

use PDO;
use PDOException;
use DateTime;
use PSharp\DB\DatabaseException;
use PSharp\Support\{Arr, Str};
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Connection
{
    /**
     * @var array
     */
    protected const DB_TYPES = [
        'mysql' => ['mysql','mariadb'],
        'pgsql' => ['pgsql','postgres','postgresql'],
        'oci' => ['oci','oracle'],
        'sqlsrv' => ['sqlsrv','mssql'],
        'sqlite' => ['sqlite','sqlite3'],
    ];

    /**
     * @var array
     */
    protected const DB_STANDARD_PORTS = [
        'mysql' => 3306,
        'pgsql' => 5432,
        'oci' => 1521,
        'sqlsrv' => 1433,
        'sqlite' => null,
    ];

    /**
     * @var array
     */
    protected const DB_DSNS = [
        'mysql' => 'mysql:host={server};dbname={database};port={port};charset={charset}',
        'pgsql' => 'pgsql:host={server};port={port};dbname={database};user={username};password={password}',
        'oci' => 'oci:dbname={server}',
        'sqlsrv' => 'sqlsrv:server={server};database={database}',
        'sqlite' => 'sqlite:{database}',
    ];

    /**
     * @var string
     */
    private $type = null;

    /**
     * @var resource
     */
    private $handle = null;

    /**
     * @var bool
     */
    private $isOpen = false;

    /**
     * @var array
     */
    private $errors = [];

    /**
     * @var Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * @var array
     */
    private static $connectionPool = [];

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $dsn;

    /**
     * @var string
     */
    protected $database;

    /**
     * @var string
     */
    protected $username;

    /**
     * @var string
     */
    protected $password;

    /**
     * @var array
     */
    protected $prepared = [];

    /**
	 * @var array
	 */
	protected $options = [
		PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_EMULATE_PREPARES => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_PERSISTENT => false, // evitar conexões que remanesçam após o término do ciclo
	];

	/**
	 * Initializes a new instance
	 *
	 * @param string $type
	 * @param mixed $dsn
	 * @param string $database
	 * @param string $username
	 * @param string $password
	 */
	public function __construct(string $type, $dsn = '', string $database = '', string $username = '', string $password = '')
	{
		$this->type = $this->getSupportedType($type);

		$this->dsn = $dsn;
		$this->database = $database;
		$this->username = $username;
		$this->password = $password;

        $this->initialize();

        $this->name = $name = 'DBC' . (new DateTime())->format('YmdHisu');

        $this->open();

        self::$connectionPool[$name] = $this;
	}

    /**
     * Builds a DSN string from parameters ,according to the supported vendors.
     * Returns empty string if vendor is not supported.
     * 
     * @static
     * @param string $vendor
     * @param string $server = ''
     * @param string $database = ''
     * @param string $username = ''
     * @param string $password = ''
     * @param int $port = 0
     * @param string|null $charset = null
     * @return string
     */
    public static function buildDsn(
        string $vendor,
        string $server = null,
        string $database = null,
        string $username = null,
        string $password = null,
        int $port = null,
        string $charset = null
    ) {
        if ($type = static::getSupportedType($vendor)) {
            $port = ($port > 0) ? $port : static::DB_STANDARD_PORTS[$type];
            $charset = is_null($charset) ? 'utf8' : $charset;

            $parameters = compact('server','database','port','charset','username','password');

            return Str::replaceVariables(static::DB_DSNS[$type], $parameters);
        }

        return '';
    }

    /**
     * Retrieves the database type;
     * 
     * @return string|null
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * Tells if the connection is of a given type/vendor.
     * 
     * @param string|null $type
     * @return bool
     */
    public function isType(string $type = null)
    {
        if (empty($type)) {
            return false;
        }

        if ($type == $this->type) {
            return true;
        }

        return static::getSupportedType($type) == $this->type;
    }

    /**
     * Returns the standardized type for the given DB type/vendor, if supported,
     * and according to the class definition.
     * 
     * @static
     * @param string $type
     * @return string|null
     */
    public static function getSupportedType(string $type)
    {
        foreach (self::DB_TYPES as $key => $possible) if (in_array($type, $possible, true)) {
            return $key;
        }

        return null;
    }

    /**
     * Tells if the given type/vendor is internally supported.
     * 
     * @static
     * @param string $type
     * @return bool
     */
    public static function isSupportedType(string $type)
    {
        $resultType = static::getSupportedType($type);

        return ! empty($resultType);
    }

    /**
     * Provides a PSR-3 logger for logging erros to.
     * 
     * @param Psr\Log\LoggerInterface $logger
     */
    public function withLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Returns the logger associated to the connection.
     * 
     * @return Psr\Log\LoggerInterface
     */
    public function logger()
    {
        if ($this->logger) {
            return $this->logger;
        }

        return $this->logger = new NullLogger();
    }

    /**
     * Performs custom initialization.
     * 
     * @return void
     */
    protected function initialize()
    {
        //
    }

	/**
	 * Opens the connection if not yet open.
	 *
	 * @param mixed $dsn
	 * @param string $user
	 * @param string $pass
	 * @param array $options
	 * @return $this
	 */
	protected function openHandle($dsn, string $user = '', string $pass = '', array $options = [])
	{
        if ($this->isOpen && ! is_null($this->handle)) {
            return $this;
        }

		try {
            if ($this->type() == 'sqlsrv') {
                unset($options[PDO::ATTR_PERSISTENT]);
            }

			$this->handle = new PDO($dsn, $user, $pass, $options);

            if (! is_null($this->handle)) {
                $this->isOpen = true;
                $this->handle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } else {
                $this->isOpen = false;
            }
			//
		} catch (PDOException $ex) {
			$reason = sprintf('Error while trying open connection at file \'%s\', line %s', __FILE__, __LINE__);

            throw new DatabaseException(null, $reason, $ex);
		}

        return $this;
	}

	/**
	 * Closes the connection
	 *
	 * @return $this
	 */
	protected function closeHandle()
	{
		$this->handle = null;
        $this->isOpen = false;

        return $this;
	}

    /**
     * Tells if connection is open.
     * 
     * @return bool
     */
    public function isOpen()
    {
        return $this->isOpen;
    }

    /**
     * Opens the connection.
     * 
     * @return $this
     */
    public function open()
    {
        return $this->openHandle($this->dsn, $this->username, $this->password, $this->options);
    }

    /**
     * Closes the connection.
     * 
     * @return $this
     */
    public function close()
    {
        return $this->closeHandle();
    }

	/**
	 * returns the underlying PDO connection
	 *
	 * @return void
	 */
	public function getHandle()
	{
		return $this->handle;
	}

	/**
	 * Executes the raw sql statement. Returns true on success, false on fail.
	 * On fail, the second argument holds an object describing the error ocurred.
	 *
	 * @param string $sql
     * @param mixed &$errors
	 * @return bool
	 */
	public function run(string $sql, &$errors = null)
	{
		try {
            if ($this->isOpen()) {
    			$this->getHandle()->exec($sql);
            } else {
                throw new DatabaseException($sql, sprintf('Connection %s not open!', $this->name));
            }
			//
		} catch (PDOException $exception) {
            $errors = $this->processException($exception, $sql);

            return false;
			//
		} catch (Throwable $exception) {
            $errors = $this->processException($exception, $sql);

            return false;
		}

        $errors = null;

        return true;
	}

	/**
	 * Executes the select statement. Returns results as array (it may be empty).
	 * On fail, the third argument holds an object describing the error ocurred.
	 *
	 * @param string $sql
     * @param array|null $data
     * @param mixed &$errors
	 * @return array
	 */
	public function select(string $sql, array $data = null, &$errors = null)
	{
		try {
            if ($this->isOpen()) {
                $stmt = $this->getPrepared($sql);
                $stmt->execute($data);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }

            throw new DatabaseException($sql, sprintf('Connection %s not open!', $this->name));
			//
		} catch (PDOException $exception) {
            $errors = $this->processException($exception, $sql);
			//
		} catch (Throwable $exception) {
            $errors = $this->processException($exception, $sql);
		}

        return [];
	}

	/**
	 * Executes a insert statement that return the inserted ID, if any.
	 * On fail, returns -1 and the third argument holds an object
     * describing the error ocurred.
	 *
	 * @param string $sql
     * @param array|null $data
     * @param mixed &$errors
	 * @return int
	 */
	public function insert(string $sql, array $data = null, &$errors = null)
	{
		try {
            if ($this->isOpen()) {
                $stmt = $this->getPrepared($sql);
                $stmt->execute($data);
                return $this->getHandle()->lastInsertId();
            }

            throw new DatabaseException($sql, sprintf('Connection %s not open!', $this->name));
			//
		} catch (PDOException $exception) {
            $errors = $this->processException($exception, $sql);
			//
		} catch (Throwable $exception) {
            $errors = $this->processException($exception, $sql);
		}

        return -1;
	}

	/**
	 * Executes an update statement. On success, returns how many rows affected
     * (0 if none). On fail, returns -1 and the third argument holds an object
     * describing the error ocurred.
	 *
	 * @param string $sql
     * @param array|null $data
     * @param mixed &$errors
	 * @return int
	 */
	public function update(string $sql, array $data = null, &$errors = null)
    {
        return $this->execute($sql, $data, $errors);
    }

	/**
	 * Executes a delete statement. On success, returns how many rows removed
     * (0 if none). On fail, returns -1 and the third argument holds an object
     * describing the error ocurred.
	 *
	 * @param string $sql
     * @param array|null $data
     * @param mixed &$errors
	 * @return int
	 */
	public function delete(string $sql, array $data = null, &$errors = null)
    {
        return $this->execute($sql, $data, $errors);
    }

	/**
	 * Executes a sql statement without returning results.
     * On success, returns how many rows affected (0 if none).
	 * On fail, returns -1 and the third argument holds an object
     * describing the error ocurred.
	 *
	 * @param string $sql
     * @param array|null $data
     * @param mixed &$errors
	 * @return int
	 */
	protected function execute(string $sql, array $data = null, &$errors = null)
	{
		try {
            if ($this->isOpen()) {
                $stmt = $this->getPrepared($sql);
                $stmt->execute($data);
                return $stmt->rowCount();
            }

            throw new DatabaseException($sql, sprintf('Connection %s not open!', $this->name));
			//
		} catch (PDOException $exception) {
            $errors = $this->processException($exception, $sql);
			//
		} catch (Throwable $exception) {
            $errors = $this->processException($exception, $sql);
		}

        return -1;
	}

    /**
     * Returns prepared statement for the given $sql code.
     * 
     * @return PDOStatement
     * @throws PDOException
     */
    protected function getPrepared(string $sql)
    {
        $hash = md5($sql);

        if (array_key_exists($hash, $this->prepared)) {
            if ($this->prepared[$hash]) {
                return $this->prepared[$hash];
            }
        }

        return $this->prepared[$hash] = $this->open()->getHandle()->prepare($sql);
    }

    /**
     * Processes and returns the exception data as data object.
     * 
     * @param Throwable $exception
     * @param string|null $sql
     * @param string|null $reason
     * @return object
     */
    protected function processException(Throwable $exception, string $sql = null, string $reason = null)
    {
        $reason = $reason ?? $exception->getMessage();

        $context = compact('exception','reason','sql');

        $this->logger()->error($reason, $context);

        $error = (object) $context;

        $this->addError($error);

        return $error;
    }

    /**
     * Adds an error descriptor to the error list.
     * 
     * @param object $error
     * @return void
     */
    protected function addError(object $error)
    {
        $this->errors[] = $error;
    }

    /**
     * Tells if any error has occurred.
     * 
     * @return bool
     */
    public function hasErrors()
    {
        return ! empty($this->errors);
    }

    /**
     * Retrieves all errors occurred, if any.
     * 
     * @return array
     */
    public function getErrors()
    {
        return $this->errors ?? [];
    }

    /**
     * Retrieves the last error occurred, if any.
     * 
     * @return object|null
     */
    public function lastError()
    {
        if ($this->hasErrors()) {
            return $this->errors[array_key_last($this->errors)];
        }

        return null;
    }

    /**
     * Crafts instance display for debugging internals.
     * 
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'isOpen' => $this->isOpen,
            'database' => $this->database,
            'username' => $this->username,
            'handle' => get_instance_id($this->handle),
            'logger' => get_instance_id($this->logger),
            'options' => $this->options,
        ];
    }
}
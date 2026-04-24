<?php
namespace PSharp\Auth\Repositories;

use PSharp\Core\Application;
use PSharp\Auth\AuthManager;
use PSharp\Auth\UserRepositoryInterface;
use PSharp\Auth\Authenticatable;
use PSharp\DB\DatabaseManager;

/**
 * User repository from file.
 */
class DatabaseRepository implements UserRepositoryInterface
{
    /**
     * @static @var array
     */
    protected const FIELDS = [
        'id',
        'username',
        'password',
        'token'
    ];

    /**
     * @var PSharp\DB\Connections\Connection
     */
    protected $connection;

    /**
     * Initialize the instance.
     * 
     * @param PSharp\Core\Application $application
     * @param PSharp\Auth\AuthManager $manager
     * @param PSharp\DB\DatabaseManager $database
     * @param string $connectionName
     * @param string $tableName
     * @param string $tableFields
     */
    public function __construct(
        protected Application $application,
        protected AuthManager $manager,
        protected DatabaseManager $database,
        protected string $connectionName,
        protected string $tableName,
        protected array $tableFields
    ) {
        if ('default' == $connectionName) {
            $connName = $this->config->get('db.connections.default', null);
            
            if (! $this->config->has("db.connections.{$connName}")) {
                $connName = null;
            }

            if ($connName && ($connName != $connectionName)) {
                $this->connectionName = $connName;
            }
        }

        if ($this->connectionName) {
            $this->connection = $database->connection($this->connectionName);
        } else {
            $this->connection = $database->connection();
        }

        foreach (static::FIELDS as $field) if (! isset($this->tableFields[$field])) {
            $this->tableFields[$field] = $field;
        }
    }

	/**
	 * Retrieves an user by $id 
	 *
	 * @param mixed $id
	 * @return \PSharp\Auth\Authenticatable|null
	 */
	public function retrieveById($id)
    {
        if ($this->connection) {
            $fields = $this->tableFields;

            $sql = sprintf('select * from %s where %s.%s = :id', $this->tableName, $this->tableName, $fields['username']);

            if ($result = $this->connection->select($sql, compact('id'))) {
                $result = $result[0];

                return new User(
                    $result[$fields['id']], $result[$fields['username']], $result[$fields['password']], $result[$fields['token']]
                );
            }
        }

        return null;
    }

	/**
	 * Retrieves an user that matches the given associative array.
	 * Keys must holder the field names and values must hold the
	 * desired values to check against. 
	 *
	 * @param array $fields
	 * @return \PSharp\Auth\Authenticatable|null
	 */
	public function retrieveByFields(array $fieldValues)
    {
        if ($this->connection) {
            $fields = $this->tableFields;

            $sql = [ sprintf('select * from %s where (1=1)', $this->tableName) ];
            $values = [];

            foreach ($fields as $name => $customName) {
                if ($value = $fieldValues[$customName] ?? $fieldValues[$name] ?? null) {
                    $sql[] = sprintf(' and (%s.%s = :%s)', $this->tableName, $customName, $name);
                    $values[$name] = $value;
                }
            }

            $sql = implode('', $sql);
            
            if ($result = $this->connection->select($sql, $values)) {
                $result = $result[0];

                return new User(
                    $result[$fields['id']], $result[$fields['username']], $result[$fields['password']], $result[$fields['token']]
                );
            }
        }

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
        if ($this->connection) {
            $fields = $this->tableFields;

            $sql = sprintf(
                'select * from %s where (%s.%s = :id) and (%s.%s = :token)',
                $this->tableName, $this->tableName, $fields['id'], $this->tableName, $fields['token']
            );

            if ($result = $this->connection->select($sql, compact('id','token'))) {
                $result = $result[0];

                return new User(
                    $result[$fields['id']], $result[$fields['username']], $result[$fields['password']], $result[$fields['token']]
                );
            }
        }

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
        if ($this->connection) {
            $fields = $this->tableFields;

            $sql = sprintf('update %s set %s = :token where (%s = :id)', $this->tableName, $fields['token'], $fields['id']);

            $id = $user->getUserID();

            $this->connection->update($sql, compact('token','id'));
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
			'database' => get_instance_id($this->database),
			'connection' => get_instance_id($this->connection),
			'connectionName' => $this->connectionName,
			'tableName' => $this->tableName,
			'tableFields' => $this->tableFields,
		];
	}
}
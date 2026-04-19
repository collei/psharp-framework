<?php
namespace PSharp\DB;

use RuntimeException;
use Throwable;

/**
 * Database exceptions.
 */
class DatabaseException extends RuntimeException
{
    /**
     * @var string The SQL issued the warning, if any.
     */
    protected $sql = null;

    /**
     * Initialize.
     * 
     * @param string|null $sql
     * @param string|null $message
     * @param Throwable|null $previous
     */
    public function __construct(string $sql = null, string $message = null, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->sql = $sql;
    }

    /**
     * Fluently adds sql to the instance.
     * 
     * @param string $sql
     * @return $this
     */
    public function withSql(string $sql)
    {
        $this->sql = $sql;

        return $this;
    }

    /**
     * Retrieves the sql statement, if any.
     * 
     * @return string|null
     */
    public function getSql()
    {
        return $this->sql;
    }
}
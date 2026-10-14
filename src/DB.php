<?php
/**
 * Database facade.
 */
class DB extends PSharp\Support\Facade
{
    /**
     * The available methods.
     * 
     * @var array
     */
    protected static $AVAILABLE = [
        'connection',
    ];

    /**
     * Retrieves the facade acessor.
     * 
     * @return string
     */
    protected static function getAcessor()
    {
        return PSharp\DB\DatabaseManager::class;
    }
}
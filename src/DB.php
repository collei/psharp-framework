<?php
/**
 * Facade class.
 */

/**
 * Database facade.
 */
class DB extends PSharp\Support\Facade
{
    /**
     * Retrieves the available methods.
     * 
     * @return array
     */
    protected static function available()
    {
        return [
            'connection',
        ];
    }

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
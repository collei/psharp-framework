<?php
/**
 * Facade class.
 */

/**
 * Log facade.
 */
class Log extends PSharp\Support\Facade
{
    /**
     * Retrieves the available methods.
     * 
     * @return array
     */
    protected static function available()
    {
        return [
            'log',
            'emergency',
            'alert',
            'critical',
            'error',
            'warning',
            'notice',
            'info',
            'debug'
        ];
    }

    /**
     * Retrieves the facade acessor.
     * 
     * @return string
     */
    protected static function getAcessor()
    {
        return PSharp\Log\LogManager::class;
    }
}
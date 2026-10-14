<?php
/**
 * Log facade.
 */
class Log extends PSharp\Support\Facade
{
    /**
     * The available methods.
     * 
     * @var array
     */
    protected static $AVAILABLE = [
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
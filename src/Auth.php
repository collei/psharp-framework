<?php
/**
 * Database facade.
 */
class Auth extends PSharp\Support\Facade
{
    /**
     * The available methods.
     * 
     * @var array
     */
    protected static $AVAILABLE = [
        'guard',
        'guest',
        'authorizes',
        'authorizesWithDefault',
        'userHas',
        'logon',
        'logoff',
    ];

    /**
     * Retrieves the facade acessor.
     * 
     * @return string
     */
    protected static function getAcessor()
    {
        return PSharp\Auth\AuthManager::class;
    }
}
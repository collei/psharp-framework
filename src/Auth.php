<?php
/**
 * Database facade.
 */
class Auth extends PSharp\Support\Facade
{
    /**
     * Retrieves the available methods.
     * 
     * @return array
     */
    protected static function available()
    {
        return [
            'guard',
            'guest',
            'authorizes',
            'authorizesWithDefault',
            'userHas',
            'logon',
            'logoff',
        ];
    }

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
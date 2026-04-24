<?php
/**
 * Log facade.
 */
class Config extends PSharp\Support\Facade
{
    /**
     * Retrieves the available methods.
     * 
     * @return array
     */
    protected static function available()
    {
        return [
            'get',
            'has',
        ];
    }

    /**
     * Retrieves the facade acessor.
     * 
     * @return string
     */
    protected static function getAcessor()
    {
        return PSharp\Core\Config::class;
    }
}
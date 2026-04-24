<?php
/**
 * Database facade.
 */
class View extends PSharp\Support\Facade
{
    /**
     * Retrieves the available methods.
     * 
     * @return array
     */
    protected static function available()
    {
        return [
            'file',
            'make',
            'share',
            'shared',
            'getShared',
        ];
    }

    /**
     * Retrieves the facade acessor.
     * 
     * @return string
     */
    protected static function getAcessor()
    {
        return PSharp\View\Factory::class;
    }
}
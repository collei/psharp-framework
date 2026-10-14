<?php
/**
 * Database facade.
 */
class View extends PSharp\Support\Facade
{
    /**
     * The available methods.
     * 
     * @var array
     */
    protected static $AVAILABLE = [
        'file',
        'make',
        'share',
        'shared',
        'getShared',
        'check',
    ];

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
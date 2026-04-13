<?php
namespace PSharp\Core\Exceptions;

use Exception;
use Throwable;
use PSharp\Core\Application;

/**
 * Exceptions thrown by the Application itself.
 */
class ApplicationException extends Exception
{
    /**
     * @var PSharp\Core\Application
     */
    protected $application;
    
    /**
     * Instantiates it.
     * 
     * @param string $message = ''
     * @param PSharp\Core\Application|null $app
     * @param Throwable|null $previous
     */
    public function __construct(string $message = '', Application $app = null, Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);

        $this->application = $app;
    }

    /**
     * Retrieves the Application instance, if any.
     * 
     * @return PSharp\Core\Application|null
     */
    public function getApplication()
    {
        return $this->application;
    }
}
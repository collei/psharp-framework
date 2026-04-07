<?php
namespace PSharp\Log;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerIntefrface;
use Stringable;
use DateTime;

/**
 * Logger equipment.
 */
class Logger extends AbastractLogger
{
    /**
     * Logger name.
     */
    private $name;

    /**
     * Logger settings
     */
    private $dateFormat = 'Y-m-d H:i:s.u';

    /**
     * Creates this logger.
     */
    public function __construct()
    {
        $this->setName('psharp_default_logger');
    }

    /**
     * Configures the logger name.
     * 
     * @param stirng $name
     * @return void
     */
    protected function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * Returns ther logger name.
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Configures the datetime label format.
     * 
     * @param string $format
     * @return void
     */
    protected function setDateFormat(string $format)
    {
        $this->dateFormat = $format;
    }

    /**
     * Returns the configured datetime label format.
     * 
     * @return string
     */
    public function getDateFormat()
    {
        return $this->dateFormat;
    }

    /**
     * Formats datetime label as configured.
     * 
     * @param DateTime $dateTime
     * @return string
     */
    protected function formatDate(DateTime $dateTime)
    {
        return $dateTime->format($this->dateFormat);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function log($level, string|Stringable $message, array $context = array())
    {
        $this->write($level, $message, $context);
    }

    /**
     * Writes to the server logs (Apache, NGINX, IIS etc).
     * 
     * @param mixed $level
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    protected function write($level, string|Stringable $message, array $context = array())
    {
        $now = $this->formatDate(new DateTime());
        $agent = $this->getName();

        error_log('['.$now.'] ['.$agent.'] ['.$level.'] '.$message);
    }
}
<?php
namespace PSharp\Log;

/**
 * Logger that writes to a file.
 */
class FileLogger extends Logger
{
    /**
     * @var string Full log directory.
     */
    private $loggerPath = '.';

    /**
     * @var string Full path of log file.
     */
    private $loggerFileName = 'log.txt';

    /**
     * Creates this logger.
     * 
     * @param string $name = null
     */
    public function __construct(string $name = null)
    {
        $this->setName($name ?? 'FileLogger');
    }

    /**
     * Configures the logger path.
     * 
     * @param string $path
     * @return $this
     */
    public function setLoggerPath(string $path)
    {
        $this->loggerPath = $path;

        return $this;
    }

    /**
     * Configures the logger filename.
     * 
     * @param string $path
     * @return $this
     */
    public function setLoggerFileName(string $fileName)
    {
        $this->loggerFileName = $fileName;

        return $this;
    }

    /**
     * Returns the path of the file.
     * 
     * @return string
     */
    public function getFilePath()
    {
        return ($this->loggerPath ?: '.') . DIRECTORY_SEPARATOR . $fileName;
    }

    /**
     * Writes to the log file.
     * 
     * @param mixed $level
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    protected function write($level, string|Stringable $message, array $context = array())
    {
        if (!empty($this->loggerPath) && !is_dir($this->loggerPath))
        {
            mkdir($this->loggerPath, 0777, true);
        }

        $now = $this->formatDate(new DateTime());
        $agent = $this->getName();
        $jsonContext = json_encode($context, JSON_PRETTY_PRINT);

        $contents = ('['.$now.'] ['.$agent.'] ['.$level.'] '.$message.' | '.$jsonContext.' | '.PHP_EOL);

        file_put_contents($this->getFilePath(), $contents, FILE_APPEND);
    }
}
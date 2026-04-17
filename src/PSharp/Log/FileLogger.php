<?php
namespace PSharp\Log;

use Stringable;
use DateTime;
use PSharp\Support\Str;

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
     * @var string Default level, if any.
     */
    protected $defaultLevel = null;

    /**
     * @var string Full path of log file.
     */
    protected $fileNameFormat = 'log.txt';

    /**
     * @var string Format of log entry.
     */
    protected $entryLabel = '[{datelabel}] [{level}] {error} {context}';

    /**
     * @var string Date format for the file name, if needed.
     */
    protected $fileNameDateFormat = 'Y-m-d';

    /**
     * Creates this logger.
     * 
     * @param string $name
     * @param array|null $settings
     */
    public function __construct(string $name, array $settings = null)
    {
        $this->setName($name);

        $this->configure($settings ?? []);
    }

    /**
     * Configure some logger settings.
     * 
     * @param array $conf
     * @return void
     */
    protected function configure(array $conf)
    {
        if (! empty($conf['filename'])) {
            $this->fileNameFormat = $conf['filename']; 
        }

        if (! empty($conf['label'])) {
            $this->entryLabel = $conf['label']; 
        }

        if (! empty($conf['datetime'])) {
            $this->setDateLabelFormat($conf['datetime']); 
        }

        if (! empty($conf['date'])) {
            $this->fileNameDateFormat = $conf['date']; 
        }
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
     * Returns the formatted file name.
     * 
     * @return string
     */
    protected function formatFileName()
    {
        $variables = [
            'date' => date($this->fileNameDateFormat ?: 'Y-m-d'),
            'level' => trim($this->defaultLevel ?: ''),
        ];

        return Str::replaceVariables($this->fileNameFormat, $variables);
    }

    /**
     * Returns the path of the file.
     * 
     * @return string
     */
    public function getFilePath()
    {
        $path = ($this->loggerPath ?: '.');

        return Str::normalizePath($path . DIRECTORY_SEPARATOR . $this->formatFileName());
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

    /**
     * Retrieves info for the internal PHP functions.
     * 
     * @return array
     */
    public function __debugInfo()
    {
        $array = parent::__debugInfo();

        return $array + [
            'loggerPath' => $this->loggerPath,
            'defaultLevel' => $this->defaultLevel,
            'fileNameFormat' => $this->fileNameFormat,
            'entryLabel' => $this->entryLabel,
            'fileNameDateFormat' => $this->fileNameDateFormat,
        ];
    }
}
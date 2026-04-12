<?php
namespace PSharp\Log;

use PSharp\Core\Application;
use PSharp\Core\Config;
use Psr\Log\LoggerTrait;
use Psr\Log\LoggerIntefrface;
use Stringable;
use InvalidArgumentException;

class LogManager implements LoggerIntefrface
{
    use LoggerTrait;

    /**
     * @var PSharp\Core\Application
     */
    protected $application;

    /**
     * @var string|null
     */
    protected $defaultLevel = null;

    /**
     * @var string|null
     */
    protected $defaultChannel = null;

    /**
     * @var array
     */
    protected $classes = [];

    /**
     * @var array
     */
    protected $channels = [];

    /**
     * Initializes the logger manager.
     * 
     * @param PSharp\Core\Application $app
     * @param PSharp\Core\Config $config
     */
    public function __construct(Application $app, Config $config)
    {
        $this->application = $app;

        $this->initialize($config);
    }

    /**
     * Initialize the configuration.
     * 
     * @param PSharp\Core\Config $config
     * @return void
     */
    protected function initialize(Config $config)
    {
        $settings = $config->get('logging');

        $this->defaultLevel = $settings['defaultLevel'] ?? 'info'; 
        $this->defaultChannel = $settings['defaultChannel'] ?? 'single';

        foreach (($settings['channels'] ?? []) as $channel => $channelSettings) {
            $this->channels[$channel] = $this->initializeChannel($channel, $channelSettings);
        }

        if (! isset($settings['single'])) {
            $this->channels['single'] = $this->initializeChannel('default', ['type' => 'file']);
        }
    }

    /**
     * Initialize the given channel with its associated settings.
     * 
     * @param string $name
     * @param array $settings
     * @return Psr\Log\LoggerInterface|null
     */
    protected function initializeChannel(string $name, array $settings)
    {
        if (empty($settings['type'])) {
            throw new InvalidArgumentException('Logger settings must include a string entry named \'type\'.');
        }

        $type = strtolower($settings['type']);

        switch ($type) {
            case 'file':
                return $this->startFileLogger($name, $settings);
            case 'email':
                return $this->startMailLogger($name, $settings);
        }

        return null;
    }

    /**
     * Initialize the file logger with its associated settings.
     * 
     * @param string $name
     * @param array $conf
     * @return PSharp\Log\FileLogger
     */
    protected function startFileLogger(string $name, array $conf)
    {
        $path = explode('/', $conf['folder'] ?? 'storage/logs');

        $loggerPath = $this->application->path(...$path);

        $logger = new FileLogger($name, $conf);
        $logger->setLoggerPath($loggerPath);

        return $logger;
    }
}
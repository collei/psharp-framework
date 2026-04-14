<?php
namespace PSharp\Log;

use PSharp\Core\Application;
use PSharp\Core\Config;
use PSharp\Support\Arr;
use PSharp\Support\Str;
use Psr\Log\LoggerTrait;
use Psr\Log\LoggerInterface;
use Stringable;
use DateTime;
use InvalidArgumentException;

class LogManager implements LoggerInterface
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
            case 'stack':
                return $this->buildLogStack($settings);
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

    /**
     * Initialize the mail logger with its associated settings.
     * 
     * @param string $name
     * @param array $conf
     * @return PSharp\Log\MailLogger
     */
    protected function startMailLogger(string $name, array $conf)
    {
        $logger = new MailLogger($name, $conf);

        return $logger;
    }

    /**
     * Builds a stack logger able to broadcast logs to various channels.
     * 
     * @param array $conf
     * @return Psr\Log\LoggerInterface
     */
    protected function buildLogStack(array $conf)
    {
        $channels = $conf['channels'] ?? ['daily'];

        return new class($this, $channels) implements LoggerInterface {
            use LoggerTrait;
            public function __construct(private LogManager $manager, private array $channels) {}
            public function log($level, string|Stringable $message, array $context = array()): void
            {
                $this->manager->logTo($this->channels, $level, $message, $context);
            }
        };
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function log($level, string|Stringable $message, array $context = array()): void
    {
        $this->write($level, $message, $context);
    }

    /**
     * Logs with an arbitrary level to the given $channel(s).
     *
     * @param string|array $channels
     * @param mixed $level
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function logTo(string|array $channels, $level, string|Stringable $message, array $context = array()): void
    {
        $channels = Arr::wrap($channels);

        $logged = 0;

        foreach ($channels as $channel) {
            if ($logger = $this->getLogger($channel)) {
                $logger->log($level, $message, $context);

                ++$logged;
            }
        }

        if (0 == $logged) {
            throw new Exception('None of the specified Logging channels are currently unavailable.');
        }
    }

    /**
     * Writes log to the default channel OR to the first available one.
     *
     * @param mixed $level
     * @param string|Stringable $message
     * @param array $context
     * @return void
     */
    public function write($level, string|Stringable $message, array $context = array())
    {
        if ($logger = $this->getDefaultLogger()) {
            $logger->log($level, $message, $context);

            return;
        }

        if ($logger = $this->getFirstAvailableLogger()) {
            $logger->log($level, $message, $context);

            return;
        }

        throw new Exception('No logging channels available.');
    }

    /**
     * Returns the given logger channel, if any.
     * 
     * @param string $channel
     * @return Psr\Log\LoggerInterface|null
     */
    protected function getLogger(string $channel)
    {
        return $this->channels[$channel] ?? null;
    }

    /**
     * Returns the default logger channel, if any.
     * 
     * @return Psr\Log\LoggerInterface|null
     */
    protected function getDefaultLogger()
    {
        return $this->channels[$this->defaultChannel] ?? null;
    }

    /**
     * Returns the first available logger channel, if any.
     * 
     * @return Psr\Log\LoggerInterface|null
     */
    protected function getFirstAvailableLogger()
    {
        foreach ($this->channels as $name => $channel) {
            if ($channel) {
                return $channel;
            }
        }

        return null;
    }
}
<?php
namespace PSharp\Core;

use Exception;
use PSharp\Support\Arr;

/**
 * Config repository.
 */
class Config
{
    /**
     * @var string config file path.
     */
    private $configFile = '';

    /**
     * @var array config data
     */
    private $data = [];

    /**
     * Creates instance and loads file.
     * 
     * @param string $configFile
     */
    public function __construct(string $configFile)
    {
        $this->load($configFile);
    }

    /**
     * Loads the config file from disk.
     * 
     * @param string $configFile
     * @return void
     */
    protected function load(string $configFile)
    {
        $this->configFile = $configFile;

        if (! file_exists($this->configFile)) {
            throw new Exception("File $configFile does not exist.");
        }

        $json = file_get_contents($this->configFile);

        $this->data = json_decode($json, true) ?? [];
    }

    /**
     * Tells if a given config exists.
     * 
     * @param string $name
     * @return bool
     */
    public function has(string $name)
    {
        return Arr::has($this->data, $name);
    }

    /**
     * Returns the value or set of values, if any.
     * 
     * @param string $name
     * @param mixed default
     * @return mixed
     */
    public function get(string $name, $default = null)
    {
        if (strpos($name, '.') === false) {
            return $this->data[$name] ?? null;
        }

        return Arr::get($this->data, $name, $default);
    }
}
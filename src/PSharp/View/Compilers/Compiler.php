<?php
namespace PSharp\View\Compilers;

use InvalidArgumentException;
use PSharp\View\Interfaces\CompilerInterface;

/**
 * Compiles views into HTML output.
 *
 * Processes view templates by compiling them into PHP, then
 * acquiring variables and processing the resulting PHP code.
 */
abstract class Compiler implements CompilerInterface
{
    /**
     * @var string
     * @access private
     */
    protected $cachePath = null;

    /**
     * Initializes the compiler engine.
     *
     * @param string|null $cachePath
     * @return void
     */
    public function __construct(string $cachePath = null)
    {
        if ($cachePath) {
            $this->cachePath = $cachePath;
        }
    }

    /**
     * Returns the instance with the base location configured.
     * 
     * @return PSharp\View\Interfaces\CompilerInterface
     */
    public function withCachePath(string $path): CompilerInterface
    {
        $this->cachePath = $path;

        return $this;
    }

    /**
     * Returns the base location of all compiled files.
     *
     * @return string
     */
    public function getCachePath()
    {
        return $this->cachePath;
    }

    /**
     * Returns the location of the compiled version of the file.
     *
     * @param string $path
     * @return string
     */
    public function getCompiledPath($path)
    {
        return $this->cachePath.'/'.sha1($path).'.php';
    }

    /**
     * Tells whether the compiled version of the given view is expired.
     *
     * @param string $path
     * @return bool
     */
    public function isExpired($path)
    {
        $compiled = $this->getCompiledPath($path);
        //
        if (! file_exists($compiled)) {
            return true;
        }
        //
        return filemtime($path) >= filemtime($compiled); 
    }
}
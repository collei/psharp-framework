<?php
namespace PSharp\View\Interfaces;

/**
 * Interface of the view compiler.
 *
 */
interface CompilerInterface
{
    /**
     * Returns the instance with the base location configured.
     * 
     * @return PSharp\View\Interfaces\CompilerInterface
     */
    public function withCachePath(string $path): CompilerInterface;

    /**
     * Returns the base location of all compiled files.
     *
     * @return string
     */
    public function getCachePath();

    /**
     * Returns the location of the compiled version of the file.
     *
     * @param string $path
     * @return string
     */
    public function getCompiledPath($path);

    /**
     * Tells whether the compiled version of the given view is expired.
     *
     * @param string $path
     * @return bool
     */
    public function isExpired($path);
}
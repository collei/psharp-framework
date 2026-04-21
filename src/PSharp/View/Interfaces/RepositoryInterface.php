<?php
namespace PSharp\View\Interfaces;

/**
 * Interface of the view repository.
 *
 */
interface RepositoryInterface
{
    /**
     * Finds a view source by its name.
     *
     * @param string $name
     * @return string
     */
    public function find($name);

    /**
     * Finds a view source name by its path, if any.
     *
     * @param string $path
     * @return string|null
     */
    public function nameByPath($path);

    /**
     * Compiles a view by its name (if needed) and returns the path
     * for the compiled version.
     *
     * It compiles when the compiled version does not exist or
     * if it is older than the source. By setting $force = true, compilation
     * occurs no matter what.
     *
     * @param string $name
     * @param bool $force = false
     * @return string
     */
    public function compile($name, bool $force = false);

    /**
     * Adds a source location.
     *
     * @param string $location
     * @return $this
     */
    public function addLocation($location);

    /**
     * Prepends a source location.
     *
     * @param string $location
     * @return $this
     */
    public function prependLocation($location);

    /**
     * Registers an extension.
     *
     * @param string $extension
     * @return $this
     */
    public function addExtension($extension);

    /**
     * Sets locations at once.
     *
     * @param array $paths
     * @return $this
     */
    public function setPaths($paths);

    /**
     * Returns all locations.
     *
     * @return array
     */
    public function getPaths();

    /**
     * Returns all registered extensions.
     *
     * @return array
     */
    public function getExtensions();

    /**
     * Returns all found views.
     *
     * @return array
     */
    public function getViews();

    /**
     * Retrieves the compiler instance.
     *
     * @return    \PSharp\View\Compilers\CompilerInterface
     */
    public function getCompiler();
}

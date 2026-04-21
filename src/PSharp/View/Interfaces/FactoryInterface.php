<?php
namespace PSharp\View\Interfaces;

use PSharp\Core\Interfaces\Container;
use PSharp\View\Interfaces\FactoryInterface;
use PSharp\View\Interfaces\RepositoryInterface;

/**
 * Interface of the view factory.
 *
 */
interface FactoryInterface
{
    /**
     * Catter a View instance from the given file path.
     *
     * @param string $path
     * @param array|Arrayable $data
     * @param array $mergeData
     * @return    \PSharp\View\Interfaces\ViewInterface
     */
    public function file($path, $data = [], $mergeData = []);

    /**
     * Catter a View instance from the given view name.
     *
     * @param string $view
     * @param array|Arrayable $data
     * @param array $mergeData
     * @return    \PSharp\View\Interfaces\ViewInterface
     */
    public function make($view, $data = [], array $mergeData = []);

    /**
     * Retrieves a reference to the container.
     *
     * @return    \PSharp\Interfaces\Container\Container
     */
    public function getContainer();

    /**
     * Retrieves a reference to the view repository.
     *
     * @return    \PSharp\View\Interfaces\RepositoryInterface
     */
    public function getRepository();

    /**
     * Retrieves a shared datum by $key.
     *
     * @param string $key
     * @param mixed $default = null
     * @return mixed
     */
    public function shared($key, $default = null);

    /**
     * Retrieves an array with all shared data.
     *
     * @return array
     */
    public function getShared();

    /**
     * Share a datum across all views.
     *
     * @param string $key
     * @param mixed $value = null
     * @return mixed
     */
    public function share($key, $value = null);

    /**
     * Increments the render count.
     *
     * @return void
     */
    public function incrementRender();

    /**
     * Decrements the render count.
     *
     * @return void
     */
    public function decrementRender();

    /**
     * Determine whether the view rendering is done.
     *
     * @return bool
     */
    public function doneRendering();

    /**
     * Flush all state.
     *
     * @return void
     */
    public function flushState();

    /**
     * Flush all state if view rendering is done.
     *
     * @return void
     */
    public function flushStateIfDoneRendering();
}
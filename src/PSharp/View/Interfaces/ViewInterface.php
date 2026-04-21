<?php
namespace PSharp\View\Interfaces;

use PSharp\Support\Interfaces\Renderable;
use Throwable;

/**
 * Interface of a view instance.
 *
 */
interface ViewInterface extends Renderable
{
    /**
     * Retrieves the view name.
     *
     * @return string
     */
    public function getName();

    /**
     * Retrieves the view source path.
     *
     * @return string
     */
    public function getPath();

    /**
     * Causes the view to render.
     *
     * @param callable|null $callback
     * @return string
     */
    public function render(callable $callback = null);
}
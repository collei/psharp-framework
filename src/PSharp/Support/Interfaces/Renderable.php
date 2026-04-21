<?php
namespace PSharp\Support\Interfaces;

/**
 * Renderable instances.
 */
interface Renderable
{
    /**
     * Causes the view to render.
     *
     * @param callable|null $callback
     * @return string
     */
    public function render(callable $callback = null): string;
}
<?php
namespace PSharp\Support\Interfaces;

/**
 * Renderable instances.
 */
interface Renderable
{
    /**
     * Causes the instance to render.
     * 
     * @return string
     */
    public function render(): string;
}
<?php
namespace PSharp\Support\Interfaces;

/**
 * Object convertible to an array.
 */
interface Arrayable
{
    /**
     * Returns the object as array.
     * 
     * @return array
     */
    public function toArray(): array;
}
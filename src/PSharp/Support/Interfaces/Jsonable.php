<?php
namespace PSharp\Support\Interfaces;

/**
 * Object convertible to Json string.
 */
interface Jsonable
{
    /**
     * Converts the object as Josn string.
     * 
     * @param int $options
     * @return string
     */
    public function toJson(int $options = 0): string;
}
<?php
namespace PSharp\Http;

/**
 * Object instances convertible to an HTTP Response.
 */
interface Responsable
{
    /**
     * Converts the object instance to Response.
     * 
     * @param PSharp\Http\Request $request
     * @return PSharp\Http\Response
     */
    public function toResponse(Request $request): Response;
}
<?php
namespace PSharp\Http;

use PSharp\Http\Methods\Base\HttpMethodBase;

/**
 * Base class for route endpoints
 */
class Endpoint extends HttpMethodBase implements IEndpoint
{
    /**
     * Called by var_dump, print_r and other debug functions.
     * 
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'name' => $this->getName(),
            'path' => $this->getPath(),
            'action' => $this->getAction(),
            'method' => $this->getMethod(),
        ];
    }
}
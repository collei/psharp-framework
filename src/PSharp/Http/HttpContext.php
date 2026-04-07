<?php
namespace PSharp\Http;

use PSharp\Support\Context;

/**
 * HTTP Context
 */
class HttpContext extends Context
{
    /**
     * @var PSharp\Http\Request
     */
    private $request;

    /**
     * @var PSharp\Http\Response
     */
    private $response;

    /**
     * @var PSharp\Logging\Logger
     */
    private $logger;

    /**
     * Creates the context.
     * 
     * @param PSharp\Http\Request $request
     * @param PSharp\Http\Response $response
     * @param PSharp\Logging\Logger|null $logger
     */
    public function __construct(Request $request, Response $response, Logger $logger = null)
    {
        $this->request = $request;
        $this->response = $response;
        $this->logger = $logger;
    }

    /**
     * Defines the request object.
     * 
     * @param PSharp\Http\Request $request
     * @return void
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Defines the response object.
     * 
     * @param PSharp\Http\Response $response
     * @return void
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * Retrieves a value.
     * 
     * @param string $name
     * @return mixed
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }

        return $this->assets[$name] ?? null;
    }

    /**
     * Returns the object as array.
     * 
     * @return array
     */
    public function toArray(): array
    {
        $assets = parent::toArray();

        $these = [
            'request' => $this->request,
            'response' => $this->response,
            'logger' => $this->logger
        ];
        
        return array_merge($assets, $these);
    }
}
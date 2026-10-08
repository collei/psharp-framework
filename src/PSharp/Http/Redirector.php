<?php
namespace PSharp\Http;

/**
 * Represents a HTTP redirector.
 *
 */
class Redirector
{
    /**
     * Performs an immediate HTTP redirection.
     * 
     * @param string $url
     * @param int $status = 302
     * @param array $headers = []
     * @param bool $secure = null
     * @return PSharp\Http\Response
     */
    public function to(string $url, int $status = 302, array $headers = [], ?bool $secure = null)
    {
        // adds header
        $headers[] = sprintf('Location: %s', $url);

        // performs response
        return new Response('', $status, $headers);
    }

    /**
     * Performs an immediate HTTP redirection.
     * 
     * @param string $name
     * @param array $parameters = []
     * @param array $headers = []
     * @return PSharp\Http\Response
     */
    public function route(string $name, array $parameters = [], array $headers = [])
    {
        // crafts url
        $url = route($name, $parameters);

        // performs it
        return $this->to($url, 302, $headers);
    }
}
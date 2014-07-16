<?php

namespace Idio\Api;

/**
 * Request
 *
 * Provides a nice wrapper for responses from the idio API
 *
 * Example Usage
 *
 * $request = new IdioApi\Request('GET', '/content')
 * $response = $request->send();
 *
 * if ($response->getStatus() == 200) {
 *     $result = $response->getBody();
 *     echo $result['total_hits'];
 * }
 *
 * @package IdioApi
 */
class Response
{
    /**
     * @var array cURL Request/Response Information
     */
    protected $info;

    /**
     * @var integer HTTP Status Code
     */
    protected $status;

    /**
     * @var mixed Response Body
     */
    protected $body;

    /**
     * Constructor
     *
     * @param string  $body    The response body
     * @param Request $request The original Idio\Api\Request object
     *
     * @return void;
     */
    public function __construct($body, $request)
    {
        $this->info = $this->info($request);
        $this->status = $this->info['http_code'];
        $this->body = $body;
    }

    /**
     * Get Body
     *
     * Returns the body, once JSON decoded
     *
     * @param boolean $asObject Return as an object? Otherwise will return an array
     *
     * @return mixed The body in either array or object form.
     */
    public function getBody($asObject = false)
    {
        return json_decode($this->body, !$asObject);
    }

    /**
     * Get Status
     *
     * @return integer HTTP Status Code
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * To String
     *
     * Dump out the raw response body
     *
     * @return string Raw response body
     */
    public function __toString()
    {
        return $this->body;
    }

    /**
     * Get Info
     *
     * A wrapper for curl_getinfo
     */
    protected function info($request)
    {
        $handle = $request->getHandle();
        return is_resource($handle) ? curl_getinfo($handle) : false;
    }
}

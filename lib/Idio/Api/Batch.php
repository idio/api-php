<?php

namespace Idio\Api;

/**
 * Batch
 *
 * Submits one or more API Requests in parallel using curl_multi_exec.
 *
 * Example Usage
 *
 * $batch = new IdioApi\Batch();
 * $batch->add(
 *     new IdioApi\Request('GET', '/content')
 * );
 * $responses = $batch->send();
 *
 * - OR -
 *
 * $batch = new IdioApi\Batch(array(
 *     new IdioApi\Request('GET', '/content')
 * ));
 * $responses = $batch->send();
 *
 * @package IdioApi
 */
class Batch
{
    /**
     * @var handle Multi cURL Handle
     */
    protected $handle;

    /**
     * @var array Request Objects
     */
    protected $requests = array();

    /**
     * Constructor
     *
     * Creates the multiple cURL Handle and adds any requests
     * that were supplied to the constructor
     *
     * @param array $requests Array of Request objects
     */
    public function __construct($requests = array())
    {
        $this->handle = $this->handle();

        foreach ($requests as $key => $request) {
            if (is_a($request, 'Idio\Api\Request')) {
                $this->add($key, $request);
            }
        }
    }

    /**
     * Add Request
     *
     * Add a Request object to be sent concurrently when send() is called.
     *
     * @param string  $key     Key to return responses under
     * @param Request $request Request Object
     *
     * @return Idio\Api\Batch (for chaining)
     */
    public function add($key, Request $request)
    {
        $this->requests[$key] = $request;
        $this->addHandle($request->getHandle());

        return $this;
    }

    /**
     * Send Request(s)
     *
     * Concurrently make API requests using curl_multi_exec
     *
     * @return array Array of Response objects
     */
    public function send()
    {
        $active = null;
        $results = array();

        // Execute the handles
        do {
            $this->exec($active);
        } while ($active && $this->block());

        // Close the handles
        foreach ($this->requests as $key => $request) {
            $requestHandle = $request->getHandle();
            $results[$key] = new Response(
                $this->get($requestHandle),
                $request
            );
            $this->removeHandle($requestHandle);
        }

        $this->close();

        // Aaaand we're good.
        return $results;
    }

    /**
     * Initialise cURL multi Handle
     *
     * Wrapper for curl_multi_init
     *
     * @return handle a new cURL multi handle
     * @codeCoverageIgnore
     */
    protected function handle()
    {
        return curl_multi_init();
    }

    /**
     * Add Request
     *
     * Add an individual request to the batch. Wrapper for curl_multi_add_handle
     *
     * @param resource $requestHandle Request Handle
     * @return handle a new cURL multi handle
     * @codeCoverageIgnore
     */
    protected function addHandle($requestHandle)
    {
        curl_multi_add_handle($this->handle, $requestHandle);
    }

    /**
     * Execute
     *
     * Run the sub-connections of the current cURL handle. Wrapper for
     * curl_multi_exec
     *
     * @param  boolean $active Whether the operations are still running.
     * @return integer cURL code
     * @codeCoverageIgnore
     */
    protected function exec(&$active)
    {
        return curl_multi_exec($this->handle, $active);
    }

    /**
     * Block
     *
     * Blocks until there is activity on any of the curl_multi connections.
     * Wrapper for curl_multi_select.
     * @codeCoverageIgnore
     */
    protected function block()
    {
        curl_multi_select($this->handle);
        return true;
    }

    /**
     * Get Content
     *
     * Returns the body for a single cURL request. Wrapper for
     * curl_multi_getcontent.
     *
     * @param resource $requestHandle Request Handle
     * @codeCoverageIgnore
     */
    protected function get($requestHandle)
    {
        return curl_multi_getcontent($requestHandle);
    }

    /**
     * Remove Handle
     *
     * Remove a multi handle from a set of cURL handles. Wrapper for
     * curl_multi_remove_handle.
     *
     * @param resource $requestHandle Request Handle
     * @return integer Returns 0 on success, or one of the CURLM_XXX error codes.
     * @codeCoverageIgnore
     */
    protected function removeHandle($requestHandle)
    {
        return curl_multi_remove_handle($this->handle, $requestHandle);
    }

    /**
     * Close Handle
     *
     * Close a set of cURL handles. Wrapper for curl_multi_close.
     *
     * @return void
     * @codeCoverageIgnore
     */
    protected function close()
    {
        curl_multi_close($this->handle);
    }
}

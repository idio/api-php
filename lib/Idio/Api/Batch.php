<?php

namespace Idio\Api;

/**
 * Batch
 * 
 * Submits one or more API Requests in parallel using curl_multi_exec.
 *
 * Example Usage
 * 
 * $objBatch = new IdioApi\Batch();
 * $objBatch->add(
 *     new IdioApi\Request('GET', '/content')
 * );
 * $arrResponses = $objBatch->send();
 *
 * - OR -
 *
 * $objBatch = new IdioApi\Batch(array(
 *     new IdioApi\Request('GET', '/content')
 * ));
 * $arrResponses = $objBatch->send();
 *
 * @package IdioApi
 */
class Batch
{
    
    // Multiple cURL handle
    protected $resHandle;

    // Array of Request objects
    protected $arrRequests = array();

    /**
     * Constructor
     * 
     * Creates the multiple cURL Handle and adds any requests
     * that were supplied to the constructor
     *
     * @param array $arrRequests Array of Request objects
     */
    public function __construct($arrRequests = array())
    {
        $this->resHandle = $this->handle();

        foreach ($arrRequests as $strKey => $resRequest) {
            if (is_a($resRequest, 'Idio\Api\Request')) {
                $this->add($strKey, $resRequest);
            }
        }
    }

    /**
     * Add Request
     * 
     * Add a Request object to be sent concurrently when send() is called.
     *
     * @param string  $strKey     Key to return responses under
     * @param Request $resRequest Request Object
     *
     * @return void
     */
    public function add($mxdKey, Request $resRequest)
    {
        $this->arrRequests[$mxdKey] = $resRequest;
        $this->addHandle($resRequest->getHandle());
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
        $blnActive = null;
        $arrResults = array();

        // Execute the handles
        do {
            // CURLM_CALL_MULTI_PERFORM means that there is more processing to
            // be done immediately, in which case we shouldn't block
            do {
                $intStatus = $this->exec($blnActive);
            } while ($intStatus == CURLM_CALL_MULTI_PERFORM);
            // Otherwise block until further activity is detected on the sockets
            // and then exec again until we're finished
            $this->block();
        } while ($blnActive > 0);

        // Close the handles
        foreach ($this->arrRequests as $mxdKey => $resRequest) {
            $resRequestHandle = $resRequest->getHandle();
            $arrResults[$mxdKey] = new Response(
                $this->get($resRequestHandle),
                $resRequest
            );
            $this->removeHandle($resRequestHandle);
        }
        
        $this->close();

        // Aaaand we're good.
        return $arrResults;
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
     * @param resource $resRequestHandle Request Handle
     * @return handle a new cURL multi handle
     * @codeCoverageIgnore
     */
    protected function addHandle($resRequestHandle)
    {
        curl_multi_add_handle($this->resHandle, $resRequestHandle);
    }

    /**
     * Execute
     *
     * Run the sub-connections of the current cURL handle. Wrapper for
     * curl_multi_exec
     *
     * @param  boolean $blnActive Whether the operations are still running.
     * @return integer cURL code
     * @codeCoverageIgnore
     */
    protected function exec(&$blnActive)
    {
        return curl_multi_exec($this->resHandle, $blnActive);
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
        return curl_multi_select($this->resHandle);
    }

    /**
     * Get Content
     *
     * Returns the body for a single cURL request. Wrapper for
     * curl_multi_getcontent.
     *
     * @param resource $resRequestHandle Request Handle
     * @codeCoverageIgnore
     */
    protected function get($resRequestHandle)
    {
        return curl_multi_getcontent($resRequestHandle);
    }

    /**
     * Remove Handle
     *
     * Remove a multi handle from a set of cURL handles. Wrapper for
     * curl_multi_remove_handle.
     *
     * @param resource $resRequestHandle Request Handle
     * @return integer Returns 0 on success, or one of the CURLM_XXX error codes.
     * @codeCoverageIgnore
     */
    protected function removeHandle($resRequestHandle)
    {
        return curl_multi_remove_handle($this->resHandle, $resRequestHandle);
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
        curl_multi_close($this->resHandle);
    }
}

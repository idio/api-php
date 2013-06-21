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
    protected $objHandle;

    // Array of Request objects
    protected $objRequests = array();

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

        $this->objHandle = curl_multi_init();

        foreach ($arrRequests as $strKey => $objRequest) {
            $this->add($strKey, $objRequest);
        }

    }

    /**
     * Add Request
     * 
     * Add a Request object to be sent concurrently when send() is called.
     *
     * @param string  $strKey     Key to return responses under
     * @param Request $objRequest Request Object
     *
     * @return void
     */
    public function add($strKey, Request $objRequest)
    {

        $this->objRequests[$strKey] = $objRequest;
        curl_multi_add_handle($this->objHandle, $objRequest->getHandle());

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
            $intCurlStatus = curl_multi_exec($this->objHandle, $blnActive);
        } while ($intCurlStatus == CURLM_CALL_MULTI_PERFORM);

        while ($blnActive && $intCurlStatus == CURLM_OK) {
            if (curl_multi_select($this->objHandle) != -1) {
                do {
                    curl_multi_exec($this->objHandle, $blnActive);
                } while ($intCurlStatus == CURLM_CALL_MULTI_PERFORM);
            }
        }

        // Close the handles
        foreach ($this->objRequests as $strKey => $objRequest) {
            $objRequestHandle = $objRequest->getHandle();
            $arrResults[$strKey] = new Response(
                curl_multi_getcontent($objRequestHandle),
                $objRequest
            );
            curl_multi_remove_handle($this->objHandle, $objRequestHandle);
        }
        curl_multi_close($this->objHandle);

        // Aaaand we're good.
        return $arrResults;

    }
}

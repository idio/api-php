<?php

namespace Idio\Api;

/**
 * Request
 * 
 * Provides a nice wrapper for responses from the idio API
 *
 * Example Usage
 * 
 * $objRequest = new IdioApi\Request('GET', '/content')
 * $objResponse = $arrRequest->send();
 * 
 * if ($objResponse->getStatus() == 200) {
 *     $arrBody = $objResponse->getBody();
 *     echo $arrBody['title'];
 * }
 *
 * @package IdioApi
 */
class Response
{
    
    // cURL Request/Response Information
    protected $arrInfo;

    // HTTP Status Code
    protected $intStatus;

    // Response Body
    protected $mxdBody;

    /**
     * Constructor
     *
     * @param string  $strBody    The response body
     * @param Request $objRequest The original Idio\Api\Request object
     *
     * @return void;
     */
    public function __construct($strBody, $objRequest)
    {

        $this->arrInfo = curl_getinfo($objRequest->getHandle());
        $this->intStatus = $this->arrInfo['http_code'];
        $this->mxdBody = $strBody;
        
    }

    /**
     * Get Body
     * 
     * Returns the body, once JSON decoded
     *
     * @param boolean $blnObject Return as an object? Otherwise will return an array
     *
     * @return mixed The body in either array or object form.
     */
    public function getBody($blnObject = false)
    {
        return json_decode($this->mxdBody, !$blnObject);
    }

    /**
     * Get Status
     *
     * @return integer HTTP Status Code
     */
    public function getStatus()
    {
        return $this->intStatus;
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
        return $this->mxdBody;
    }
}

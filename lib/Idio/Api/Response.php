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
class Response {
    
    // cURL Request/Response Information
    protected $arrInfo;

    // HTTP Status Code
    protected $intStatus;

    // Response Body
    protected $mxdBody;

    public function __construct($strBody, $objRequest) {

        $this->arrInfo = curl_getinfo($objRequest->getHandle());
        $this->intStatus = $this->arrInfo['http_code'];
        $this->mxdBody = $strBody;
        
    }

    public function getBody($blnObject = false) {
        return json_decode($this->mxdBody, !$blnObject);
    }

    public function getStatus() {
        return $this->intStatus;
    }

    public function __toString() {
        return $this->mxdBody;
    }

}
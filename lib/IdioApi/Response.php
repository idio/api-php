<?php

namespace IdioApi;

class Response {
    
    protected $arrInfo;
    protected $intStatus;
    protected $mxdBody;

    public function __construct($strBody, $objRequest) {

        $this->arrInfo = curl_getinfo($objRequest->getHandler());
        $this->intStatus = $this->arrInfo['http_code'];
        $this->mxdBody = $strBody;
        
    }

    public function getBody() {
        return json_decode($this->mxdBody);
    }

    public function getStatus() {
        return $this->intStatus;
    }

    public function __toString() {
        return $this->mxdBody;
    }

}
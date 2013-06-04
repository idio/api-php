<?php

namespace IdioApi;

class Request {

    protected $objHandler;
    
    public function __construct($strMethod, $strUrl, $mxdData = array()) {

        $arrHeaders = Authentication::getHeaders($strMethod, $strUrl);

        $this->objHandler = curl_init();

        curl_setopt_array($this->objHandler, array(
            CURLOPT_CUSTOMREQUEST => strToUpper($strMethod),
            CURLOPT_ENCODING => 'utf-8',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $strUrl,
            CURLOPT_USERAGENT => 'idioPlatform',
            CURLOPT_POSTFIELDS => $mxdData,
            CURLOPT_HTTPHEADER => $arrHeaders,
            CURLOPT_SSL_VERIFYPEER => false
        ));

    }

    public function send() {

        $strContent = curl_exec($this->objHandler);
        return new Response($strContent, $this->objHandler);

    }

    public function getHandler() {
        return $this->objHandler;
    }

}
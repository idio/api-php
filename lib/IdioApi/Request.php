<?php

namespace IdioApi;

class Request {

    protected $objHandler;
    
    public function __construct($strMethod, $strUrl, $mxdData = array()) {

        $arrHeaders = Authentication::getHeaders($strMethod, $strUrl);

        // If we're sending data, set the content type
        if ($mxdData != null) {
            $arrHeaders[] = "Content-type: application/json";
        }

        if (!is_string($mxdData) && $mxdData != null) {
            $mxdData = json_encode($mxdData);
        }

        $this->objHandler = curl_init();

        $strUrl = Configuration::getUrl() . $strUrl;

        curl_setopt_array($this->objHandler, array(
            CURLOPT_CUSTOMREQUEST => strToUpper($strMethod),
            CURLOPT_ENCODING => 'utf-8',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $strUrl,
            CURLOPT_USERAGENT => 'Idio API PHP Library',
            CURLOPT_POSTFIELDS => $mxdData,
            CURLOPT_HTTPHEADER => $arrHeaders,
            CURLOPT_SSL_VERIFYPEER => false
        ));

    }

    public function send() {

        $strContent = curl_exec($this->objHandler);
        return new Response($strContent, $this);

    }

    public function getHandler() {
        return $this->objHandler;
    }

}
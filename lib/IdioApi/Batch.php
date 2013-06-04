<?php

namespace IdioApi;

class Batch {
    
    protected $objHandler;

    protected $objRequests = array();

    public function __construct() {

        $this->objHandler = curl_multi_init();
        $arrRequests = func_get_args();

        foreach ($arrRequests as $objRequest) {
            if (is_a($objRequest, 'IdioApi\Request')) {
                $this->objRequests[] = $objRequest;
                curl_multi_add_handle($this->objHandler, $objRequest->getHandler());
            }
        }

    }

    public function send() {

        $blnActive = null;
        $arrResults = array();

        // execute the handles
        do {
            $intCurlStatus = curl_multi_exec($this->objHandler, $blnActive);
        } while ($intCurlStatus == CURLM_CALL_MULTI_PERFORM);

        while ($blnActive && $intCurlStatus == CURLM_OK) {
            if (curl_multi_select($this->objHandler) != -1) {
                do {
                    $mrc = curl_multi_exec($this->objHandler, $blnActive);
                } while ($intCurlStatus == CURLM_CALL_MULTI_PERFORM);
            }
        }

        //close the handles
        foreach ($this->objRequests as $intKey => $objRequest) {
            $objRequestHandler = $objRequest->getHandler();
            $arrResults[$intKey] = new Response(
                curl_multi_getcontent($objRequestHandler),
                $objRequest
            );
            curl_multi_remove_handle($this->objHandler, $objRequestHandler);
        }
        curl_multi_close($this->objHandler);

        return $arrResults;

    }

}
<?php

namespace Idio\Api;

/**
 * Request
 * 
 * Provides a nice wrapper for making requests against the idio API, whilst
 * taking care of authentication etc.
 *
 * Example Usage
 * 
 * $objRequest = new IdioApi\Request('GET', '/content')
 * $objResponse = $arrRequest->send();
 *
 * If you are using PHP 5.4 or newer, you can just do
 * $objResponse = new IdioApi\Request('GET', '/content')->send();
 *
 * @package IdioApi
 */
class Request
{

    // cURL Handle
    protected $resHandle;
    
    /**
     * Constructor
     *
     * @param Client $objClient Idio\Api\Client object
     * @param string $strMethod HTTP Verb (GET, POST, etc)
     * @param string $strPath   Relative URL (excluding version) to call
     *           e.g. /content
     * @param string $mxdData   POST data or query parameters to send, 
     *                depending on HTTP method chosen
     */
    public function __construct(Client $objClient, $strMethod, $strPath, $mxdData = array())
    {
        $arrHeaders = $objClient->getHeaders($strMethod, $strPath);

        // If we're sending data, set the content type
        if ($mxdData != null) {
            $arrHeaders[] = "Content-type: application/json";
        }

        if (!is_string($mxdData) && $mxdData != null) {
            $mxdData = json_encode($mxdData);
        }

        $this->resHandle = $this->handle();

        $strUrl = $objClient->getUrl() . $strPath;

        $this->setOptions(
            array(
                CURLOPT_CUSTOMREQUEST => strToUpper($strMethod),
                CURLOPT_ENCODING => 'utf-8',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $strUrl,
                CURLOPT_USERAGENT => 'Idio API PHP Library',
                CURLOPT_POSTFIELDS => $mxdData,
                CURLOPT_HTTPHEADER => $arrHeaders,
                CURLOPT_SSL_VERIFYPEER => false
            )
        );
    }

    /**
     * Send Request
     *
     * @return Response API Response Object
     */
    public function send()
    {
        $strContent = $this->exec();
        return new Response($strContent, $this);
    }

    /**
     * Get cURL Handle
     *
     * Used by the Batch object to get multiple Request Handles and fire them
     * all at the same time.
     *
     * @return handle cURL handle
     */
    public function getHandle()
    {
        return $this->resHandle;
    }

    /**
     * Initialise cURL Handle.
     *
     * Wrapper for curl_init
     *
     * @return resource cURL Handle
     * @codeCoverageIgnore
     */
    protected function handle()
    {
        return curl_init();
    }

    /**
     * Execute
     *
     * Run the sub-connections of the current cURL handle. Wrapper for curl_exec
     *
     * @return string Response Content
     * @codeCoverageIgnore
     */
    protected function exec()
    {
        return curl_exec($this->resHandle);
    }

    /**
     * Set cURL Options
     *
     * Wrapper for curl_setopt_array
     *
     * @param array $arrOptions Array of cURL options
     * @codeCoverageIgnore
     */
    protected function setOptions($arrOptions)
    {
        curl_setopt_array($this->resHandle, $arrOptions);
    }
}

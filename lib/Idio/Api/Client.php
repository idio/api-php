<?php

namespace Idio\Api;

/**
 * Client
 * 
 * Handles configuration (URL, version) and authentication for idio API
 * requests
 *
 * @package IdioApi
 */
class Client
{

    // Authentication Credentials
    protected $arrCredentials = array(
        'App' => array(
            'key' => false,
            'secret' => false
        ),
        'Delivery' => array(
            'key' => false,
            'secret' => false
        )
    );

    // Base URL
    protected $strBaseUrl = "";

    // Version Prefix
    protected $strVersion = "";

    /**
     * Set URL
     *
     * @param string $strBaseUrl Full URL to the API, including http://
     *              but without a trailing slash
     * @param string $strVersion Version prefix, e.g. "1.0"
     *
     * @return void
     */
    public function setUrl($strBaseUrl, $strVersion = false)
    {
        $this->strBaseUrl = $strBaseUrl;
        $this->strVersion = $strVersion;
    }

    /**
     * Get Version
     *
     * @return string Version Prefix
     */
    public function getVersion()
    {
        return $this->strVersion;
    }

    /**
     * Get URL
     *
     * @return string Full base URL, including version
    * *             e.g. "https://api.idio.co/0.3"
     */
    public function getUrl()
    {
        return $this->getVersion() ? "$this->strBaseUrl/{$this->getVersion()}" : $this->strBaseUrl;
    }

    /**
     * Set App Credentials
     * 
     * @param string $strAppApiKey    API Key
     * @param string $strAppApiSecret API Secret
     *
     * @return void
     */
    public function setAppCredentials($strAppApiKey, $strAppApiSecret)
    {
        $this->arrCredentials['App'] = array(
            'key' => $strAppApiKey,
            'secret' => $strAppApiSecret
        );
    }

    /**
     * Set Delivery Credentials
     * 
     * @param string $strDeliveryApiKey    Delivery Key
     * @param string $strDeliveryApiSecret Delivery Secret
     *
     * @return void
     */
    public function setDeliveryCredentials($strDeliveryApiKey, $strDeliveryApiSecret)
    {
        $this->arrCredentials['Delivery'] = array(
            'key' => $strDeliveryApiKey,
            'secret' => $strDeliveryApiSecret
        );
    }

    /**
     * Build Signature
     *
     * Build a signature as defined in the idio API documentation
     * 
     * Signature = Base64(
     *    HMAC-SHA1(
     *        Secret Key, UTF-8 Encoding(HTTP Verb + "\n" + Request URI + "\n" + Request Date)
     *    )
     * );
     * 
     * @param string $strRequestMethod HTTP Verb - e.g. GET, POST
     * @param string $strRequestPath   Path to the endpoint we're trying to hit, without version prefix
     * @param string $strSecretKey     Secret key to sign with
     *
     * @return string Generated Signature
     */
    protected function buildSignature($strRequestMethod, $strRequestPath, $strSecretKey)
    {

        // Split off any query parameters.
        $arrRequestParts = explode('?', $strRequestPath);
        $strRequestPath = array_shift($arrRequestParts);

        // Prefix with version
        $strVersion = $this->getVersion();
        $strRequestPath = ($strVersion ? "/{$strVersion}" : "") . $strRequestPath;

        $strStringToSign = utf8_encode(
            strtoupper($strRequestMethod) . "\n" .
            $strRequestPath . "\n" .
            $this->date()
        );
        
        return base64_encode(hash_hmac("sha1", $strStringToSign, $strSecretKey));
    }

    /**
     * Get Headers for a particular method
     *
     * Get the HTTP headers for the app and (optionally) delivery, including
     * the signature
     *
     * @param string $strMethod HTTP Verb (e.g. GET, POST)
     * @param string $strPath   Path to the endpoint we want to hit
     *
     * @return array Array of headers to send with the HTTP request
     */
    public function getHeaders($strMethod, $strPath)
    {
        $arrHeaders = array();

        foreach ($this->arrCredentials as $strKey => $arrCredentials) {
            if (!empty($arrCredentials['key']) && !empty($arrCredentials['secret'])) {
                $strSignature = $this->buildSignature($strMethod, $strPath, $arrCredentials['secret']);
                $arrHeaders[] = "X-{$strKey}-Authentication: {$arrCredentials['key']}:{$strSignature}";
            }
        }

        return $arrHeaders;

    }

    /**
     * Make Request
     *
     * Convenience wrapper for creating request objects. Does not send the
     * request, in case you're looking to batch them up. @see IdioApi\Request.
     *
     * @param string $strMethod HTTP Verb (e.g. GET, POST)
     * @param string $strPath   Relative URL (excluding version) to call
     *           e.g. /content
     * @param string $mxdData   POST data or query parameters to send, 
     *                depending on HTTP method chosen
     *
     * @return IdioApi\Response Response object
     */
    public function request($strMethod, $strPath, $mxdData = array())
    {

        return new Request($this, $strMethod, $strPath, $mxdData);

    }

    /**
     * Batch Requests
     *
     * Convenience wrapper for creating a batch object. Does not send the
     * requests, in case you're looking to do something else. @see IdioApi\Batch.
     * 
     * @param array $arrRequests Array of Request objects
     *
     * @return IdioApi\Batch Batch object
     */
    public function batch($arrRequests)
    {
        return new Batch($arrRequests);
    }

    /**
     * Get Link Object
     *
     * Convenience wrapper for creating a link object. 
     * 
     * @param string $strUrl Link to manipulate
     *
     * @return IdioApi\Link Link object
     */
    public function link($strLink)
    {
        return new Link($strLink);
    }

    /**
     * Get Date
     * 
     * Get date in Y-m-d form, used as part of signature generation and wrapped
     * to allow for stubbing during tests
     */
    protected function date()
    {
        return date('Y-m-d');
    }
}

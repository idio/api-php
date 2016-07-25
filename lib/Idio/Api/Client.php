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
    /**
     * @var array Client credentials
     */
    protected $credentials = array(
        'App' => array(
            'key' => false,
            'secret' => false
        ),
        'Delivery' => array(
            'key' => false,
            'secret' => false
        )
    );

    /**
     * @var string Base URL
     */
    protected $baseUrl = "";

    /**
     * @var string Version prefix
     */
    protected $version = "";

    /**
     * Set URL
     *
     * @param string $baseUrl Full URL to the API, including http://
     *              but without a trailing slash
     * @param string $version Version prefix, e.g. "1.0"
     * @return Idio\Api\Client (for chaining)
     */
    public function setUrl($baseUrl, $version = false)
    {
        $this->baseUrl = $baseUrl;
        $this->version = $version;

        return $this;
    }

    /**
     * Get Version
     *
     * @return string Version Prefix
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get URL
     *
     * @return string Full base URL, including version
     *             e.g. "https://api.idio.co/0.3"
     */
    public function getUrl()
    {
        return $this->getVersion() ? "$this->baseUrl/{$this->getVersion()}" : $this->baseUrl;
    }

    /**
     * Set App Credentials
     *
     * @param string $appKey    API Key
     * @param string $appSecret API Secret
     * @return Idio\Api\Client (for chaining)
     */
    public function setAppCredentials($appKey, $appSecret)
    {
        $this->credentials['App'] = array(
            'key' => $appKey,
            'secret' => $appSecret
        );

        return $this;
    }

    /**
     * Set Delivery Credentials
     *
     * @param string $deliveryKey    Delivery Key
     * @param string $deliverySecret Delivery Secret
     * @return Idio\Api\Client (for chaining)
     */
    public function setDeliveryCredentials($deliveryKey, $deliverySecret)
    {
        $this->credentials['Delivery'] = array(
            'key' => $deliveryKey,
            'secret' => $deliverySecret
        );

        return $this;
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
     * @param string $method HTTP Verb - e.g. GET, POST
     * @param string $path   Path to the endpoint we're trying to hit, without version prefix
     * @param string $secretKey     Secret key to sign with
     * @return string Generated Signature
     */
    protected function buildSignature($method, $path, $secretKey)
    {
        // Split off any query parameters.
        $parts = explode('?', $path);
        $path = array_shift($parts);

        // Prefix with version
        $version = $this->getVersion();
        $path = ($version ? "/{$version}" : "") . $path;

        $string = mb_convert_encoding(
            strtoupper($method) . "\n" .  $path . "\n" .  $this->date(),
            'UTF-8'
        );

        return base64_encode(hash_hmac("sha1", $string, $secretKey));
    }

    /**
     * Get Headers for a particular method
     *
     * Get the HTTP headers for the app and (optionally) delivery, including
     * the signature
     *
     * @param string $method HTTP Verb (e.g. GET, POST)
     * @param string $path   Path to the endpoint we want to hit
     * @return array Array of headers to send with the HTTP request
     */
    public function getHeaders($method, $path)
    {
        $headers = array();

        foreach ($this->credentials as $type => $credentials) {
            if (!empty($credentials['key']) && !empty($credentials['secret'])) {
                $signature = $this->buildSignature($method, $path, $credentials['secret']);
                $headers[] = "X-{$type}-Authentication: {$credentials['key']}:{$signature}";
            }
        }

        return $headers;
    }

    /**
     * Make Request
     *
     * Convenience wrapper for creating request objects. Does not send the
     * request, in case you're looking to batch them up. @see IdioApi\Request.
     *
     * @param string $method HTTP Verb (e.g. GET, POST)
     * @param string $path   Relative URL (excluding version) to call
     *           e.g. /content
     * @param string $data   POST data or query parameters to send,
     *                depending on HTTP method chosen
     * @return IdioApi\Response Response object
     */
    public function request($method, $path, $data = array())
    {
        return new Request($this, $method, $path, $data);
    }

    /**
     * Batch Requests
     *
     * Convenience wrapper for creating a batch object. Does not send the
     * requests, in case you're looking to do something else. @see IdioApi\Batch.
     *
     * @param array $requests Array of Request objects
     * @return IdioApi\Batch Batch object
     */
    public function batch($requests)
    {
        return new Batch($requests);
    }

    /**
     * Get Link Object
     *
     * Convenience wrapper for creating a link object.
     *
     * @param string $url Link to manipulate
     * @return IdioApi\Link Link object
     */
    public function link($url)
    {
        return new Link($url);
    }

    /**
     * Get Date
     *
     * Get date in Y-m-d form, used as part of signature generation and wrapped
     * to allow for stubbing during tests
     *
     * @return string Current date
     */
    protected function date()
    {
        return date('Y-m-d');
    }
}

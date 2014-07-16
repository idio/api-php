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
 * $request = new IdioApi\Request('GET', '/content')
 * $response = $request->send();
 *
 * If you are using PHP 5.4 or newer, you can just do
 * $response = new IdioApi\Request('GET', '/content')->send();
 *
 * @package IdioApi
 */
class Request
{
    /**
     * @var cURL Handle
     */
    protected $handle;

    /**
     * Constructor
     *
     * @param Client $client Idio\Api\Client object
     * @param string $method HTTP Verb (GET, POST, etc)
     * @param string $path   Relative URL (excluding version) to call
     *           e.g. /content
     * @param string $data   POST data or query parameters to send,
     *                depending on HTTP method chosen
     */
    public function __construct(Client $client, $method, $path, $data = array())
    {
        if (substr($path, 0, 1) != '/') {
            $path = "/{$path}";
        }
        $headers = $client->getHeaders($method, $path);

        // If we're sending data, set the content type
        if ($data != null) {
            $headers[] = "Content-type: application/json";
        }

        if (!is_string($data) && $data != null) {
            $data = json_encode($data);
        }

        $this->handle = $this->handle();

        $url = $client->getUrl() . $path;

        $this->setOptions(
            array(
                CURLOPT_CUSTOMREQUEST => strtoupper($method),
                CURLOPT_ENCODING => 'utf-8',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $url,
                CURLOPT_USERAGENT => 'Idio API PHP Library',
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => $headers,
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
        $content = $this->exec();
        return new Response($content, $this);
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
        return $this->handle;
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
        return curl_exec($this->handle);
    }

    /**
     * Set cURL Options
     *
     * Wrapper for curl_setopt_array
     *
     * @param array $options Array of cURL options
     * @codeCoverageIgnore
     */
    protected function setOptions($options)
    {
        curl_setopt_array($this->handle, $options);
    }
}

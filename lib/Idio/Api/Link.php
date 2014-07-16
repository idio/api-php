<?php

namespace Idio\Api;

/**
 * Link
 *
 * Class to handle manipulation of click tracking links
 *
 * Example Usage
 *
 * $link = new IdioApi\Link('http://a.idio.co/r?o=1&d=1&u=http%3A%2F%2Fwww.idioplatform.com%2F&c=idio');
 * $link->setParameters(array(
 *     'x' => array(
 *         'idio' => 1234
 *     )
 * ));
 * $url = $link->get();
 *
 * @package IdioApi
 */
class Link
{
    /**
     * @var array Valid 'endpoints'
     *
     * If the URL we attempt to manipulate doesn't have one
     * of these as its path, we bail out
     */
    protected $validEndpoints = array(
        '/r',
        '/r/'
    );

    /**
     * @var array Link parts, as returned by parse_url and parse_str
     */
    protected $parts = array();

    /**
     * @var boolean Error state
     *
     * True if the link we're trying to manipulate doesn't look like one of ours
     */
    protected $error = false;

    /**
     * @var string The original link passed in
     */
    protected $url = false;

    /**
     * Link Constructor
     *
     * @param string $url Link to manipulate
     * @return Idio\Api\Link Link Object
     */
    public function __construct($url)
    {
        $this->url = $url;

        $this->parts = parse_url($url);
        if (isset($this->parts['query'])) {
            // PHP is stupid.
            parse_str($this->parts['query'], $this->parts['query']);
        } else {
            $this->parts['query'] = array();
        }

        // Failed to parse the URL?
        if (empty($this->parts['path'])) {
            $this->error = true;
        }

        // Is it an idio click tracking link?
        if (!in_array($this->parts['path'], $this->validEndpoints)) {
            $this->error = true;
        }
    }

    /**
     * Set Parameters
     *
     * @param array $params Array of parameters to merge into the URL
     * @return Idio\Api\Link (for chaining)
     */
    public function setParameters($params)
    {
        $this->parts['query'] = array_replace_recursive(
            $this->parts['query'],
            $params
        );
        return $this;
    }

    /**
     * Get Link
     *
     * @return string The manipulated version of the original URL.
     */
    public function get()
    {
        if ($this->error) {
            return $this->url;
        }

        // Work in the scope of this method so we don't stomp over the
        // original, in case for some reason more changes are needed.
        $parts = $this->parts;
        $parts['query'] = http_build_query($parts['query']);

        return http_build_url($parts);
    }

    /**
     * To String (Magic Method)
     *
     * Convenient wrapper for get() when debugging
     *
     * @return string The manipulated version of the original URL.
     */
    public function __toString()
    {
        return $this->get();
    }
}

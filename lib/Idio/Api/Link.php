<?php

namespace Idio\Api;

/**
 * Link
 * 
 * Class to handle manipulation of click tracking links
 *
 * Example Usage
 * 
 * $objLink = new IdioApi\Link('http://a.idio.co/r?o=1&d=1&u=http%3A%2F%2Fwww.idioplatform.com%2F&c=idio');
 * $objLink->setParameters(array(
 *     'x' => array(
 *         'idio' => 1234
 *     )
 * ));
 * $strNewLink = $objLink->get();
 *
 * @package IdioApi
 */
class Link
{

    // Valid 'endpoints' - if the URL we attempt to manipulate doesn't have one
    // of these as its path, we bail out
    protected $arrValidEndpoints = array(
        '/r',
        '/r/'
    );

    // Array of link parts, as returned by parse_url and parse_str
    protected $arrLinkParts = array();

    // Error state (true if the link we're trying to manipulate doesn't look
    // like one of ours)
    protected $blnError = false;

    // The original link passed in
    protected $strLink = false;

    /**
     * Link Constructor
     *
     * @param string $strLink Link to manipulate
     *
     * @return Idio\Api\Link Link Object
     */
    public function __construct($strLink)
    {
        $this->strLink = $strLink;

        $this->arrLinkParts = parse_url($strLink);
        if (isset($this->arrLinkParts['query'])) {
            // PHP is stupid.
            parse_str($this->arrLinkParts['query'], $this->arrLinkParts['query']);
        } else {
            $this->arrLinkParts['query'] = array();
        }

        // Failed to parse the URL?
        if (empty($this->arrLinkParts['path'])) {
            $this->blnError = true;
        }

        // Is it an idio click tracking link?
        if (!in_array($this->arrLinkParts['path'], $this->arrValidEndpoints)) {
            $this->blnError = true;
        }
    }

    /**
     * Set Parameters
     *
     * @param array $arrParameters Array of parameters to merge into the URL
     *
     * @return Idio\Api\Link Link Object (for chaining purposes)
     */
    public function setParameters($arrParameters)
    {
        $this->arrLinkParts['query'] = array_replace_recursive(
            $this->arrLinkParts['query'],
            $arrParameters
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
        if ($this->blnError) {
            return $this->strLink;
        }

        // Work in the scope of this method so we don't stomp over the
        // original, in case for some reason more changes are needed.
        $arrLinkParts = $this->arrLinkParts;
        $arrLinkParts['query'] = http_build_query($arrLinkParts['query']);

        return http_build_url($arrLinkParts);
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

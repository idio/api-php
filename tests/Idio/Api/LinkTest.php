<?php

namespace Idio\Api;

include_once('vendor/autoload.php');

/**
 * Link Tests
 *
 * Tests for the Idio Api Link Manipulation Class
 *
 * @package Idio\Api
 * @author Oliver Byford <oliver.byford@idioplatform.com>
 */
class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test manipulating an invalid URL should have no effect
     */
    public function testInvalidUrl()
    {
        $strLink = "LOL I'm not really a link";
        $objLink = new Link($strLink);

        $objLink->setParameters(
            array(
                'x' => array(
                    'idio' => 12345
                )
            )
        );

        $this->assertEquals(
            $strLink,
            $objLink->get(),
            "Expecting to get the original input to be returned, despite attempts to manipulate it"
        );
    }

    /**
     * Test manipulating a URL that is not a click tracking URL should have no
     * effect
     */
    public function testNotAClickTrackingUrl()
    {
        $strLink = 'http://www.idioplatform.com/newsroom';
        $objLink = new Link($strLink);

        $objLink->setParameters(
            array(
                'x' => array(
                    'idio' => 12345
                )
            )
        );

        $this->assertEquals(
            $strLink,
            $objLink->get(),
            "Expecting to get the original URL to be returned, despite attempts to manipulate it"
        );
    }

    /**
     * Test manipulating a URL that doesn't already have a query string
     *  => ?a=1
     */
    public function testSetParametersNoQueryString()
    {
        $objLink = new Link('http://a.idio.co/r');
        $objLink->setParameters(
            array(
                'a' => 1
            )
        );

        $this->assertEquals(
            'http://a.idio.co/r?a=1',
            $objLink->get(),
            "Expecting the array parameter to be overwritten"
        );
    }

    /**
     * Test overwriting a simple parameter (a=1)
     * ?a=1 => ?a=2
     */
    public function testSetParametersOverwriteParameter()
    {
        $objLink = new Link('http://a.idio.co/r?a=1');
        $objLink->setParameters(
            array(
                'a' => 2
            )
        );

        $this->assertEquals(
            'http://a.idio.co/r?a=2',
            $objLink->get(),
            "Expecting the a parameter to be rewritten"
        );
    }

    /**
     * Test overwriting an array parameter 
     * ?a[a]=1&a[b]=2 => ?a=2
     */
    public function testSetParametersOverwriteArrayParameter()
    {
        $objLink = new Link('http://a.idio.co/r?a[a]=1&a[b]=2');
        $objLink->setParameters(
            array(
                'a' => 2
            )
        );

        $this->assertEquals(
            'http://a.idio.co/r?a=2',
            $objLink->get(),
            "Expecting the a parameter to be overwritten"
        );
    }

    /**
     * Test merging new values into an existing array parameter
     * ?a[a]=1&a[b]=2 => ?a[a]=1&a[b]=2&a[c]=3
     */
    public function testSetParametersMergeArrayParameter()
    {
        $objLink = new Link('http://a.idio.co/r?a[a]=1&a[b]=2');
        $objLink->setParameters(
            array(
                'a' => array(
                    'c' => 3
                )
            )
        );

        $this->assertEquals(
            'http://a.idio.co/r?a%5Ba%5D=1&a%5Bb%5D=2&a%5Bc%5D=3',
            $objLink->get(),
            "Expecting the new parameter to be merged in to the array"
        );
    }

    /**
     * Test overwriting individual values in an existing array parameter
     * ?a[a]=1&a[b]=2 => ?a[a]=1&a[b]=3
     */
    public function testSetParametersMergeOverwriteArrayParameter()
    {
        $objLink = new Link('http://a.idio.co/r?a[a]=1&a[b]=2');
        $objLink->setParameters(
            array(
                'a' => array(
                    'b' => 3
                )
            )
        );

        $this->assertEquals(
            'http://a.idio.co/r?a%5Ba%5D=1&a%5Bb%5D=3',
            $objLink->get(),
            "Expecting the array parameter to be overwritten"
        );
    }

    /**
     * Test unsetting parameters
     * ?a=1 => ?
     */
    public function testSetParametersUnsetParameter()
    {
        $objLink = new Link('http://a.idio.co/r?a=1');
        $objLink->setParameters(
            array(
                'a' => null
            )
        );

        $this->assertEquals(
            'http://a.idio.co/r',
            $objLink->get(),
            "Expecting the a parameter to be unset"
        );
    }

    /**
     * Test unsetting individual values in an array parameter
     * ?a[a]=1&a[b]=2 => ?a[a]=1
     */
    public function testSetParametersUnsetArrayParameter()
    {
        $objLink = new Link('http://a.idio.co/r?a[a]=1&a[b]=2');
        $objLink->setParameters(
            array(
                'a' => array(
                    'b' => null
                )
            )
        );

        $this->assertEquals(
            'http://a.idio.co/r?a%5Ba%5D=1',
            $objLink->get(),
            "Expecting the b value in the array to be unset"
        );
    }

    /**
     * Test that the setParameters function returns the object so
     * it can be chained
     */
    public function testSetParametersChainability()
    {
        $objLink = new Link('http://a.idio.co/r?a[a]=1&a[b]=2');
        $objResult = $objLink->setParameters(
            array(
                'a' => array(
                    'b' => 3
                )
            )
        );

        $this->assertEquals(
            $objLink,
            $objResult,
            "Expecting the object to be returned for chaining"
        );
    }

    /**
     * Test that multiple calls can be made to setParameters
     */
    public function testSetParametersMultipleCalls()
    {
        $objLink = new Link('http://a.idio.co/r?a=1');
        $objLink->setParameters(
            array(
                'b' => 2
            )
        );
        $objLink->setParameters(
            array(
                'c' => 3
            )
        );
        $this->assertEquals(
            'http://a.idio.co/r?a=1&b=2&c=3',
            $objLink->get(),
            "Expecting the array parameter to be overwritten"
        );
    }

    /**
     * Test that the object, when cast to a string, is the same as the
     * response from a call to get()
     */
    public function testToStringMagicMethod()
    {
        $objLink = new Link('http://a.idio.co/r?');

        $this->assertEquals(
            $objLink->get(),
            "{$objLink}",
            "Expecting string casting of object to match output of get()"
        );
    }
}

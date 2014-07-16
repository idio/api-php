<?php

namespace Idio\Api;

include_once('vendor/autoload.php');

/**
 * Client Tests
 *
 * Tests for the Idio Api Client Class
 *
 * @package Idio\Api
 * @author Oliver Byford <oliver.byford@idioplatform.com>
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test 'Set URL' Method
     */
    public function testSetUrlNoVersion()
    {
        $client = new Client();
        $client->setUrl('http://api.idio.co');

        $this->assertEquals(
            'http://api.idio.co',
            $client->getUrl(),
            "Expecting the URL to be set correctly"
        );
    }

    /**
     * Test 'Set URL' Method when providing a version
     */
    public function testSetUrlWithVersion()
    {
        $client = new Client();
        $client->setUrl('http://api.idio.co', '0.1');

        $this->assertEquals(
            'http://api.idio.co/0.1',
            $client->getUrl(),
            "Expecting the URL to be set correctly"
        );
    }

    /**
     * Test 'Set URL' Method when providing a version
     */
    public function testSetUrlChaining()
    {
        $client = new Client();

        $this->assertEquals(
            $client,
            $client->setUrl('http://api.idio.co'),
            "Expecting setUrl to return the object for chaining"
        );
    }

    /**
     * Test 'Get Version' Method with a version
     */
    public function testGetVersionWithVersion()
    {
        $client = new Client();
        $client->setUrl('http://api.idio.co', '0.1');

        $this->assertEquals(
            '0.1',
            $client->getVersion(),
            "Expecting the version to be returned correctly"
        );
    }

    /**
     * Test 'Get Version' Method with no version
     */
    public function testGetVersionNoVersion()
    {
        $client = new Client();
        $client->setUrl('http://api.idio.co');

        $this->assertEquals(
            false,
            $client->getVersion(),
            "Expecting the version to be returned correctly"
        );
    }

    /**
     * Test 'Get Headers' Method with no credentials
     */
    public function testGetHeadersNoCredentials()
    {
        $client = $this->getMockBuilder('Idio\Api\Client')
                          ->setMethods(array('buildSignature'))
                          ->getMock();

        $client->expects($this->never())
            ->method('buildSignature');

        $this->assertEquals(
            array(),
            $client->getHeaders('GET', '/'),
            "Expecting empty headers to be returned"
        );

    }

    /**
     * Test 'Get Headers' Method with only app credentials
     */
    public function testGetHeadersAppCredentials()
    {
        $client = $this->getMockBuilder('Idio\Api\Client')
                          ->setMethods(array('buildSignature'))
                          ->getMock();

        $client->expects($this->once())
            ->method('buildSignature')
            ->will($this->returnValue('generated_signature'));

        $client->setAppCredentials(
            'app_key',
            'app_secret'
        );

        $this->assertEquals(
            array(
                'X-App-Authentication: app_key:generated_signature'
            ),
            $client->getHeaders('GET', '/'),
            "Expecting app headers to be returned"
        );
    }

    /**
     * Test 'Get Headers' Method with app and delivery credentials
     */
    public function testGetHeadersAllCredentials()
    {
        $client = $this->getMockBuilder('Idio\Api\Client')
                          ->setMethods(array('buildSignature'))
                          ->getMock();

        $client->expects($this->exactly(2))
            ->method('buildSignature')
            ->will($this->returnValue('generated_signature'));

        $client->setAppCredentials(
            'app_key',
            'app_secret'
        );

        $client->setDeliveryCredentials(
            'delivery_key',
            'delivery_secret'
        );

        $this->assertEquals(
            array(
                'X-App-Authentication: app_key:generated_signature',
                'X-Delivery-Authentication: delivery_key:generated_signature',
            ),
            $client->getHeaders('GET', '/'),
            "Expecting app and delivery headers to be returned"
        );
    }

    /**
     * Test 'Get Headers' generates a valid signature
     */
    public function testBuildSignature()
    {
        $client = $this->getMockBuilder('Idio\Api\Client')
                          ->setMethods(array('date'))
                          ->getMock();

        // The signature changes by day, so stub the day to always
        // be the 1st January 2000...
        $client->expects($this->exactly(2))
            ->method('date')
            ->will($this->returnValue('2000-01-01'));

        $client->setAppCredentials(
            'abcdefghij',
            '1234567890'
        )->setDeliveryCredentials(
            'klmnopqrst',
            '0987654321'
        );

        $this->assertEquals(
            array(
                'X-App-Authentication: abcdefghij:Mjc4N2M4NWM5ZDc4NDg4ZTkyMmJhOTVlMTljYTZlMTg0MWZkYTBhNA==',
                'X-Delivery-Authentication: klmnopqrst:M2E2YTcxNmQxNDZmNWRjZTRhMGZmM2RjNjNhZmQ1OWY1NTYwMWZkMA=='
            ),
            $client->getHeaders('GET', '/test'),
            "Expecting the signatures to be correct"
        );
    }

    /**
     * Test Signature provided by getHeaders is affected by the version prefix
     */
    public function testBuildSignatureWithVersion()
    {
        $client = new Client();

        $client->setAppCredentials(
            'abcdefghij',
            '1234567890'
        );

        $client->setDeliveryCredentials(
            'klmnopqrst',
            '0987654321'
        );

        $headers = $client->getHeaders('GET', '/test');

        $client->setUrl('', 'version');

        $versionedHeaders = $client->getHeaders('GET', '/test');

        $this->assertNotEquals(
            $headers,
            $versionedHeaders,
            "Expecting the version number to affect the signature"
        );
    }

    /**
     * Test Signature provided by getHeaders is not affected by the query string
     */
    public function testBuildSignatureNoQueryString()
    {
        $client = new Client();

        $client->setAppCredentials(
            'abcdefghij',
            '1234567890'
        );

        $client->setDeliveryCredentials(
            'klmnopqrst',
            '0987654321'
        );

        $this->assertEquals(
            $client->getHeaders('GET', '/test'),
            $client->getHeaders('GET', '/test?a=b'),
            "Expecting the query string to have no effect on the signature"
        );
    }

    /**
     * Test the request method returns a Request object
     */
    public function testRequest()
    {
        $client = new Client();
        $this->assertInstanceOf(
            'Idio\Api\Request',
            $client->request('GET', '/test'),
            "Expecting a Idio\Api\Request object to be returned"
        );
    }

    /**
     * Test the batch method returns a Batch object
     */
    public function testBatch()
    {
        $client = new Client();
        $this->assertInstanceOf(
            'Idio\Api\Batch',
            $client->batch(array()),
            "Expecting a Idio\Api\Batch object to be returned"
        );
    }

    /**
     * Test the link method returns a Link object
     */
    public function testLink()
    {
        $client = new Client();
        $this->assertInstanceOf(
            'Idio\Api\Link',
            $client->link(''),
            "Expecting a Idio\Api\Link object to be returned"
        );
    }
}

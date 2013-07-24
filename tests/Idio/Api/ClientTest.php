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
        $objClient = new Client();
        $objClient->setUrl('http://api.idio.co');

        $this->assertEquals(
            'http://api.idio.co/',
            $objClient->getUrl(),
            "Expecting the URL to be set correctly"
        );
    }

    /**
     * Test 'Set URL' Method when providing a version
     */
    public function testSetUrlWithVersion()
    {
        $objClient = new Client();
        $objClient->setUrl('http://api.idio.co', '0.1');

        $this->assertEquals(
            'http://api.idio.co/0.1',
            $objClient->getUrl(),
            "Expecting the URL to be set correctly"
        );
    }

    /**
     * Test 'Get Version' Method with a version
     */
    public function testGetVersionWithVersion()
    {
        $objClient = new Client();
        $objClient->setUrl('http://api.idio.co', '0.1');

        $this->assertEquals(
            '0.1',
            $objClient->getVersion(),
            "Expecting the version to be returned correctly"
        );
    }

    /**
     * Test 'Get Version' Method with no version
     */
    public function testGetVersionNoVersion()
    {
        $objClient = new Client();
        $objClient->setUrl('http://api.idio.co');

        $this->assertEquals(
            false,
            $objClient->getVersion(),
            "Expecting the version to be returned correctly"
        );
    }

    /**
     * Test 'Get Headers' Method with no credentials
     */
    public function testGetHeadersNoCredentials()
    {
        $objClient = $this->getMockBuilder('Idio\Api\Client')
                          ->setMethods(array('buildSignature'))
                          ->getMock();

        $objClient->expects($this->never())
            ->method('buildSignature');

        $this->assertEquals(
            array(),
            $objClient->getHeaders('GET', '/'),
            "Expecting empty headers to be returned"
        );

    }

    /**
     * Test 'Get Headers' Method with only app credentials
     */
    public function testGetHeadersAppCredentials()
    {
        $objClient = $this->getMockBuilder('Idio\Api\Client')
                          ->setMethods(array('buildSignature'))
                          ->getMock();

        $objClient->expects($this->once())
            ->method('buildSignature')
            ->will($this->returnValue('generated_signature'));

        $objClient->setAppCredentials(
            'app_key',
            'app_secret'
        );

        $this->assertEquals(
            array(
                'X-App-Authentication: app_key:generated_signature'
            ),
            $objClient->getHeaders('GET', '/'),
            "Expecting app headers to be returned"
        );
    }

    /**
     * Test 'Get Headers' Method with app and delivery credentials
     */
    public function testGetHeadersAllCredentials()
    {
        $objClient = $this->getMockBuilder('Idio\Api\Client')
                          ->setMethods(array('buildSignature'))
                          ->getMock();

        $objClient->expects($this->exactly(2))
            ->method('buildSignature')
            ->will($this->returnValue('generated_signature'));

        $objClient->setAppCredentials(
            'app_key',
            'app_secret'
        );

        $objClient->setDeliveryCredentials(
            'delivery_key',
            'delivery_secret'
        );

        $this->assertEquals(
            array(
                'X-App-Authentication: app_key:generated_signature',
                'X-Delivery-Authentication: delivery_key:generated_signature',
            ),
            $objClient->getHeaders('GET', '/'),
            "Expecting app and delivery headers to be returned"
        );
    }

    /**
     * Test 'Get Headers' generates a valid signature
     */
    public function testBuildSignature()
    {
        $objClient = $this->getMockBuilder('Idio\Api\Client')
                          ->setMethods(array('date'))
                          ->getMock();

        // The signature changes by day, so stub the day to always
        // be the 1st January 2000...
        $objClient->expects($this->exactly(2))
            ->method('date')
            ->will($this->returnValue('2000-01-01'));

        $objClient->setAppCredentials(
            'abcdefghij',
            '1234567890'
        );

        $objClient->setDeliveryCredentials(
            'klmnopqrst',
            '0987654321'
        );

        $this->assertEquals(
            array(
                'X-App-Authentication: abcdefghij:Mjc4N2M4NWM5ZDc4NDg4ZTkyMmJhOTVlMTljYTZlMTg0MWZkYTBhNA==',
                'X-Delivery-Authentication: klmnopqrst:M2E2YTcxNmQxNDZmNWRjZTRhMGZmM2RjNjNhZmQ1OWY1NTYwMWZkMA=='
            ),
            $objClient->getHeaders('GET', '/test'),
            "Expecting the signatures to be correct"
        );
    }

    /**
     * Test Signature provided by getHeaders is affected
     * by the version prefix
     */
    public function testBuildSignatureWithVersion()
    {
        $objClient = new Client();

        $objClient->setAppCredentials(
            'abcdefghij',
            '1234567890'
        );

        $objClient->setDeliveryCredentials(
            'klmnopqrst',
            '0987654321'
        );

        $arrHeaders = $objClient->getHeaders('GET', '/test');

        $objClient->setUrl('', 'version');

        $arrVersionedHeaders = $objClient->getHeaders('GET', '/test');

        $this->assertNotEquals(
            $arrHeaders,
            $arrVersionedHeaders,
            "Expect the version number to affect the signature"
        );
    }

    /**
     * Test Signature provided by getHeaders is not affected
     * by the query string
     */
    public function testBuildSignatureNoQueryString()
    {
        $objClient = new Client();

        $objClient->setAppCredentials(
            'abcdefghij',
            '1234567890'
        );

        $objClient->setDeliveryCredentials(
            'klmnopqrst',
            '0987654321'
        );

        $this->assertEquals(
            $objClient->getHeaders('GET', '/test'),
            $objClient->getHeaders('GET', '/test?a=b'),
            "Expecting the query string to have no effect on the signature"
        );
    }

    /**
     * Test the request method returns a Request object
     */
    public function testRequest()
    {
        $objClient = new Client();
        $this->assertInstanceOf(
            'Idio\Api\Request',
            $objClient->request('GET', '/test'),
            "Expecting a Idio\Api\Request object to be returned"
        );
    }

/**
     * Test the batch method returns a Batch object
     */
    public function testBatch()
    {
        $objClient = new Client();
        $this->assertInstanceOf(
            'Idio\Api\Batch',
            $objClient->batch(array()),
            "Expecting a Idio\Api\Batch object to be returned"
        );
    }

    /**
     * Test the link method returns a Link object
     */
    public function testLink()
    {
        $objClient = new Client();
        $this->assertInstanceOf(
            'Idio\Api\Link',
            $objClient->link(''),
            "Expecting a Idio\Api\Link object to be returned"
        );
    }
}

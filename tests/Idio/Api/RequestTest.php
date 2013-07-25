<?php

namespace Idio\Api;

/**
 * Request Tests
 *
 * Tests for the Idio Api Request Class
 *
 * @package Idio\Api
 * @author Oliver Byford <oliver.byford@idioplatform.com>
 */
class RequestTest extends \PHPUnit_Framework_TestCase
{

    protected $objRequest = false;

    /**
     * Set Up
     */
    public function setUp()
    {
        $this->objRequest = $this->getMockBuilder('Idio\Api\Request')
                   ->setMethods(array('handle', 'setOptions', 'exec'))
                   ->disableOriginalConstructor()
                   ->getMock();
    }

    /**
     * Test that the method is sent to cURL to be part of the request
     */
    public function testConstructorSetsMethod()
    {
        $this->objRequest->expects($this->once())
                   ->method('setOptions')
                   ->with($this->callback(function ($value) {
                               return isset($value[CURLOPT_CUSTOMREQUEST])
                                   && $value[CURLOPT_CUSTOMREQUEST] == 'GET';
                   }));

        $this->objRequest->__construct(new Client(), 'GET', '/test');
    }

    /**
     * Test that the path is sent to cURL to be part of the request
     */
    public function testConstructorSetsUrl()
    {
        $objClient = new Client();
        $objClient->setUrl('http://api.idio.co', '1.0');

        $this->objRequest->expects($this->once())
                   ->method('setOptions')
                   ->with($this->callback(function ($value) {
                       return isset($value[CURLOPT_URL])
                           && $value[CURLOPT_URL] == 'http://api.idio.co/1.0/test';
                   }));

        $this->objRequest->__construct($objClient, 'GET', '/test');
    }

    /**
     * Test that POST data is sent to cURL to be part of the request
     */
    public function testConstructorSetsData()
    {
        $arrData = array('a' => 'b');

        $this->objRequest->expects($this->once())
                   ->method('setOptions')
                   ->with($this->callback(function ($value) use ($arrData) {
                       return isset($value[CURLOPT_POSTFIELDS])
                           && $value[CURLOPT_POSTFIELDS] == json_encode($arrData);
                   }));

        $this->objRequest->__construct(new Client(), 'POST', '/test', $arrData);
    }

    /**
     * Test that the headers are sent to cURL to be part of the request
     */
    public function testConstructorSetsHeaders()
    {
        $arrHeaders = array('a' => 'b');

        $objClient = $this->getMock('Idio\Api\Client');
        $objClient->expects($this->once())
                  ->method('getHeaders')
                  ->will($this->returnValue($arrHeaders));

        $this->objRequest->expects($this->once())
                   ->method('setOptions')
                   ->with($this->callback(function ($value) use ($arrHeaders) {
                       return isset($value[CURLOPT_HTTPHEADER])
                           && $value[CURLOPT_HTTPHEADER] == $arrHeaders;
                   }));

        $this->objRequest->__construct($objClient, 'GET', '/test');
    }

    /**
     * Test that get handle returns the curl handle
     */
    public function testGetHandle()
    {
        $objRequest = new Request(new Client(), 'GET', '/test');

        $this->assertTrue(
            is_resource($objRequest->getHandle()),
            "Expecting the handle to be returned"
        );
    }

    /**
     * Test that the send method returns a suitable Response object
     */
    public function testSend()
    {
        $this->objRequest->expects($this->once())
             ->method('exec')
             ->will($this->returnValue('beans'));

        $this->objRequest->__construct(new Client(), 'GET', '/test');

        $objResponse = $this->objRequest->send();

        $this->assertInstanceOf(
            'Idio\Api\Response',
            $objResponse,
            "Expecting a Response object to be returned"
        );

        $this->assertEquals(
            "{$objResponse}",
            'beans',
            "Expecting the response to have the correct body"
        );
    }
}

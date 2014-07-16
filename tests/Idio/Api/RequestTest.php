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

    protected $request = false;

    /**
     * Set Up
     */
    public function setUp()
    {
        $this->request = $this->getMockBuilder('Idio\Api\Request')
                   ->setMethods(array('handle', 'setOptions', 'exec'))
                   ->disableOriginalConstructor()
                   ->getMock();
    }

    /**
     * Test that the method is sent to cURL to be part of the request
     */
    public function testConstructorSetsMethod()
    {
        $this->request->expects($this->once())
                   ->method('setOptions')
                   ->with($this->callback(function ($value) {
                               return isset($value[CURLOPT_CUSTOMREQUEST])
                                   && $value[CURLOPT_CUSTOMREQUEST] == 'GET';
                   }));

        $this->request->__construct(new Client(), 'GET', '/test');
    }

    /**
     * Test that the path is sent to cURL to be part of the request
     */
    public function testConstructorSetsUrl()
    {
        $client = new Client();
        $client->setUrl('http://api.idio.co', '1.0');

        $this->request->expects($this->once())
                   ->method('setOptions')
                   ->with($this->callback(function ($value) {
                       return isset($value[CURLOPT_URL])
                           && $value[CURLOPT_URL] == 'http://api.idio.co/1.0/test';
                   }));

        $this->request->__construct($client, 'GET', '/test');
    }

    /**
     * Test that POST data is sent to cURL to be part of the request
     */
    public function testConstructorSetsData()
    {
        $data = array('a' => 'b');

        $this->request->expects($this->once())
                   ->method('setOptions')
                   ->with($this->callback(function ($value) use ($data) {
                       return isset($value[CURLOPT_POSTFIELDS])
                           && $value[CURLOPT_POSTFIELDS] == json_encode($data);
                   }));

        $this->request->__construct(new Client(), 'POST', '/test', $data);
    }

    /**
     * Test that the headers are sent to cURL to be part of the request
     */
    public function testConstructorSetsHeaders()
    {
        $headers = array('a' => 'b');

        $client = $this->getMock('Idio\Api\Client');
        $client->expects($this->once())
                  ->method('getHeaders')
                  ->will($this->returnValue($headers));

        $this->request->expects($this->once())
                   ->method('setOptions')
                   ->with($this->callback(function ($value) use ($headers) {
                       return isset($value[CURLOPT_HTTPHEADER])
                           && $value[CURLOPT_HTTPHEADER] == $headers;
                   }));

        $this->request->__construct($client, 'GET', '/test');
    }

    /**
     * Test that get handle returns the curl handle
     */
    public function testGetHandle()
    {
        $request = new Request(new Client(), 'GET', '/test');

        $this->assertTrue(
            is_resource($request->getHandle()),
            "Expecting the handle to be returned"
        );
    }

    /**
     * Test that the send method returns a suitable Response object
     */
    public function testSend()
    {
        $this->request->expects($this->once())
             ->method('exec')
             ->will($this->returnValue('beans'));

        $this->request->__construct(new Client(), 'GET', '/test');

        $response = $this->request->send();

        $this->assertInstanceOf(
            'Idio\Api\Response',
            $response,
            "Expecting a Response object to be returned"
        );

        $this->assertEquals(
            "{$response}",
            'beans',
            "Expecting the response to have the correct body"
        );
    }
}

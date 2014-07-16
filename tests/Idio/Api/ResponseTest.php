<?php

namespace Idio\Api;

/**
 * Response Tests
 *
 * Tests for the Idio Api Request Class
 *
 * @package Idio\Api
 * @author Oliver Byford <oliver.byford@idioplatform.com>
 */
class ResponseTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Set Up
     */
    public function setUp()
    {
        $this->request = $this->getMockBuilder('Idio\Api\Request')
                                 ->disableOriginalConstructor();

        $this->response = $this->getMockBuilder('Idio\Api\Response')
                                  ->setMethods(
                                      array('info')
                                  )
                                  ->disableOriginalConstructor()
                                  ->getMock();
    }

    /**
     * Test that the Constructor tries to get the curl info from the original
     * request
     */
    public function testConstructor()
    {
        $this->response->expects($this->once())
             ->method('info')
             ->with($this->request);

        $this->response->__construct('', $this->request);
    }

    /**
     * Test that get status returns the http status code as returned as part of
     * curl's info
     */
    public function testGetStatus()
    {
        $this->response->expects($this->once())
             ->method('info')
             ->with($this->request)
             ->will(
                 $this->returnValue(
                     array(
                         'http_code' => 12345
                     )
                 )
             );

        $this->response->__construct('', $this->request);

        $this->assertEquals(
            12345,
            $this->response->getStatus(),
            "Expecting the HTTP status code to be returned"
        );
    }

    /**
     * Test that getBody returns the response body as an array
     */
    public function testGetBodyAsArray()
    {
        $expected = array(
            'a' => 1
        );

        $this->response->__construct(
            json_encode($expected),
            $this->request
        );

        $this->assertEquals(
            $expected,
            $this->response->getBody(),
            "Expecting the response body to be returned"
        );
    }

    /**
     * Test that getBody returns the response body as an object
     */
    public function testGetBodyAsObject()
    {
        $expected = array(
            'a' => 1
        );

        $this->response->__construct(
            json_encode($expected),
            $this->request
        );

        $this->assertEquals(
            (object)$expected,
            $this->response->getBody(true),
            "Expecting the response body to be returned"
        );
    }

    /**
     * Test that the object, when cast to a string, is the same as the response
     * body
     */
    public function testToStringMagicMethod()
    {
        $this->response->__construct('hi', $this->request);

        $this->assertEquals(
            'hi',
            "{$this->response}",
            "Expecting string casting of object to match the response body"
        );
    }
}

<?php

namespace Idio\Api;

class ResponseTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->objRequest  = $this->getMockBuilder('Idio\Api\Request')
                                  ->disableOriginalConstructor();

        $this->objResponse = $this->getMockBuilder('Idio\Api\Response')
                                  ->setMethods(
                                      array('info')
                                  )
                                  ->disableOriginalConstructor()
                                  ->getMock();
    }

    /**
     * Test that the Constructor tries to get the 
     * curl info from the original request
     */
    public function testConstructor()
    {
        $this->objResponse->expects($this->once())
             ->method('info')
             ->with($this->objRequest);

        $this->objResponse->__construct('', $this->objRequest);
    }

    /**
     * Test that get status returns the http status
     * code as returned as part of curl's info
     */
    public function testGetStatus()
    {
        $this->objResponse->expects($this->once())
             ->method('info')
             ->with($this->objRequest)
             ->will(
                 $this->returnValue(
                     array(
                         'http_code' => 12345
                     )
                 )
             );

        $this->objResponse->__construct('', $this->objRequest);

        $this->assertEquals(
            12345,
            $this->objResponse->getStatus(),
            "Expecting the HTTP status code to be returned"
        );
    }

    /**
     * Test that getBody returns the response body as an
     * array
     */
    public function testGetBodyAsArray()
    {
        $arrExpectedBody = array(
            'a' => 1
        );

        $this->objResponse->__construct(json_encode($arrExpectedBody), $this->objRequest);

        $this->assertEquals(
            $arrExpectedBody,
            $this->objResponse->getBody(),
            "Expect the response body to be returned"
        );
    }

    /**
     * Test that getBody returns the response body as an
     * object
     */
    public function testGetBodyAsObject()
    {
        $arrExpectedBody = array(
            'a' => 1
        );

        $this->objResponse->__construct(json_encode($arrExpectedBody), $this->objRequest);

        $this->assertEquals(
            (object)$arrExpectedBody,
            $this->objResponse->getBody(true),
            "Expect the response body to be returned"
        );
    }

    /**
     * Test that the object, when cast to a string, is the same as the
     * response body
     */
    public function testToStringMagicMethod()
    {
        $this->objResponse->__construct('hi', $this->objRequest);

        $this->assertEquals(
            'hi',
            "{$this->objResponse}",
            "Expecting string casting of object to match the response body"
        );
    }
}

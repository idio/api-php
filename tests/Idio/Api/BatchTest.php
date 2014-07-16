<?php

namespace Idio\Api;

include_once('vendor/autoload.php');

/**
 * Batch Tests
 *
 * Tests for the Idio Api Batch Request Class
 *
 * @package Idio\Api
 * @author Oliver Byford <oliver.byford@idioplatform.com>
 */
class BatchTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->client = new Client();
        $this->batch = $this->getMockBuilder('Idio\Api\Batch')
                                ->setMethods(
                                    array(
                                        'handle', 'addHandle', 'exec',
                                        'block', 'get', 'removeHandle', 'close'
                                    )
                                )
                                ->disableOriginalConstructor()
                                ->getMock();
    }

    /**
     * Test that the constructor creates a new curl multi handle
     */
    public function testConstructor()
    {
        $this->batch->expects($this->once())
             ->method('handle');

        $this->batch->__construct();
    }

    /**
     * Test that calling add with a Request will result in that Requests' handle
     * being added to the curl multi handle (via add)
     */
    public function testConstructorAddsValidRequests()
    {
        $request = $this->getMockBuilder('Idio\Api\Request')
                           ->disableOriginalConstructor()
                           ->getMock();

        $request->expects($this->once())
            ->method('getHandle')
            ->will($this->returnValue('handle!'));

        $this->batch->expects($this->once())
            ->method('addHandle')
            ->with($this->equalTo('handle!'));

        $this->batch->__construct(
            array(
                'request' => $request
            )
        );
    }

    /**
     * Test that the constructor ignores 'requests' which are not really
     * Requests at all.
     */
    public function testConstructorIgnoresValidRequests()
    {
        $this->batch->expects($this->never())
            ->method('addHandle');

        $this->batch->__construct(
            array(
                'request' => 'string'
            )
        );
    }

    /**
     * Test that calling add with a Request will result in that Requests' handle
     * being added to the curl multi handle
     */
    public function testAdd()
    {
        $request = $this->getMockBuilder('Idio\Api\Request')
                           ->disableOriginalConstructor()
                           ->getMock();

        $request->expects($this->once())
            ->method('getHandle')
            ->will($this->returnValue('handle!'));

        $this->batch->expects($this->once())
            ->method('addHandle')
            ->with($this->equalTo('handle!'));

        $this->batch->add('request', $request);
    }
}

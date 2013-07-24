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
        $this->objClient = new Client();
        $this->objBatch = $this->getMockBuilder('Idio\Api\Batch')
                                ->setMethods(
                                    array(
                                        'handle', 'addHandle', 'exec', 'block', 'get', 'removeHandle', 'close'
                                    )
                                )
                                ->disableOriginalConstructor()
                                ->getMock();
    }

    /**
     * Test that the constructor creates a new
     * curl multi handle
     */
    public function testConstructor()
    {
        $this->objBatch->expects($this->once())
             ->method('handle');

        $this->objBatch->__construct();
    }

    /**
     * Test that calling add with a Request will
     * result in that Requests' handle being
     * added to the curl multi handle (via add)
     */
    public function testConstructorAddsValidRequests()
    {
        $objRequest = $this->getMockBuilder('Idio\Api\Request')
                           ->disableOriginalConstructor()
                           ->getMock();

        $objRequest->expects($this->once())
            ->method('getHandle')
            ->will($this->returnValue('handle!'));

        $this->objBatch->expects($this->once())
            ->method('addHandle')
            ->with($this->equalTo('handle!'));

        $this->objBatch->__construct(
            array(
                'request' => $objRequest
            )
        );
    }

    /**
     * Test that the constructor ignores 'requests' 
     * which are not really Requests at all.
     */
    public function testConstructorIgnoresValidRequests()
    {
        $this->objBatch->expects($this->never())
            ->method('addHandle');

        $this->objBatch->__construct(
            array(
                'request' => 'string'
            )
        );
    }

    /**
     * Test that calling add with a Request will
     * result in that Requests' handle being
     * added to the curl multi handle
     */
    public function testAdd()
    {
        $objRequest = $this->getMockBuilder('Idio\Api\Request')
                           ->disableOriginalConstructor()
                           ->getMock();

        $objRequest->expects($this->once())
            ->method('getHandle')
            ->will($this->returnValue('handle!'));

        $this->objBatch->expects($this->once())
            ->method('addHandle')
            ->with($this->equalTo('handle!'));

        $this->objBatch->add('request', $objRequest);
    }
}

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
class ClientTest extends \PHPUnit_Framework_TestCase {

    /**
     * Test 'Set URL' Method
     */
    function testSetUrlNoVersion() {
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
    function testSetUrlWithVersion() {
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
    function testGetVersionWithVersion() {
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
    function testGetVersionNoVersion() {
        $objClient = new Client();
        $objClient->setUrl('http://api.idio.co');

        $this->assertEquals(
            false,
            $objClient->getVersion(),
            "Expecting the version to be returned correctly"
        );
    }

}
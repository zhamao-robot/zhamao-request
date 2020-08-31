<?php


use PHPUnit\Framework\TestCase;
use ZM\Requests\ZMRequest;

class ZMRequestTest extends TestCase
{
    public function testGet() {
        $this->assertIsString(ZMRequest::get("http://captive.apple.com"));
    }
}

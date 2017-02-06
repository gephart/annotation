<?php

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * @Route /home/
 */
class SuperClass
{
    /**
     * @Template {
     *     "url": "index.html"
     * }
     */
    public function index()
    {
    }
}

class ReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testClass()
    {
        $reader = new \Gephart\Annotation\Reader();
        $annotation = $reader->get("Route", SuperClass::class);
        $this->assertEquals("/home/", $annotation);
    }

    public function testMethod()
    {
        $reader = new \Gephart\Annotation\Reader();
        $annotation = $reader->get("Template", SuperClass::class, "index");
        $this->assertEquals(["url" => "index.html"], $annotation);
    }
}

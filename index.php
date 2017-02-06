<?php

require_once __DIR__ . '/vendor/autoload.php';

/**
 * @Route /home/
 */
class SuperClass
{
    /**
     * @Template {
     *     "url": "index.html"
     * }
     * @Route /home/
     */
    public function index()
    {
    }
}



        $reader = new \Gephart\Annotation\Reader();
        $annotation = $reader->get("Route", \SuperClass::class, "index");
var_dump($annotation);


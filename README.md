Gephart Annotation
===

[![Build Status](https://travis-ci.org/gephart/dependency-injection.svg?branch=master)](https://travis-ci.org/gephart/dependency-injection)

Dependencies
---
 - PHP >= 7.0

Instalation
---

```
composer require gephart/annotation
```

Using
---
```
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

$reader = new \Gephart\Annotation\Reader();
$annotation = $reader->get("Route", SuperClass::class);
// /home/

$annotation = $reader->get("Template", SuperClass::class, "index);
// ["url"=>"index.html"]
```
Gephart Annotation
===

[![php](https://github.com/gephart/annotation/actions/workflows/php.yml/badge.svg?branch=master)](https://github.com/gephart/annotation/actions)

Dependencies
---
 - PHP >= 7.1

Instalation
---

```
composer require gephart/annotation
```

Using
---

@AnnotationName value
@AnnotationName {"or anything":"in JSON"}

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
<?php

namespace Gephart\Annotation;

final class Reader
{
    private $annotation;
    private $class_name;
    private $method_name;

    public function get(string $annotation, string $class_name, string $method_name = null)
    {
        $this->annotation = $annotation;
        $this->class_name = $class_name;
        $this->method_name = $method_name;

        $raw = $this->getRawDoc($class_name, $method_name);
        $annotations = $this->parseAnnotation($raw);

        return !empty($annotations[$annotation]) ? $annotations[$annotation] : false;
    }

    private function getRawDoc(string $class_name, string $method_name = null): string
    {
        $rc = new \ReflectionClass($class_name);
        if ($method_name) {
            $doc = $rc->getMethod($method_name)->getDocComment();
        } else {
            $doc = $rc->getDocComment();
        }
        return trim($doc, "/");
    }

    private function parseAnnotation(string $raw_doc): array
    {
        preg_match_all("/@([A-Za-z0-9]+)([^@]*)/s", $raw_doc, $matches);

        $annotations = [];

        foreach ($matches[1] as $key => $annotation_name) {
            $annotation_name = trim($annotation_name);
            $annotation_value = $matches[2][$key];

            $annotation_value = $this->cleanAnnotationValue($annotation_value);
            $annotation_value = $this->validateValue($annotation_value);

            $annotations[$annotation_name] = trim($annotation_value);
        }

        return $annotations;
    }

    private function cleanAnnotationValue(string $annotation_value): string
    {
        $lines = explode("\n", $annotation_value);
        foreach ($lines as $key => $line) {
            $lines[$key] = trim($line, "* \t\r()");
        }

        return implode(" ", $lines);
    }

    private function validateValue(string $annotation_value)
    {
        $decode = json_decode($annotation_value, true);

        if (json_last_error()) {
            $decode = json_decode('"' . $annotation_value . '"', true);
        }

        if (json_last_error()) {
            $detail = "@" . $this->annotation . " in " . $this->class_name . ($this->method_name ? "::" . $this->method_name : "");
            throw new \Exception("Annotation value of '{$detail}' is not a valid JSON . ");
        }

        return $decode;
    }

}

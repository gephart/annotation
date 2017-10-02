<?php

namespace Gephart\Annotation;

use Gephart\Annotation\Exception\NotValidJsonException;

/**
 * Annotation Reader
 *
 * @package Gephart\Annotation
 * @author Michal Katuščák <michal@katuscak.cz>
 * @since 0.2
 */
final class Reader
{
    /**
     * @var string
     */
    private $class_name;

    /**
     * @var string
     */
    private $method_name;

    /**
     * @param string $annotation
     * @param string $class_name
     * @param string|null $method_name
     * @return mixed
     */
    public function get(string $annotation, string $class_name, string $method_name = null)
    {
        $annotations = $this->getAll($class_name, $method_name);

        return !empty($annotations[$annotation]) ? $annotations[$annotation] : false;
    }

    /**
     * @param string $class_name
     * @param string|null $method_name
     * @return array
     */
    public function getAll(string $class_name, string $method_name = null): array
    {
        $this->class_name = $class_name;
        $this->method_name = $method_name;

        $raw = $this->getRawDoc($class_name, $method_name);
        $annotations = $this->parseAnnotation($raw);

        return $annotations;
    }

    /**
     * @param string $class_name
     * @param string $property
     * @return array
     */
    public function getAllProperty(string $class_name, string $property): array
    {
        $reflection_class = new \ReflectionClass($class_name);
        $raw = trim($reflection_class->getProperty($property)->getDocComment(), "/");
        $annotations = $this->parseAnnotation($raw);

        return $annotations;
    }

    /**
     * @param string $class_name
     * @param string|null $method_name
     * @return string
     */
    private function getRawDoc(string $class_name, string $method_name = null): string
    {
        $reflection_class = new \ReflectionClass($class_name);

        if ($method_name) {
            $doc = $reflection_class->getMethod($method_name)->getDocComment();
        } else {
            $doc = $reflection_class->getDocComment();
        }

        return trim($doc, "/");
    }

    /**
     * @param string $raw_doc
     * @return array
     */
    private function parseAnnotation(string $raw_doc): array
    {
        preg_match_all("/@([A-Za-z0-9\\\\]+)([^@]*)/s", $raw_doc, $matches);

        $annotations = [];

        foreach ($matches[1] as $key => $annotation_name) {
            $annotation_name = trim($annotation_name);
            $annotation_value = $matches[2][$key];

            $annotation_value = $this->cleanValue($annotation_value);
            $annotation_value = $this->validateValue($annotation_name, $annotation_value);

            $annotations[$annotation_name] = $annotation_value;
        }

        return $annotations;
    }

    /**
     * @param string $annotation_value
     * @return string
     */
    private function cleanValue(string $annotation_value): string
    {
        $lines = explode("\n", $annotation_value);
        foreach ($lines as $key => $line) {
            $lines[$key] = trim($line, "* \t\r");
        }

        return trim(implode(" ", $lines));
    }

    /**
     * @since 0.4 Now throw \Gephart\Annotation\Exception\NotValidJsonException
     * @since 0.2
     *
     * @param string $annotation_name
     * @param string $annotation_value
     * @return mixed
     * @throws NotValidJsonException
     */
    private function validateValue(string $annotation_name, string $annotation_value)
    {
        $annotation_value = str_replace("\\", "\\\\", $annotation_value);
        $decode = json_decode($annotation_value, true);

        if (json_last_error()) {
            $decode = json_decode('"' . $annotation_value . '"', true);
        }

        if (json_last_error()) {
            $detail = "@" . $annotation_name . " in "
                . $this->class_name . ($this->method_name ? "::" . $this->method_name : "");
            throw new NotValidJsonException("Annotation value of '{$detail}' is not a valid JSON.");
        }

        return $decode;
    }
}

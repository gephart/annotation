<?php

namespace Gephart\Annotation;

use Gephart\Annotation\Exception\NotValidJsonException;
use ReflectionClass;
use Exception;

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
    private $className;

    /**
     * @var string|null
     */
    private $methodName;

    /**
     * @param string $annotation
     * @param string $className
     * @param string|null $methodName
     * @return mixed
     */
    public function get(string $annotation, string $className, string $methodName = null)
    {
        $annotations = $this->getAll($className, $methodName);

        return !empty($annotations[$annotation]) ? $annotations[$annotation] : false;
    }

    /**
     * @param string $className
     * @param string|null $methodName
     * @return array<mixed>
     */
    public function getAll(string $className, string $methodName = null): array
    {
        $this->className = $className;
        $this->methodName = $methodName;

        $raw = $this->getRawDoc($className, $methodName);
        $annotations = $this->parseAnnotation($raw);

        return $annotations;
    }

    /**
     * @param string $className
     * @param string $property
     * @return array<mixed>
     */
    public function getAllProperty(string $className, string $property): array
    {
        if (!class_exists($className)) {
            throw new Exception("Class $className not exist.");
        }

        $reflectionClass = new ReflectionClass($className);
        $doc = $reflectionClass->getProperty($property)->getDocComment();

        if (!$doc) {
            return [];
        }

        $raw = trim($doc, "/");
        $annotations = $this->parseAnnotation($raw);

        return $annotations;
    }

    /**
     * @param string $className
     * @param string|null $methodName
     * @return string
     */
    private function getRawDoc(string $className, string $methodName = null): string
    {
        if (!class_exists($className)) {
            throw new Exception("Class $className not exist.");
        }

        $reflectionClass = new ReflectionClass($className);

        $doc = $reflectionClass->getDocComment();

        if ($methodName) {
            $doc = $reflectionClass->getMethod($methodName)->getDocComment();
        }

        if (!$doc) {
            return "";
        }

        return trim($doc, "/");
    }

    /**
     * @param string $rawDoc
     * @return array<mixed>
     */
    private function parseAnnotation(string $rawDoc): array
    {
        preg_match_all("/@([A-Za-z0-9\\\\]+)([^@]*)/s", $rawDoc, $matches);

        $annotations = [];

        foreach ($matches[1] as $key => $annotationName) {
            $annotationName = trim($annotationName);
            $annotationValue = $matches[2][$key];

            $annotationValue = $this->cleanValue($annotationValue);
            $annotationValue = $this->validateValue($annotationName, $annotationValue);

            $annotations[$annotationName] = $annotationValue;
        }

        return $annotations;
    }

    /**
     * @param string $annotationValue
     * @return string
     */
    private function cleanValue(string $annotationValue): string
    {
        $lines = explode("\n", $annotationValue);
        foreach ($lines as $key => $line) {
            $lines[$key] = trim($line, "* \t\r");
        }

        return trim(implode(" ", $lines));
    }

    /**
     * @since 0.4 Now throw \Gephart\Annotation\Exception\NotValidJsonException
     * @since 0.2
     *
     * @param string $annotationName
     * @param string $annotationValue
     * @return mixed
     * @throws NotValidJsonException
     */
    private function validateValue(string $annotationName, string $annotationValue)
    {
        $annotationValue = str_replace("\\", "\\\\", $annotationValue);
        $decode = json_decode($annotationValue, true);

        if (json_last_error()) {
            $decode = json_decode('"' . $annotationValue . '"', true);
        }

        if (json_last_error()) {
            $detail = "@" . $annotationName . " in "
                . $this->className . ($this->methodName ? "::" . $this->methodName : "");
            throw new NotValidJsonException("Annotation value of '{$detail}' is not a valid JSON.");
        }

        return $decode;
    }
}

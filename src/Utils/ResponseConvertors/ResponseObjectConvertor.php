<?php

namespace Core\Utils\ResponseConvertors;

use Core\Interfaces\ResponseConvertorInterface;
use Psr\Http\Message\ResponseInterface;
use \stdClass;
use \SimpleXMLElement;
use \RuntimeException;

class ResponseObjectConvertor implements ResponseConvertorInterface
{

    use ResponseConverterTrait;
    
    protected ?ResponseInterface $response = null;

    public function __construct(?ResponseInterface $response = null)
    {
        $this->response = $response;
    }

    public function get(?ResponseInterface $response = null): object
    {
        if ($response !== null) {
            $this->response = $response;
        }

        if (empty($this->response)) {
            throw new RuntimeException('Response is empty');
        }

        $contentType = $this->response->getHeaderLine('Content-Type');

        $body = (string) $this->response->getBody();

        if (stripos($contentType, 'application/json') !== false) {
            return $this->convertJsonToObject($body);
        } elseif (stripos($contentType, 'application/xml') !== false) {
            return $this->convertXmlToObject($body);
        } elseif (stripos($contentType, 'text/csv') !== false) {
            return $this->convertCsvToObject($body);
        } else {
            throw new RuntimeException('Unsupported data format: ' . $contentType);
        }
    }

    protected function convertJsonToObject(string $body): object
    {

        $data = json_decode($body);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Failed to decode JSON: ' . json_last_error_msg());
        }

        return $data;
    }

    protected function convertXmlToObject(string $body): object
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new RuntimeException('Failed to parse XML: ' . $this->formatXmlErrors($errors));
        }

        $data = $this->normalizeXmlToObject($xml);

        return $data;
    }

    protected function normalizeXmlToObject(SimpleXMLElement $xml): object
    {
        $result = new stdClass();

        $attributes = $xml->attributes() ?? [];

        foreach ($attributes as $name => $value) {
            $result->{'@attributes'}[$name] = (string) $value;
        }

        foreach ($xml as $element) {
            $key = $element->getName();
            $value = ($element->count() > 0) ? $this->normalizeXmlToObject($element) : (string) $element;

            if (isset($result->$key)) {
                if (!is_array($result->$key) || !isset($result->$key[0])) {
                    $result->$key = [$result->$key];
                }

                $result->$key[] = $value;
            } else {
                $result->$key = $value;
            }
        }

        return $result;
    }

    protected function convertCsvToObject(string $body): object
    {
        return (object) $this->convertCsvToArray($body);
    }

}

<?php

namespace Core\Utils\ResponseConvertors;

use Core\Interfaces\ResponseConvertorInterface;
use Psr\Http\Message\ResponseInterface;
use \SimpleXMLElement;
use \RuntimeException;

class ResponseArrayConvertor implements ResponseConvertorInterface
{

    use ResponseConverterTrait;
    
    protected ?ResponseInterface $response = null;

    public function __construct(?ResponseInterface $response = null)
    {
        $this->response = $response;
    }

    public function get(?ResponseInterface $response = null): array
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
            return $this->convertJsonToArray($body);
        } elseif (stripos($contentType, 'application/xml') !== false) {
            return $this->convertXmlToArray($body);
        } elseif (stripos($contentType, 'text/csv') !== false) {
            return $this->convertCsvToArray($body);
        } else {
            throw new RuntimeException('Unsupported data format: ' . $contentType);
        }
    }

    protected function convertJsonToArray(string $body): array
    {
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Failed to decode JSON: ' . json_last_error_msg());
        }

        return $data;
    }

    protected function convertXmlToArray(string $body): array
    {
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);

        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            throw new RuntimeException('Failed to parse XML: ' . $this->formatXmlErrors($errors));
        }

        $data = $this->normalizeXmlToArray($xml);

        return $data;
    }

    protected function normalizeXmlToArray(SimpleXMLElement $xml): array
    {
        $result = [];

        $attributes = $xml->attributes() ?? [];

        foreach ($attributes as $name => $value) {
            $result['@attributes'][$name] = (string) $value;
        }

        foreach ($xml as $element) {
            $key = $element->getName();
            $value = ($element->count() > 0) ? $this->normalizeXmlToArray($element) : (string) $element;

            if (isset($result[$key])) {
                if (!is_array($result[$key]) || !isset($result[$key][0])) {
                    $result[$key] = [$result[$key]];
                }

                $result[$key][] = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

}

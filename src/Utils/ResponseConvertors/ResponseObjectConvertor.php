<?php

declare(strict_types=1);

namespace Core\Utils\ResponseConvertors;

use Core\Interfaces\ResponseConvertorInterface;
use Psr\Http\Message\ResponseInterface;
use \stdClass;
use \SimpleXMLElement;
use \RuntimeException;

/**
 * ResponseObjectConvertor is a response convertor class that converts
 * response bodies to objects.
 */
class ResponseObjectConvertor implements ResponseConvertorInterface
{

    use ResponseConverterTrait;

    /**
     * PSR Response
     * 
     * @var ResponseInterface|null
     */
    protected ?ResponseInterface $response = null;

    /**
     * Create a new ResponseObjectConvertor instance.
     *
     * @param ResponseInterface|null $response The response object to be converted (optional).
     */
    public function __construct(?ResponseInterface $response = null)
    {
        $this->response = $response;
    }

    /**
     * Get the converted response body as an object.
     *
     * @param ResponseInterface|null $response The response object to be converted (optional).
     * @return object The converted response body as an object.
     * @throws RuntimeException If the response is empty or if the data format is unsupported.
     */
    public function get(?ResponseInterface $response = null): object
    {
        if ($response !== null) {
            $this->response = $response;
        }

        if (empty($this->response)) {
            throw new RuntimeException('Response is empty');
        }

        $contentType = $this->getContentType($this->response);

        $body = (string) $this->response->getBody();

        if ($contentType === 'application/json') {
            return $this->convertJsonToObject($body);
        } elseif ($contentType === 'application/xml') {
            return $this->convertXmlToObject($body);
        } elseif ($contentType === 'text/csv') {
            return $this->convertCsvToObject($body);
        } else {
            throw new RuntimeException('Unsupported data format: ' . $contentType ?? 'Not defined');
        }
    }

    /**
     * Convert a JSON string to an object.
     *
     * @param string $body The JSON string.
     * @return object The converted object.
     * @throws RuntimeException If the JSON decoding fails.
     */
    public function convertJsonToObject(string $body): object
    {

        $data = json_decode($body);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Failed to decode JSON: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Convert an XML string to an object.
     *
     * @param string $body The XML string.
     * @return object The converted object.
     * @throws RuntimeException If the XML parsing fails.
     */
    public function convertXmlToObject(string $body): object
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

    /**
     * Normalize a SimpleXMLElement object to an object.
     *
     * @param SimpleXMLElement $xml The SimpleXMLElement object.
     * @return object The normalized object.
     */
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

    /**
     * Convert a CSV string to an object.
     *
     * @param string $body The CSV string.
     * @return stdClass The converted object.
     */
    public function convertCsvToObject(string $body): stdClass
    {
        $lines = preg_split('/\r\n|\r|\n/', $body);
        $data = new stdClass();

        $delimiter = $this->detectCsvDelimiter($lines);

        $data->rows = [];

        foreach ($lines as $line) {
            $row = new stdClass();
            $row->data = new stdClass();

            $rowSeparated = str_getcsv($line, $delimiter);
            $rowWithoutEmpty = array_filter($rowSeparated);

            $row->data->cells = $rowWithoutEmpty;
            $data->rows[] = $row;
        }

        return $data;
    }

}

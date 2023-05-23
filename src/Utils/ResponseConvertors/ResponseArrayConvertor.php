<?php

declare(strict_types=1);

namespace Core\Utils\ResponseConvertors;

use Core\Interfaces\ResponseConvertorInterface;
use Psr\Http\Message\ResponseInterface;
use \SimpleXMLElement;
use \RuntimeException;
use function json_decode,
             json_last_error,
             json_last_error_msg,
             libxml_use_internal_errors,
             simplexml_load_string,
             libxml_get_errors,
             libxml_clear_errors,
             is_array,
             str_getcsv,
             array_filter;
use const JSON_ERROR_NONE;

/**
 * ResponseArrayConvertor is a class that implements the ResponseConvertorInterface
 * and provides methods to convert response bodies to an array format.
 */
class ResponseArrayConvertor implements ResponseConvertorInterface
{

    use ResponseConverterTrait;

    /**
     * PSR Response
     * 
     * @var ResponseInterface|null
     */
    protected ?ResponseInterface $response = null;

    /**
     * Create a new ResponseArrayConvertor instance.
     *
     * @param ResponseInterface|null $response The response to convert (optional).
     */
    public function __construct(?ResponseInterface $response = null)
    {
        $this->response = $response;
    }

    /**
     * Get the converted response body as an array.
     *
     * @param ResponseInterface|null $response The response object to be converted (optional).
     * @return array The converted response body as an array.
     * @throws RuntimeException If the response is empty or if the data format is unsupported.
     */
    public function get(?ResponseInterface $response = null): array
    {
        if ($response !== null) {
            $this->response = $response;
        }

        if (empty($this->response)) {
            throw new RuntimeException('Response is empty');
        }

        $contentType = $this->getContentType($response);

        $body = (string) $this->response->getBody();

        if ($contentType === 'application/json') {
            return $this->convertJsonToArray($body);
        } elseif ($contentType === 'application/xml') {
            return $this->convertXmlToArray($body);
        } elseif ($contentType === 'text/csv') {
            return $this->convertCsvToArray($body);
        } else {
            throw new RuntimeException('Unsupported data format: ' . $contentType ?? 'not defined');
        }
    }

    /**
     * Convert a JSON string to an array.
     *
     * @param string $body The JSON string.
     * @return array The converted array.
     * @throws RuntimeException If the JSON decoding fails.
     */
    public function convertJsonToArray(string $body): array
    {
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Failed to decode JSON: ' . json_last_error_msg());
        }

        return $data;
    }

    /**
     * Convert an XML string to an array.
     * 
     * @param string $body The XML string
     * @return array The converted array
     * @throws RuntimeException If the XML parsing fails.
     */
    public function convertXmlToArray(string $body): array
    {
        libxml_use_internal_errors(true);

        $xml = simplexml_load_string($body);

        if ($xml === false) {
            $errors = $this->formatXmlErrors(libxml_get_errors());
            libxml_clear_errors();
            throw new RuntimeException('Failed to parse XML: ' . $errors);
        }

        $result = $this->normalizeXmlToArray($xml);

        return $result;
    }

    /**
     * Normalizes a SimpleXMLElement object to an array recursively.
     *
     * @param SimpleXMLElement $xml The SimpleXMLElement object to normalize.
     * @return array The normalized array representation of the XML.
     */
    protected function normalizeXmlToArray(SimpleXMLElement $xml): array
    {
        $result = [];

        $children = $xml->children() ?? [];

        foreach ($children as $name => $element) {
            $data = $this->normalizeXmlToArray($element);

            if (isset($result[$name])) {
                //if (!is_array($result[$name])) {
                $result[$name] = [$result[$name]];
                //}
                $result[$name][] = $data;
            } else {
                $result[$name] = $data;
            }
        }

        return $result;
    }

    /**
     * Convert a CSV string to an array.
     *
     * @param string $body The CSV string.
     * @return array The converted array.
     */
    public function convertCsvToArray(string $body): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $body);
        $data = [];

        $delimiter = $this->detectCsvDelimiter($lines);

        foreach ($lines as $line) {
            $rowSeparated = str_getcsv($line, $delimiter);

            $rowWithoutEmpty = array_filter($rowSeparated);

            $data[] = $rowWithoutEmpty;
        }

        return $data;
    }

}

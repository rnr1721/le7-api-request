<?php

declare(strict_types=1);

namespace Core\Utils\ResponseConvertors;

use Psr\Http\Message\ResponseInterface;
use function sprintf,
             implode;

/**
 * ResponseConverterTrait provides common methods for converting response
 * bodies to different formats.
 */
trait ResponseConverterTrait
{

    /**
     * Detects the delimiter used in a CSV file based on the given lines.
     *
     * @param array $lines An array of CSV lines.
     * @return string The detected delimiter.
     */
    private function detectCsvDelimiter(array $lines): string
    {
        $delimiters = [',', ';', '\t'];

        foreach ($delimiters as $delimiter) {
            $counts = array_map(function ($line) use ($delimiter) {
                return substr_count($line, $delimiter);
            }, $lines);

            if (count(array_unique($counts)) === 1) {
                return $delimiter;
            }
        }

        return ',';
    }

    /**
     * Format XML errors as a string.
     *
     * @param array $errors The XML errors.
     * @return string The formatted error string.
     */
    protected function formatXmlErrors(array $errors): string
    {
        $messages = [];

        foreach ($errors as $error) {
            $messages[] = sprintf(
                    '[%s] %s (Line: %d, Column: %d)',
                    $error->level,
                    $error->message,
                    $error->line,
                    $error->column
            );
        }

        return implode(', ', $messages);
    }

    /**
     * Safe get Content-Type from ResponseInterface
     * 
     * @param ResponseInterface|null $response
     * @return string|null
     */
    protected function getContentType(?ResponseInterface $response): string|null
    {
        if ($response === null) {
            return null;
        }
        $contentType = $response->getHeaderLine('Content-Type');
        if (empty($contentType)) {
            return null;
        }
        $mimeParts = explode(';', $contentType);
        $dataType = trim($mimeParts[0]);
        return $dataType;
    }

}

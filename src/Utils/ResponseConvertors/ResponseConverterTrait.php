<?php

namespace Core\Utils\ResponseConvertors;

trait ResponseConverterTrait
{

    protected function convertCsvToArray(string $body): array
    {
        $lines = explode("\n", $body);
        $headers = str_getcsv($lines[0]);
        $headersCleared = array_filter($headers);
        $data = [];

        foreach ($lines as $line) {
            $row = str_getcsv($line);
            $data[] = array_combine($headersCleared, $row);
        }

        return $data;
    }

    protected function formatXmlErrors(array $errors): string
    {
        $messages = [];

        foreach ($errors as $error) {
            $messages[] = sprintf('[%s] %s (Line: %d, Column: %d)', $error->level, $error->message, $error->line, $error->column);
        }

        return implode(', ', $messages);
    }

}

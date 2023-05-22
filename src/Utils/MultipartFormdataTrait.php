<?php

declare(strict_types=1);

namespace Core\Utils;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\RequestInterface;
use \SplFileInfo;
use function uniqid,
             is_array,
             function_exists,
             finfo_open,
             finfo_file,
             finfo_close,
             mime_content_type;
use const FILEINFO_MIME_TYPE;

trait MultipartFormdataTrait
{

    protected StreamFactoryInterface $streamFactory;

    /**
     * Builds a multipart/form-data request with support for file uploads.
     *
     * @param RequestInterface $request
     * @param array $data
     * @return RequestInterface
     */
    protected function buildMultipartRequest(RequestInterface $request, array $data)
    {
        $boundary = uniqid();
        $requestWithHeader = $request->withHeader('Content-Type', 'multipart/form-data; boundary=' . $boundary);

        $body = $this->streamFactory->createStream();

        foreach ($data as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $item) {
                    $body->write("--{$boundary}\r\n");
                    if ($item instanceof SplFileInfo) {
                        $body->write("Content-Disposition: form-data; name=\"{$name}\"; filename=\"{$item->getFilename()}\"\r\n");
                        $body->write("Content-Type: {$this->getMimeType($item)}\r\n\r\n");
                        $body->write($item->openFile()->fread($item->getSize()) . "\r\n");
                    } else {
                        $body->write("Content-Disposition: form-data; name=\"{$name}[]\"\r\n\r\n");
                        $body->write("{$item}\r\n");
                    }
                }
            } else {
                $body->write("--{$boundary}\r\n");
                $body->write("Content-Disposition: form-data; name=\"{$name}\"\r\n\r\n");
                $body->write("{$value}\r\n");
            }
        }

        $body->write("--{$boundary}--\r\n");

        return $requestWithHeader->withBody($body);
    }

    /**
     * Get the MIME type of a file.
     *
     * @param SplFileInfo $file
     * @return string|null
     */
    protected function getMimeType(SplFileInfo $file)
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file->getPathname());
            finfo_close($finfo);
            return $mimeType !== false ? $mimeType : null;
        } elseif (function_exists('mime_content_type')) {
            return mime_content_type($file->getPathname());
        } else {
            return null;
        }
    }

}

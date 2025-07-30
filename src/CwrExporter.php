<?php

namespace LabelTools\PhpCwrExporter;

use LabelTools\PhpCwrExporter\Contracts\VersionInterface;

class CwrExporter
{
    private VersionInterface $version;

    public function __construct(VersionInterface $version)
    {
        $this->version = $version;
    }

    public function export(array $works, array $opts): string
    {
        // Retrieve pre-formatted lines from the version implementation
        $headerLines  = $this->version->renderHeader($opts);
        $detailLines  = $this->version->renderDetailLines($works, $opts);
        $trailerLines = $this->version->renderTrailer($opts);

        // Merge all lines into a single sequence
        $lines = array_merge($headerLines, $detailLines, $trailerLines);

        // Join with Windows CRLF and append a final newline
        return implode("\r\n", $lines) . "\r\n";
    }
}
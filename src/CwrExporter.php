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

    public function getVersion(): VersionInterface
    {
        return $this->version;
    }

    public function export(array $works, array $opts): string
    {
        // Set initial options
        $opts['group_count'] = 1; // single group per file
        $opts['transaction_count'] = count($works); // one transaction per work
        $opts['header_count'] = 2; // file header + group header

        // Retrieve pre-formatted lines from the version implementation
        $headerLines  = $this->version->renderHeader($opts);
        $detailLines  = $this->version->renderDetailLines($works, $opts);
        $opts['detail_count'] = count($detailLines); // Count the lines after they are generated
        $trailerLines = $this->version->renderTrailer($opts); // Pass all options

        // Merge all lines into a single sequence
        $lines = array_merge($headerLines, $detailLines, $trailerLines);

        // Join with Windows CRLF and append a final newline
        return implode("\r\n", $lines) . "\r\n";
    }
}
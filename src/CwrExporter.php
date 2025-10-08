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
        // Complete options with calculated counts
        $opts['group_count'] = 1; // single group per file
        $opts['transaction_count'] = count($works); // one transaction per work
        $opts['header_count'] = 2; // file header + group header
        $opts['detail_count'] = $this->version->countDetailLines($works, $opts);

        // Retrieve pre-formatted lines from the version implementation
        $headerLines  = $this->version->renderHeader($opts); // Pass all options
        $detailLines  = $this->version->renderDetailLines($works, $opts); // Pass all options
        $trailerLines = $this->version->renderTrailer($opts); // Pass all options

        // Merge all lines into a single sequence
        $lines = array_merge($headerLines, $detailLines, $trailerLines);

        // Join with Windows CRLF and append a final newline
        return implode("\r\n", $lines) . "\r\n";
    }
}
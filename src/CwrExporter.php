<?php

namespace LabelTools\PhpCwrExporter;

use LabelTools\PhpCwrExporter\Contracts\VersionInterface;

/**
 * Main exporter: takes a VersionImplementation and a collection of works,
 * and produces the full CWR file as a string.
 */
class CwrExporter
{
    protected VersionInterface $version;

    public function __construct(VersionInterface $version)
    {
        $this->version = $version;
    }

    /**
     * @param  array  $works  List of work data (models or arrays)
     * @return string         Full CWR flat-file contents (lines separated by \r\n)
     */
    public function export(array $works): string
    {
        // 1) Build the file-level control records (HDR, GRH, GRT, TRL)
        $lines = $this->version->buildControlRecords($works);

        // 2) Build each work transaction
        $transaction = 0;
        foreach ($works as $work) {
            $transaction++;
            $lines = array_merge(
                $lines,
                $this->version->buildWorkRecords($work, $transaction)
            );
        }

        // 3) Join with CRLF and ensure file ends with a newline
        return implode("\r\n", $lines) . "\r\n";
    }
}
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
    public function export(array $works, array $options = []): string
    {
        // Pass $options through to the Version implementation:
        $records = $this->version->buildControlRecords($works, $options);

        $txn = 0;
        foreach ($works as $work) {
            $txn++;
            $records = array_merge(
                $records,
                $this->version->buildWorkRecords($work, $txn)
            );
        }

        return implode("\r\n", $records) . "\r\n";
    }
}
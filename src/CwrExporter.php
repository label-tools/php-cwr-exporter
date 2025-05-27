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


    public function export(array $works, array $options = []): string
    {
        // 1) File header + group header
        $headerRecs = $this->version->buildControlRecords($works, $options, 'header');

        // 2) All work-by-work detail records
        $detailLines = [];
        $tx = 0;
        foreach ($works as $work) {
            $tx++;
            $seq = 0;
            foreach ($this->version->buildWorkRecords($work, $tx) as $rec) {
                $seq++;
                $detailLines[] = $rec->toString($tx, $seq);
            }
        }

        // 3) Group trailer + file trailer
        $trailerRecs = $this->version->buildControlRecords($works, $options, 'trailer');

        // 4) Stringify and join
        $lines = array_map(fn($r) => $r->toString(0, 0), $headerRecs)
               + $detailLines
               + array_map(fn($r) => $r->toString(0, 0), $trailerRecs);

        return implode("\r\n", $lines) . "\r\n";
    }
}
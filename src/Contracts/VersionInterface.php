<?php

namespace LabelTools\PhpCwrExporter\Contracts;

/**
 * Defines a CWR version implementation (e.g. 2.1, 2.2).
 */
interface VersionInterface
{
    /**
     * The version identifier used in the header (e.g. "2.1" or "2.2").
     */
    public function getVersion(): string;

    /**
     * Build the control records for the entire file:
     *  - HDR (file header)
     *  - GRH (group header) / GRT (group trailer) if applicable
     *  - TRL (file trailer)
     *
     * @param  array  $works  Collection of work data to export
     * @return RecordInterface[]
     */
    public function buildControlRecords(array $works): array;

    /**
     * Build all records for a single work transaction:
     *  - WRK, AUT, PWR, etc.
     *
     * @param  mixed  $work                 Your Work model or data array
     * @param  int    $transactionSequence  Incremental transaction number
     * @return RecordInterface[]
     */
    public function buildWorkRecords($work, int $transactionSequence): array;
}
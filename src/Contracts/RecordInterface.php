<?php

namespace LabelTools\PhpCwrExporter\Contracts;

/**
 * Represents a single fixed-width CWR record.
 */
interface RecordInterface
{
    /**
     * Build the fixed-width string for this record.
     *
     * @param int $transactionSequence  Sequence number of the transaction (per work)
     * @param int $recordSequence       Sequence number of this record within the transaction
     * @return string                   The 120-character CWR line
     */
    public function toString(int $transactionSequence, int $recordSequence): string;
}
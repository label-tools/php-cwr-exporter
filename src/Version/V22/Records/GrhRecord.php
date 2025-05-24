<?php

namespace LabelTools\PhpCwrExporter\Version\V22\Records;

use LabelTools\PhpCwrExporter\Contracts\RecordInterface;

/**
 * v2.2 Group Header (GRH) record — 167 chars
 */
class GrhRecord implements RecordInterface
{
    public function __construct(
        protected string $transactionType,
        protected int    $groupId,
        protected string $versionNumber   = '02.10',
        protected ?string $batchRequest   = '',
        protected ?string $submissionType = ''
    ) {}

    public function toString(int $transactionSequence, int $recordSequence): string
    {
        $line  = str_pad('GRH', 3);                                         // Record Type
        $line .= str_pad($this->transactionType, 3);                       // Transaction Type
        $line .= str_pad((string) $this->groupId, 5, '0', STR_PAD_LEFT);    // Group ID
        $line .= str_pad($this->versionNumber, 5);                         // Version Number  [oai_citation:1‡CWR19-1070R1_Functional_specifications_CWR_version_2-2_Rev2_2022-02-03_EN.pdf](file-service://file-PER8KbcWdAGez32gAw9FAW)
        $line .= str_pad($this->batchRequest   ?? '', 10);                 // Batch Request (opt)
        $line .= str_pad($this->submissionType ?? '', 2);                  // Submission/Distribution Type (opt)
        // pad out to full 167-char record length
        $line .= str_repeat(' ', 167 - mb_strlen($line));

        return $line;
    }
}
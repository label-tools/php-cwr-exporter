<?php

namespace LabelTools\PhpCwrExporter\Version\V22\Records;

use LabelTools\PhpCwrExporter\Contracts\RecordInterface;

/**
 * v2.2 Transmission Trailer (TRL) record — fixed-width 167 chars:
 *  • Record Type      1–3   = "TRL"
 *  • Group Count      4–8   (zero-padded)
 *  • Transaction Cnt  9–16  (zero-padded)
 *  • Record Cnt      17–24  (zero-padded)
 *  • Padding        25–167 = spaces
 *
 */
class TrlRecord implements RecordInterface
{
    public function __construct(
        protected int $groupCount,
        protected int $transactionCount,
        protected int $recordCount
    ) {}

    public function toString(int $transactionSequence, int $recordSequence): string
    {
        $line  = str_pad('TRL', 3);
        $line .= str_pad((string) $this->groupCount, 5, '0', STR_PAD_LEFT);
        $line .= str_pad((string) $this->transactionCount, 8, '0', STR_PAD_LEFT);
        $line .= str_pad((string) $this->recordCount, 8, '0', STR_PAD_LEFT);
        $line .= str_repeat(' ', 167 - mb_strlen($line));

        return $line;
    }
}

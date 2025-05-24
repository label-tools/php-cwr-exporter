<?php

namespace LabelTools\PhpCwrExporter\Version\V22\Records;

use LabelTools\PhpCwrExporter\Contracts\RecordInterface;

/**
 * v2.2 Publisher Territory of Control (SPT) record — fixed-width 167 chars
 *
 * Field Layout:
 *  • Record Prefix         1–19   = "SPT"+prefix
 *  • Interested Party #   20–28   (9 chars)
 *  • Constant (spaces)    29–34   (6 spaces)
 *  • PR Collection Share  35–39   (5 digits)
 *  • MR Collection Share  40–44   (5 digits)
 *  • SR Collection Share  45–49   (5 digits)
 *  • Territory Code       50–52   (3 digits)
 *  • Inclusion/Exclusion  53–53   (1 char, “I” or “E”)
 *  • Shares Change Flag   54–54   (1 char, “Y” or “N”)
 *  • Sequence #           55–57   (3 digits, incremental)
 *  • (rest padded to 167)
 *
 * Spec: SPT record format and validations  [oai_citation:1‡CWR19-1070R1_Functional_specifications_CWR_version_2-2_Rev2_2022-02-03_EN.pdf](file-service://file-PER8KbcWdAGez32gAw9FAW)
 */
class SptRecord implements RecordInterface
{
    public function __construct(
        protected string $interestedPartyNumber,
        protected int    $prCollectionShare,
        protected int    $mrCollectionShare,
        protected int    $srCollectionShare,
        protected string $territoryCode,
        protected string $inclusionExclusion,
        protected string $sharesChangeFlag,
        protected int    $sequence
    ) {}

    public function toString(int $transactionSequence, int $recordSequence): string
    {
        $line  = str_pad('SPT', 3);
        $line .= str_pad($this->interestedPartyNumber, 9);
        $line .= str_repeat(' ', 6);
        $line .= str_pad((string)$this->prCollectionShare, 5, '0', STR_PAD_LEFT);
        $line .= str_pad((string)$this->mrCollectionShare, 5, '0', STR_PAD_LEFT);
        $line .= str_pad((string)$this->srCollectionShare, 5, '0', STR_PAD_LEFT);
        $line .= str_pad($this->territoryCode, 3);
        $line .= $this->inclusionExclusion;
        $line .= $this->sharesChangeFlag;
        $line .= str_pad((string)$this->sequence, 3, '0', STR_PAD_LEFT);
        $line .= str_repeat(' ', 167 - mb_strlen($line));

        return $line;
    }
}
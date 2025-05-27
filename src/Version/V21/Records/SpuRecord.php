<?php

namespace LabelTools\PhpCwrExporter\Version\V21\Records;

use LabelTools\PhpCwrExporter\Contracts\RecordInterface;

/**
 * v2.2 Publisher Controlled by Submitter (SPU) record — fixed-width 167 chars
 *
 * Field Layout (start–length):
 *  • Record Prefix           1–19   = "SPU"+prefix
 *  • Publisher Sequence #   20–21   (2 digits, zero-padded)
 *  • Interested Party #     22–30   (9 chars)
 *  • Publisher Name         31–75   (45 chars)
 *  • Unknown Indicator      76–76   (1 char, blank)
 *  • Publisher Type         77–78   (2 chars)
 *  • Tax ID #               79–87   (9 chars, optional)
 *  • IPI Name #            88–98   (11 chars)
 *  • Submitter Agreement # 99–112  (14 chars, optional)
 *  • Society Agreement #   113–125  (14 chars, optional)
 *  • PR Society #         126–128  (3 chars, optional)
 *  • PR Ownership Share   129–133  (5 digits, implied decimal)
 *  • MR Society #         134–136  (3 chars, optional)
 *  • MR Ownership Share   137–141  (5 digits, implied decimal)
 *  • (rest padded to 167)
 *
 * Spec: SPU record format and validations  [oai_citation:0‡CWR19-1070R1_Functional_specifications_CWR_version_2-2_Rev2_2022-02-03_EN.pdf](file-service://file-PER8KbcWdAGez32gAw9FAW)
 */
class SpuRecord implements RecordInterface
{
    public function __construct(
        protected int    $publisherSequence,
        protected string $interestedPartyNumber,
        protected string $publisherName,
        protected string $publisherType,
        protected ?string $taxId,
        protected string $ipiNameNumber,
        protected ?string $submitterAgreementNumber,
        protected ?string $societyAgreementNumber,
        protected ?string $prSociety,
        protected int    $prOwnershipShare,
        protected ?string $mrSociety,
        protected int    $mrOwnershipShare
    ) {}

    public function toString(int $transactionSequence, int $recordSequence): string
    {
        $line  = str_pad('SPU', 3);
        $line .= str_pad((string)$this->publisherSequence, 2, '0', STR_PAD_LEFT);
        $line .= str_pad($this->interestedPartyNumber, 9);
        $line .= str_pad($this->publisherName, 45);
        $line .= ' '; // Unknown indicator always blank for SPU
        $line .= str_pad($this->publisherType, 2);
        $line .= str_pad($this->taxId ?? '', 9);
        $line .= str_pad($this->ipiNameNumber, 11);
        $line .= str_pad($this->submitterAgreementNumber ?? '', 14);
        $line .= str_pad($this->societyAgreementNumber ?? '', 14);
        $line .= str_pad($this->prSociety ?? '', 3);
        $line .= str_pad((string)$this->prOwnershipShare, 5, '0', STR_PAD_LEFT);
        $line .= str_pad($this->mrSociety ?? '', 3);
        $line .= str_pad((string)$this->mrOwnershipShare, 5, '0', STR_PAD_LEFT);
        $line .= str_repeat(' ', 167 - mb_strlen($line));

        return $line;
    }
}
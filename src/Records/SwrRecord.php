<?php

namespace LabelTools\PhpCwrExporter\Records;

use LabelTools\PhpCwrExporter\Contracts\RecordInterface;

/**
 * v2.2 Writer Controlled By Submitter (SWR) or Other Writer (OWR) record — fixed-width 167 chars:
 *  • Record Type              1–3   = "SWR" or "OWR"
 *  • Interested Party #       4–12  (9 chars)
 *  • Writer Last Name        13–57  (45 chars)
 *  • Writer First Name       58–87  (30 chars, optional)
 *  • Writer Unknown Indicator 88–88  (1 char, 'Y' or blank)
 *  • Writer Designation Code  89–90  (2 chars)
 *  • Tax ID #                91–99  (9 chars, optional)
 *  • IPI Name #             100–110 (11 chars, optional)
 *  • PR Affiliation Society #111–113 (3 chars, optional)
 *  • PR Ownership Share     114–118 (5 chars: implied decimal)
 *  • Padding                119–167 spaces
 *
 */
class SwrRecord implements RecordInterface
{
    public function __construct(
        protected string $recordType,                // 'SWR' or 'OWR'
        protected string $interestedPartyNumber,
        protected string $lastName,
        protected ?string $firstName,
        protected bool   $unknownIndicator,
        protected ?string $designationCode,
        protected ?string $taxId,
        protected ?string $ipiNameNumber,
        protected ?string $prAffiliationSociety,
        protected int    $prOwnershipShare           // e.g. 5000 for 50.00%
    ) {}

    public function toString(int $transactionSequence, int $recordSequence): string
    {
        $line  = str_pad($this->recordType, 3);
        $line .= str_pad($this->interestedPartyNumber, 9);
        $line .= str_pad($this->lastName, 45);
        $line .= str_pad($this->firstName ?? '', 30);
        $line .= $this->unknownIndicator ? 'Y' : ' ';
        $line .= str_pad($this->designationCode ?? '', 2);
        $line .= str_pad($this->taxId ?? '', 9);
        $line .= str_pad($this->ipiNameNumber ?? '', 11);
        $line .= str_pad($this->prAffiliationSociety ?? '', 3);
        $line .= str_pad((string)$this->prOwnershipShare, 5, '0', STR_PAD_LEFT);
        $line .= str_repeat(' ', 167 - mb_strlen($line));

        return $line;
    }
}

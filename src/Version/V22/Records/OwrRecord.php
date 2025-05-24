<?php

namespace LabelTools\PhpCwrExporter\Version\V22\Records;

use LabelTools\PhpCwrExporter\Contracts\RecordInterface;

/**
 * v2.2 Other Writer (OWR) record — same layout as SWR but record type "OWR"
 *
 * Fields (fixed-width 167 chars):
 *  • Record Type              1–3   = "OWR"
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
 */
class OwrRecord implements RecordInterface
{
    public function __construct(
        protected string $interestedPartyNumber,
        protected string $lastName,
        protected ?string $firstName,
        protected bool   $unknownIndicator,
        protected ?string $designationCode,
        protected ?string $taxId,
        protected ?string $ipiNameNumber,
        protected ?string $prAffiliationSociety,
        protected int    $prOwnershipShare
    ) {}

    public function toString(int $transactionSequence, int $recordSequence): string
    {
        $line  = str_pad('OWR', 3);                                        // Record Type
        $line .= str_pad($this->interestedPartyNumber, 9);                // Interested Party #
        $line .= str_pad($this->lastName, 45);                            // Last Name
        $line .= str_pad($this->firstName ?? '', 30);                     // First Name
        $line .= $this->unknownIndicator ? 'Y' : ' ';                     // Unknown Indicator
        $line .= str_pad($this->designationCode ?? '', 2);                // Designation Code
        $line .= str_pad($this->taxId ?? '', 9);                          // Tax ID #
        $line .= str_pad($this->ipiNameNumber ?? '', 11);                 // IPI Name #
        $line .= str_pad($this->prAffiliationSociety ?? '', 3);           // PR Affiliation Society #
        $line .= str_pad((string)$this->prOwnershipShare, 5, '0', STR_PAD_LEFT); // PR Ownership Share
        $line .= str_repeat(' ', 167 - mb_strlen($line));                 // Padding
        return $line;
    }
}
<?php

namespace LabelTools\PhpCwrExporter\Records;

class SwrRecord extends Record
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

    protected function validateBeforeToString(): void
    {

    }

}

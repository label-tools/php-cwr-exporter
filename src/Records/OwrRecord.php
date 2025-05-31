<?php

namespace LabelTools\PhpCwrExporter\Records;

class OwrRecord extends Record
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

    protected function validateBeforeToString(): void
    {
    }
}
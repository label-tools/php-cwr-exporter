<?php

namespace LabelTools\PhpCwrExporter\Records\Detail;

use LabelTools\PhpCwrExporter\Records\Record;

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
}
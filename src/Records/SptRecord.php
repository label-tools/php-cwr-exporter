<?php

namespace LabelTools\PhpCwrExporter\Records;


class SptRecord extends Record
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

    protected function validateBeforeToString(): void
    {

    }
}
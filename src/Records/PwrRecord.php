<?php

namespace LabelTools\PhpCwrExporter\Records;

class PwrRecord extends Record
{
    public function __construct(
        protected string $publisherIp,
        protected string $publisherName,
        protected ?string $submitterAgreementNumber,
        protected ?string $societyAgreementNumber,
        protected ?string $writerIpNumber,
        protected int    $publisherSequence
    ) {}

    protected function validateBeforeToString(): void
    {

    }

}
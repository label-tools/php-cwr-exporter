<?php

namespace LabelTools\PhpCwrExporter\Version\V22\Records;

use LabelTools\PhpCwrExporter\Version\V21\Records\PwrRecord as V21PwrRecord;

class PwrRecord extends V21PwrRecord
{
    protected const IDX_PUBLISHER_SEQUENCE_NUMBER = 7;

    public function __construct(
        string $publisherIpNumber,
        string $publisherName,
        ?string $submitterAgreementNumber = '',
        ?string $societyAssignedAgreementNumber = '',
        ?string $writerIpNumber = '',
        ?int $publisherSequenceNumber = 0
    ) {
        parent::__construct($publisherIpNumber, $publisherName, $submitterAgreementNumber, $societyAssignedAgreementNumber, $writerIpNumber);
        $this->stringFormat .= '%02d';

        $this->setPublisherSequenceNumber($publisherSequenceNumber);
    }

    public function setPublisherSequenceNumber(int $publisherSequenceNumber): self
    {
        $this->data[self::IDX_PUBLISHER_SEQUENCE_NUMBER] = $publisherSequenceNumber;
        return $this;
    }

    protected function validateBeforeToString(): void
    {
        parent::validateBeforeToString();
    }
}
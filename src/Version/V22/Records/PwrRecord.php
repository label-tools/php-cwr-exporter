<?php

namespace LabelTools\PhpCwrExporter\Version\V22\Records;

use LabelTools\PhpCwrExporter\Fields\HasSequenceNumber;
use LabelTools\PhpCwrExporter\Version\V21\Records\PwrRecord as V21PwrRecord;

class PwrRecord extends V21PwrRecord
{
    use HasSequenceNumber;
    protected const IDX_PUBLISHER_SEQUENCE_NUMBER = 7;

    public function __construct(
        string $publisherIpNumber,
        string $publisherName,
        ?string $submitterAgreementNumber = '',
        ?string $societyAssignedAgreementNumber = '',
        ?string $writerIpNumber = '',
        ?int $publisherSequenceNumber = 1
    ) {
        parent::__construct($publisherIpNumber, $publisherName, $submitterAgreementNumber, $societyAssignedAgreementNumber, $writerIpNumber);
        $this->stringFormat .= '%02d';

        $this->setSequenceNumber($publisherSequenceNumber);
    }

    protected function getSequenceNumberIndex(): int
    {
        return self::IDX_PUBLISHER_SEQUENCE_NUMBER;
    }

    protected function validateBeforeToString(): void
    {
        parent::validateBeforeToString();
    }
}
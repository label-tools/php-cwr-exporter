<?php

namespace LabelTools\PhpCwrExporter\Version\V21\Records\Detail;

class PwrRecord extends \LabelTools\PhpCwrExporter\Records\Detail\PwrRecord
{
    protected const IDX_WRITER_IP_NUMBER = 6;

    public function __construct(
        string $publisherIpNumber,
        string $publisherName,
        ?string $submitterAgreementNumber = '',
        ?string $societyAssignedAgreementNumber = '',
        ?string $writerIpNumber = ''
    ) {
        parent::__construct($publisherIpNumber, $publisherName, $submitterAgreementNumber, $societyAssignedAgreementNumber);
        $this->stringFormat .= '%-9s';

        $this->setWriterIpNumber($writerIpNumber);
    }

    public function setWriterIpNumber(string $writerIpNumber): self
    {
        $this->data[self::IDX_WRITER_IP_NUMBER] = $writerIpNumber;
        return $this;
    }

    protected function validateBeforeToString(): void
    {
        parent::validateBeforeToString();
    }

}
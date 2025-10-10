<?php

namespace LabelTools\PhpCwrExporter\Version\V21\Records\Detail;

use LabelTools\PhpCwrExporter\Enums\SocietyCode;
use LabelTools\PhpCwrExporter\Enums\WriterDesignation;

class SwrRecord extends \LabelTools\PhpCwrExporter\Records\Detail\SwrRecord
{
    protected const IDX_USA_LICENSE_IND = 21;


    public function __construct(
        string $interestedPartyNumber,

        string $writerLastName,
        string $writerFirstName = '',
        WriterDesignation|string $writerDesignationCode = '',
        string $taxId = '',
        string $writerIpiNameNumber = '',

        int|SocietyCode|null $prAffiliationSociety = null,
        int $prOwnershipShare = 0,

        int|SocietyCode|null $mrAffiliationSociety = null,
        int $mrOwnershipShare = 0,

        int|SocietyCode|null $srAffiliationSociety = null,
        int $srOwnershipShare = 0,

        //Society/Region Specific Fields
        string $reversionaryIndicator = '',
        string $firstRecordingRefusalIndicator = '',
        string $workForHireIndicator = '',
        string $filler = '',

        // V 2.0 fields
        string $writerIpiBaseNumber = '',
        string $personalNumber = '',

        string $usaLicenseIndicator = ''
    ) {
        parent::__construct(
            $interestedPartyNumber,
            $writerLastName,
            $writerFirstName,
            $writerDesignationCode,
            $taxId,
            $writerIpiNameNumber,
            $prAffiliationSociety,
            $prOwnershipShare,
            $mrAffiliationSociety,
            $mrOwnershipShare,
            $srAffiliationSociety,
            $srOwnershipShare,
            $reversionaryIndicator,
            $firstRecordingRefusalIndicator,
            $workForHireIndicator,
            $filler,
            $writerIpiBaseNumber,
            $personalNumber
        );
        $this->stringFormat .= "%-1s";
        $this
            ->setWriterUnknownIndicator('Y')
            ->setUsaLicenseIndicator($usaLicenseIndicator);
    }

    public function setUsaLicenseIndicator(string $flag): static
    {
        $flag = trim($flag);
        if ($flag !== '' && !in_array($flag, ['Y',''], true)) {
            throw new \InvalidArgumentException("USA License Indicator must be 'Y' or blank.");
        }
        $this->data[self::IDX_USA_LICENSE_IND] = $flag;
        return $this;
    }

    protected function validateBeforeToString(): void
    {
        parent::validateBeforeToString();
    }
}

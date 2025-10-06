<?php

namespace LabelTools\PhpCwrExporter\Records;

use LabelTools\PhpCwrExporter\Enums\SocietyCode;
use LabelTools\PhpCwrExporter\Enums\WriterDesignation;
use LabelTools\PhpCwrExporter\Fields\HasOwnershipShare;

class SwrRecord extends Record
{
    use HasOwnershipShare;

    protected static string $recordType = 'SWR';

    // Field indices for data array
    protected const IDX_INTERESTED_PARTY_NUMBER = 2;

    protected const IDX_WRITER_LAST_NAME = 3;
    protected const IDX_WRITER_FIRST_NAME = 4;
    protected const IDX_WRITER_UNKNOWN_IND = 5;
    protected const IDX_WRITER_DESIGNATION_CODE = 6;
    protected const IDX_TAX_ID = 7;
    protected const IDX_WRITER_IPI_NAME_NUMBER = 8;

    protected const IDX_PR_AFFILIATION_SOCIETY = 9;
    protected const IDX_PR_OWNERSHIP_SHARE = 10;
    protected const IDX_MR_AFFILIATION_SOCIETY = 11;
    protected const IDX_MR_OWNERSHIP_SHARE = 12;
    protected const IDX_SR_AFFILIATION_SOCIETY = 13;
    protected const IDX_SR_OWNERSHIP_SHARE = 14;

    //Society/Region Specific Fields
    protected const IDX_REVERSIONARY_IND = 15;
    protected const IDX_FIRST_RECORDING_REFUSAL_IND = 16;
    protected const IDX_WORK_FOR_HIRE_IND = 17;
    protected const IDX_FILLER = 18;

    // V 2.0 fields
    protected const IDX_WRITER_IPI_BASE_NUMBER = 19;
    protected const IDX_PERSONAL_NUMBER = 20;

    // Fixed-length string format for SWR layout (including society/region fields and v2.0 fields)
    protected string $stringFormat =
        "%-19s" .   // Record Prefix (19 A)
        "%-9s"  .   // Interested Party # (9 A)
        "%-45s" .   // Writer Last Name (45 A)
        "%-30s" .   // Writer First Name (30 A)
        "%-1s"  .   // Writer Unknown Indicator (1 F)
        "%-2s"  .   // Writer Designation Code (2 L)
        "%-9s"  .   // Tax ID # (9 A)
        "%-11s" .   // Writer IPI Name # (11 L)

        "%03d"  .   // PR Affiliation Society # (3 L)
        "%05d" .    // PR Ownership Share (5 N)

        "%03d"  .   // MR Affiliation Society # (3 L)
        "%05d" .    // MR Ownership Share (5 N)

        "%03d"  .   // SR Affiliation Society # (3 L)
        "%05d" .    // SR Ownership Share (5 N)

        "%-1s"  .   // Reversionary Indicator (1 F)
        "%-1s"  .   // First Recording Refusal Ind (1 B)
        "%-1s"  .   // Work For Hire Indicator (1 B)
        "%-1s"  .   // Filler (1 A)

        "%-13s" .   // Writer IPI Base Number (13 L)
        "%-12s";    // Personal Number (12 N)


    public function __construct(
        string $interestedPartyNumber,

        string $writerLastName,
        string $writerFirstName = '',
        string|WriterDesignation $writerDesignationCode = '',
        string $taxId = '',
        string $writerIpiNameNumber = '',

        int|SocietyCode|null $prAffiliationSociety = null,
        int|float $prOwnershipShare = 0,

        int|SocietyCode|null $mrAffiliationSociety = null,
        int|float $mrOwnershipShare = 0,

        int|SocietyCode|null $srAffiliationSociety = null,
        int|float $srOwnershipShare = 0,

        //Society/Region Specific Fields
        string $reversionaryIndicator = '',
        string $firstRecordingRefusalIndicator = '',
        string $workForHireIndicator = '',
        string $filler = '',

        // V 2.0 fields
        string $writerIpiBaseNumber = '',
        string $personalNumber = ''
    ) {
        parent::__construct();

        $this->setInterestedPartyNumber($interestedPartyNumber)
            ->setWriterLastName($writerLastName)
            ->setWriterFirstName($writerFirstName)
            ->setWriterUnknownIndicator(' ')// Always blank for SWR records
            ->setWriterDesignationCode($writerDesignationCode)
            ->setTaxId($taxId)
            ->setWriterIpiNameNumber($writerIpiNameNumber)
            ->setPrAffiliationSociety($prAffiliationSociety)
            ->setPrOwnershipShare($prOwnershipShare)
            ->setMrAffiliationSociety($mrAffiliationSociety)
            ->setMrOwnershipShare($mrOwnershipShare)
            ->setSrAffiliationSociety($srAffiliationSociety)
            ->setSrOwnershipShare($srOwnershipShare)
            ->setReversionaryIndicator($reversionaryIndicator)
            ->setFirstRecordingRefusalIndicator($firstRecordingRefusalIndicator)
            ->setWorkForHireIndicator($workForHireIndicator)
            ->setFiller($filler)
            ->setWriterIpiBaseNumber($writerIpiBaseNumber)
            ->setPersonalNumber($personalNumber);
    }

    /**
     * Normalize society input into a value or blank.
     *
     * @param SocietyCode|int|string|null $soc
     * @return string|int
     */
    private function normalizeSociety(int|SocietyCode|null $soc): string|int
    {
        if ($soc === null || $soc === '') {
            return '';
        }
        if ($soc instanceof SocietyCode) {
            return $soc->value;
        }

        return SocietyCode::from($soc)->value;
    }

    public function setReversionaryIndicator(string $flag): static
    {
        $flag = trim($flag);
        if ($flag !== '' && !in_array($flag, ['Y',''], true)) {
            throw new \InvalidArgumentException("Reversionary Indicator must be 'Y' or blank.");
        }
        $this->data[self::IDX_REVERSIONARY_IND] = $flag;
        return $this;
    }

    public function setFirstRecordingRefusalIndicator(string $flag): static
    {
        $flag = trim($flag);
        if ($flag !== '' && !in_array($flag, ['Y','N'], true)) {
            throw new \InvalidArgumentException("First Recording Refusal Indicator must be 'Y', 'N', or blank.");
        }
        $this->data[self::IDX_FIRST_RECORDING_REFUSAL_IND] = $flag;
        return $this;
    }

    public function setWorkForHireIndicator(string $flag): static
    {
        $flag = trim($flag);
        if ($flag !== '' && !in_array($flag, ['Y','N'], true)) {
            throw new \InvalidArgumentException("Work For Hire Indicator must be 'Y', 'N', or blank.");
        }
        $this->data[self::IDX_WORK_FOR_HIRE_IND] = $flag;
        return $this;
    }

    public function setFiller(string $filler): static
    {
        // Always fill with a blank or single ASCII space
        $this->data[self::IDX_FILLER] = ' ';
        return $this;
    }

    public function setWriterIpiBaseNumber(string $base): static
    {
        $base = trim($base);
        if ($base !== '' && !preg_match('/^[A-Za-z0-9]{1,13}$/', $base)) {
            throw new \InvalidArgumentException("Writer IPI Base Number must be 1-13 alphanumeric chars or blank.");
        }
        $this->data[self::IDX_WRITER_IPI_BASE_NUMBER] = $base;
        return $this;
    }

    public function setPersonalNumber(string $num): static
    {
        $num = trim($num);
        if ($num !== '' && !preg_match('/^[0-9]{1,12}$/', $num)) {
            throw new \InvalidArgumentException("Personal Number must be 1-12 digits or blank.");
        }
        $this->data[self::IDX_PERSONAL_NUMBER] = $num;
        return $this;
    }

    public function setInterestedPartyNumber(string $id): static
    {
        $id = trim($id);
        if ($id === '' || strlen($id) > 9) {
            throw new \InvalidArgumentException("Interested Party Number must be 1-9 characters.");
        }
        $this->data[self::IDX_INTERESTED_PARTY_NUMBER] = $id;
        return $this;
    }

    public function setWriterLastName(string $writerLastName): static
    {
        $writerLastName = trim($writerLastName);
        if ($writerLastName === '' || strlen($writerLastName) > 45) {
            throw new \InvalidArgumentException("Last Name is required and max 45 chars.");
        }
        if (!preg_match('/^[\x20-\x7E]*$/', $writerLastName)) {
            throw new \InvalidArgumentException("Last Name must be ASCII printable chars.");
        }
        $this->data[self::IDX_WRITER_LAST_NAME] = $writerLastName;
        return $this;
    }

    public function setWriterFirstName(string $writerFirstName): static
    {
        $writerFirstName = trim($writerFirstName);
        if ($writerFirstName !== '' && strlen($writerFirstName) > 30) {
            throw new \InvalidArgumentException("First Name max 30 chars.");
        }
        if ($writerFirstName !== '' && !preg_match('/^[\x20-\x7E]*$/', $writerFirstName)) {
            throw new \InvalidArgumentException("First Name must be ASCII printable chars.");
        }
        $this->data[self::IDX_WRITER_FIRST_NAME] = $writerFirstName;
        return $this;
    }

    public function setWriterUnknownIndicator(false|string $flag): static
    {
        // For SWR, must always be blank
        $this->data[self::IDX_WRITER_UNKNOWN_IND] = '';
        return $this;
    }

    public function setWriterDesignationCode(WriterDesignation|string $code): static
    {
        if (is_string($code))
        {
            if (trim($code) === '') {
                throw new \InvalidArgumentException("Designation Code is required for SWR.");
            }
            $oldCode = $code;
            if ($code = WriterDesignation::tryFrom($code) === null) {
                throw new \InvalidArgumentException("Invalid Writer Designation Code: {$oldCode}");
            }
        }
        $this->data[self::IDX_WRITER_DESIGNATION_CODE] = $code->value;
        return $this;
    }

    public function setTaxId(string $taxId): static
    {
        $taxId = trim($taxId);
        if ($taxId !== '') {
            if (!preg_match('/^[A-Za-z0-9]{1,9}$/', $taxId)) {
                throw new \InvalidArgumentException("Tax ID must be 1-9 alphanumeric chars.");
            }
        }
        $this->data[self::IDX_TAX_ID] = $taxId;
        return $this;
    }

    public function setWriterIpiNameNumber(string $ipi): static
    {
        $ipi = trim($ipi);
        if ($ipi !== '') {
            if (!preg_match('/^[A-Za-z0-9]{1,11}$/', $ipi)) {
                throw new \InvalidArgumentException("IPI Name Number must be 1-11 alphanumeric chars.");
            }
        }
        $this->data[self::IDX_WRITER_IPI_NAME_NUMBER] = $ipi;
        return $this;
    }

    public function setPrAffiliationSociety(int|SocietyCode|null $soc): static
    {
        $value = $this->normalizeSociety($soc);
        $this->data[self::IDX_PR_AFFILIATION_SOCIETY] = $value;
        return $this;
    }

    public function setMrAffiliationSociety(int|SocietyCode|null $soc): static
    {
        $value = $this->normalizeSociety($soc);
        $this->data[self::IDX_MR_AFFILIATION_SOCIETY] = $value;
        return $this;
    }

    public function setSrAffiliationSociety(int|SocietyCode|null $soc): static
    {
        $value = $this->normalizeSociety($soc);
        $this->data[self::IDX_SR_AFFILIATION_SOCIETY] = $value;
        return $this;
    }

    protected function validateBeforeToString(): void
    {
        parent::validateBeforeToString();

        // If recordType = SWR, lastName and designationCode are required.
        if (static::$recordType === 'SWR') {
            if (empty($this->data[self::IDX_WRITER_LAST_NAME])) {
                throw new \RuntimeException("SWR: Last Name is required.");
            }
            if (empty($this->data[self::IDX_WRITER_LAST_NAME])) {
                throw new \RuntimeException("SWR:Designation Code are required.");
            }
        }
        // If writerUnknownIndicator is non-blank, lastName must be blank (but for SWR it's always blank)
        if (!empty($this->data[self::IDX_WRITER_UNKNOWN_IND])) {
            if (!empty($this->data[self::IDX_WRITER_LAST_NAME])) {
                throw new \RuntimeException("If Writer Unknown Indicator is set, Last Name must be blank.");
            }
        }
        // No extra v2.0 checks beyond alphanumeric patterns
    }

}

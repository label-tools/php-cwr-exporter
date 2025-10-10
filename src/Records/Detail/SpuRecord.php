<?php

namespace LabelTools\PhpCwrExporter\Records\Detail;

use LabelTools\PhpCwrExporter\Enums\PublisherType;
use LabelTools\PhpCwrExporter\Enums\SocietyCode;
use LabelTools\PhpCwrExporter\Fields\HasInterestedPartyNumber;
use LabelTools\PhpCwrExporter\Fields\HasOwnershipShare;
use LabelTools\PhpCwrExporter\Records\Record;

class SpuRecord extends Record
{
    use HasInterestedPartyNumber;
    use HasOwnershipShare;

    protected static string $recordType = 'SPU';
    protected string $stringFormat =
        "%-19s" .  // Record Prefix
        "%02d"   .  // Publisher Sequence #
        "%-9s"  .  // Interested Party #
        "%-45s" .  // Publisher Name
        "%-1s"  .  // Publisher Unknown Indicator
        "%-2s"  .  // Publisher Type
        "%-9s"  .  // Tax ID #
        "%-11s" .  // Publisher IPI Name #
        "%-14s" .  // Submitter Agreement Number
        "%03d"  .  // PR Affiliation Society #
        "%05d"  .  // PR Ownership Share
        "%03d"  .  // MR Society
        "%05d"  .  // MR Ownership Share
        "%03d"  .  // SR Society
        "%05d"  .  // SR Ownership Share
        "%-1s"  .  // Special Agreements Indicator
        "%-1s"  .  // First Recording Refusal Indicator
        "%-1s";     // Filler

    protected const IDX_PUBLISHER_SEQUENCE = 2;
    protected const IDX_INTERESTED_PARTY_NUMBER = 3;
    protected const IDX_PUBLISHER_NAME = 4;
    protected const IDX_PUBLISHER_UNKNOWN_IND  = 5;
    protected const IDX_PUBLISHER_TYPE = 6;
    protected const IDX_TAX_ID = 7;
    protected const IDX_PUBLISHER_IPI_NAME = 8;
    protected const IDX_SUBMITTER_AGREEMENT = 9;
    protected const IDX_PR_AFFILIATION_SOCIETY = 10;
    protected const IDX_PR_OWNERSHIP_SHARE = 11;
    protected const IDX_MR_AFFILIATION_SOCIETY = 12;
    protected const IDX_MR_OWNERSHIP_SHARE = 13;
    protected const IDX_SR_AFFILIATION_SOCIETY = 14;
    protected const IDX_SR_OWNERSHIP_SHARE = 15;
    protected const IDX_SPECIAL_AGREEMENTS_IND = 16;
    protected const IDX_FIRST_RECORDING_REFUSAL_IND = 17;
    protected const IDX_FILLER = 18;

    public function __construct(
        int $publisherSequence, //mandatory
        string $interestedPartyNumber, //mandatory for SPU
        string $publisherName,  //mandatory for SPU
        PublisherType|string $publisherType, //mandatory for SPU
        ?string $taxId = '',
        ?string $publisherIpiName = '', //If the record is of type SPU and followed by an SPT (and hence represents the file submitter), then the IPI Name Number is mandatory.
        ?string $submitterAgreementNumber = '',
        int|string|null $prAffiliationSociety = null,
        int|float|null $prOwnershipShare = 0,
        int|string|null $mrAffiliationSociety = null,
        int|float|null $mrOwnershipShare = 0,
        int|string|null $srAffiliationSociety = null,
        int|float|null $srOwnershipShare = 0,
        null|bool|string $specialAgreementsIndicator = null,
        null|bool|string $firstRecordingRefusalIndicator = null
    ) {
        parent::__construct();

        $this->data[static::IDX_PUBLISHER_UNKNOWN_IND] = ' '; // must be blank for SPU records

        $this->setPublisherSequence($publisherSequence)
             ->setInterestedPartyNumber($interestedPartyNumber)
             ->setPublisherName($publisherName)
             ->setPublisherType($publisherType)
             ->setTaxId($taxId)
             ->setPublisherIpiName($publisherIpiName)
             ->setSubmitterAgreementNumber($submitterAgreementNumber)
             ->setPrAffiliationSociety($prAffiliationSociety)
             ->setPrOwnershipShare($prOwnershipShare)
             ->setMrSociety($mrAffiliationSociety)
             ->setMrOwnershipShare($mrOwnershipShare)
             ->setSrSociety($srAffiliationSociety)
             ->setSrOwnershipShare($srOwnershipShare)
             ->setSpecialAgreementsIndicator($specialAgreementsIndicator)
             ->setFirstRecordingRefusalIndicator($firstRecordingRefusalIndicator)
             ->setFiller('');  // always blank
    }

    public function setPublisherSequence(int $seq): self
    {
        $fieldName = 'Publisher Sequence #';
        $this->validateCount($seq, $fieldName, min: 1, max: 99);
        return $this->setNumeric(static::IDX_PUBLISHER_SEQUENCE, $seq, $fieldName);
    }

    public function setPublisherName(string $name): self
    {
        $fieldName = 'Publisher Name';
        if (empty($name) && static::$recordType === 'SPU') {
            throw new \InvalidArgumentException("{$fieldName} is required for SPU.");
        }
        return $this->setAlphaNumeric(static::IDX_PUBLISHER_NAME, $name, $fieldName);
    }

    public function setPublisherType(PublisherType|string $type): self
    {
        return $this->setEnumValue(static::IDX_PUBLISHER_TYPE, PublisherType::class, $type, 'Publisher Type');
    }

    public function setTaxId(?string $taxId): self
    {
        return $this->setAlphaNumeric(static::IDX_TAX_ID, $taxId, 'Tax ID #');
    }

    public function setPublisherIpiName(string $ipi): self
    {
        return $this->setAlphaNumeric(static::IDX_PUBLISHER_IPI_NAME, $ipi, 'Publisher IPI Name Number');
    }

    public function setSubmitterAgreementNumber(?string $agr): self
    {
        return $this->setAlphaNumeric(static::IDX_SUBMITTER_AGREEMENT, $agr, 'Submitter Agreement Number');
    }

    public function setPrAffiliationSociety(?string $soc): self
    {
        return $this->setEnumValue(static::IDX_PR_AFFILIATION_SOCIETY, SocietyCode::class, $soc, 'PR Affiliation Society', isRequired:false);
    }

    public function setMrSociety(int|string|null $soc): self
    {
        return $this->setEnumValue(static::IDX_MR_AFFILIATION_SOCIETY, SocietyCode::class, $soc, 'MR Affiliation Society', isRequired:false);
    }

    public function setSrSociety(int|string|null $soc): self
    {
        return $this->setEnumValue(static::IDX_SR_AFFILIATION_SOCIETY, SocietyCode::class, $soc, 'SR Affiliation Society', isRequired:false);
    }

    public function setSpecialAgreementsIndicator(null|bool|string $flag): self
    {
        return $this->setFlag(static::IDX_SPECIAL_AGREEMENTS_IND, $flag, 'Special Agreements Indicator');
    }

    public function setFirstRecordingRefusalIndicator(null|bool|string $flag): self
    {
        return $this->setFlag(static::IDX_FIRST_RECORDING_REFUSAL_IND, $flag, 'First Recording Refusal Indicator');
    }

    public function setFiller(string $filler): self
    {
        $this->data[static::IDX_FILLER] = ' ';
        return $this;
    }

    protected function validateBeforeToString(): void
    {
        parent::validateBeforeToString();

        if (empty($this->data[static::IDX_INTERESTED_PARTY_NUMBER]) && static::$recordType === 'SPU') {
            throw new \InvalidArgumentException("Interested Party # is required for SPU.");
        }
        if (empty($this->data[static::IDX_PUBLISHER_NAME]) && static::$recordType === 'SPU') {
            throw new \InvalidArgumentException("Publisher Name is required for SPU.");
        }
        if (empty($this->data[static::IDX_PUBLISHER_TYPE]) && static::$recordType === 'SPU') {
            throw new \InvalidArgumentException("Publisher Type is required for SPU.");
        }

        //@todo since this might have to be checked at the group level since "If the record is of type SPU and followed by an SPT (and hence represents the file"
        // if (empty($this->data[static::IDX_PUBLISHER_IPI_NAME]) && static::$recordType === 'SPU') {
        //     throw new \InvalidArgumentException("Publisher IPI Name # is required for SPU.");
        // }
    }
}
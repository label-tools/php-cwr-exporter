<?php

namespace LabelTools\PhpCwrExporter\Records;

use LabelTools\PhpCwrExporter\Enums\PublisherType;
use LabelTools\PhpCwrExporter\Enums\SocietyCode;
use LabelTools\PhpCwrExporter\Fields\HasInterestedPartyNumber;

class SpuRecord extends Record
{
    use HasInterestedPartyNumber;

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
        ?string $prAffiliationSociety = '',
        ?int $prOwnershipShare = 0,
        ?string $mrAffiliationSociety = '',
        ?int $mrOwnershipShare = 0,
        ?string $srAffiliationSociety = '',
        ?int $srOwnershipShare = 0,
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
        if ($seq < 1) {
            throw new \InvalidArgumentException("Publisher Sequence # must be >= 1.");
        }
        $this->data[static::IDX_PUBLISHER_SEQUENCE] = $seq;
        return $this;
    }

    public function setPublisherName(string $name): self
    {
        if (empty($name) && static::$recordType === 'SPU') {
            throw new \InvalidArgumentException("Publisher Name is required for SPU.");
        }
        $this->data[static::IDX_PUBLISHER_NAME] = $name;
        return $this;
    }

    public function setPublisherType(PublisherType|string $type): self
    {
        return $this->setEnumValue(self::IDX_PUBLISHER_TYPE, PublisherType::class, $type);
    }

    public function setTaxId(?string $taxId): self
    {   if (empty($taxId)) {
            $taxId = '';
        }
        elseif ($taxId !== '' && !ctype_digit($taxId)) {
            throw new \InvalidArgumentException("Tax ID must be numeric.");
        }
        $this->data[static::IDX_TAX_ID] = $taxId;
        return $this;
    }

    public function setPublisherIpiName(string $ipi): self
    {

        $this->data[static::IDX_PUBLISHER_IPI_NAME] = $ipi;
        return $this;
    }

    public function setSubmitterAgreementNumber(?string $agr): self
    {
        $this->data[static::IDX_SUBMITTER_AGREEMENT] = $agr;
        return $this;
    }

    public function setPrAffiliationSociety(?string $soc): self
    {
        // If entered, must be numeric and match a SocietyCode
        $this->validateSocietyCode($soc);
        $this->data[static::IDX_PR_AFFILIATION_SOCIETY] = $soc;
        return $this;
    }

    public function setPrOwnershipShare(null|int $share): self
    {
        $share ??= 0;
        if ($share < 0 || $share > 5000) {
            throw new \InvalidArgumentException("PR Ownership Share must be between 0 and 5000 (50.00%).");
        }
        $this->data[static::IDX_PR_OWNERSHIP_SHARE] = $share;
        return $this;
    }


    public function setMrSociety(?string $soc): self
    {
        $this->validateSocietyCode($soc);
        $this->data[static::IDX_MR_AFFILIATION_SOCIETY] = $soc;
        return $this;
    }

    public function setMrOwnershipShare(null|int $share): self
    {
        $share ??= 0;
        if ($share < 0 || $share > 10000) {
            throw new \InvalidArgumentException("MR Ownership Share must be between 0 and 10000 (100.00%).");
        }
        $this->data[static::IDX_MR_OWNERSHIP_SHARE] = $share;
        return $this;
    }

    public function setSrSociety(?string $soc): self
    {
        $this->validateSocietyCode($soc);
        $this->data[static::IDX_SR_AFFILIATION_SOCIETY] = $soc;
        return $this;
    }

    public function setSrOwnershipShare(null|int $share): self
    {
        $share ??= 0;
        if ($share < 0 || $share > 10000) {
            throw new \InvalidArgumentException("SR Ownership Share must be between 0 and 10000 (100.00%).");
        }
        $this->data[static::IDX_SR_OWNERSHIP_SHARE] = $share;
        return $this;
    }

    public function setSpecialAgreementsIndicator(null|bool|string $flag): self
    {
        $this->data[static::IDX_SPECIAL_AGREEMENTS_IND] = $this->flagToValue($flag);
        return $this;
    }

    public function setFirstRecordingRefusalIndicator(null|bool|string $flag): self
    {
        $this->data[static::IDX_FIRST_RECORDING_REFUSAL_IND] = $this->flagToValue($flag);
        return $this;
    }

    public function setFiller(string $filler): self
    {
        $this->data[static::IDX_FILLER] = ' ';
        return $this;
    }

    protected function validateSocietyCode(?string $soc): string
    {
         // If entered, must be numeric and match a SocietyCode
        if (empty($soc)) {
            $soc = '';
        } elseif ($soc !== '') {
            if (!ctype_digit($soc)) {
                throw new \InvalidArgumentException("Society must be numeric: {$soc}");
            }
            if (SocietyCode::tryFrom((int) $soc) === null) {
                throw new \InvalidArgumentException("Invalid Society: {$soc}");
            }
        }

        return $soc;
    }

    protected function validateBeforeToString(): void
    {
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
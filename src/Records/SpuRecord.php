<?php

namespace LabelTools\PhpCwrExporter\Records;

use LabelTools\PhpCwrExporter\Enums\PublisherType;
use LabelTools\PhpCwrExporter\Enums\SocietyCode;

class SpuRecord extends Record
{
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
        "%-3s"  .  // PR Affiliation Society #
        "%05d"  .  // PR Ownership Share
        "%-3s"  .  // MR Society
        "%05d"  .  // MR Ownership Share
        "%-3s"  .  // SR Society
        "%05d"  .  // SR Ownership Share
        "%-1s"  .  // Special Agreements Indicator
        "%-1s"  .  // First Recording Refusal Indicator
        "%-1s";     // Filler

    private const IDX_PUBLISHER_SEQUENCE = 2;
    private const IDX_INTERESTED_PARTY = 3;
    private const IDX_PUBLISHER_NAME = 4;
    private const IDX_PUBLISHER_UNKNOWN_IND  = 5;
    private const IDX_PUBLISHER_TYPE = 6;
    private const IDX_TAX_ID = 7;
    private const IDX_PUBLISHER_IPI_NAME = 8;
    private const IDX_SUBMITTER_AGREEMENT = 9;
    private const IDX_PR_AFFILIATION_SOCIETY = 10;
    private const IDX_PR_OWNERSHIP_SHARE = 11;
    private const IDX_MR_AFFILIATION_SOCIETY = 12;
    private const IDX_MR_OWNERSHIP_SHARE = 13;
    private const IDX_SR_AFFILIATION_SOCIETY = 14;
    private const IDX_SR_OWNERSHIP_SHARE = 15;
    private const IDX_SPECIAL_AGREEMENTS_IND = 16;
    private const IDX_FIRST_RECORDING_REFUSAL_IND = 17;
    private const IDX_FILLER = 18;

    public function __construct(
        int $publisherSequence, //mandatory
        string $interestedPartyNumber, //mandatory for SPU
        string $publisherName,  //mandatory for SPU
        string $publisherType, //mandatory for SPU
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


        $this->data[self::IDX_PUBLISHER_UNKNOWN_IND] = ' '; // must be blank for SPU records

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
        $this->data[self::IDX_PUBLISHER_SEQUENCE] = $seq;
        return $this;
    }

    public function setInterestedPartyNumber(string $number): self
    {
        if (empty($number) && self::$recordType === 'SPU') {
            throw new \InvalidArgumentException("Interested Party # is required for SPU.");
        }
        $this->data[self::IDX_INTERESTED_PARTY] = $number;
        return $this;
    }

    public function setPublisherName(string $name): self
    {
        if (empty($name) && self::$recordType === 'SPU') {
            throw new \InvalidArgumentException("Publisher Name is required for SPU.");
        }
        $this->data[self::IDX_PUBLISHER_NAME] = $name;
        return $this;
    }

    public function setPublisherType(string $type): self
    {
        if (empty($type) && self::$recordType === 'SPU') {
            throw new \InvalidArgumentException("Publisher Type is required for SPU.");
        }
        try {
            $type = PublisherType::from($type)->value;
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException("Invalid Publisher Type: {$type}");
        }
        $this->data[self::IDX_PUBLISHER_TYPE] = $type;
        return $this;
    }

    public function setTaxId(string $taxId): self
    {
        if ($taxId !== '' && !ctype_digit($taxId)) {
            throw new \InvalidArgumentException("Tax ID must be numeric.");
        }
        $this->data[self::IDX_TAX_ID] = $taxId;
        return $this;
    }

    public function setPublisherIpiName(string $ipi): self
    {

        $this->data[self::IDX_PUBLISHER_IPI_NAME] = $ipi;
        return $this;
    }

    public function setSubmitterAgreementNumber(string $agr): self
    {
        $this->data[self::IDX_SUBMITTER_AGREEMENT] = $agr;
        return $this;
    }

    public function setPrAffiliationSociety(string $soc): self
    {
        // If entered, must be numeric and match a SocietyCode
        $this->validateSocietyCode($soc);
        $this->data[self::IDX_PR_AFFILIATION_SOCIETY] = $soc;
        return $this;
    }

    public function setPrOwnershipShare(int $share): self
    {
        if ($share < 0 || $share > 5000) {
            throw new \InvalidArgumentException("PR Ownership Share must be between 0 and 5000 (50.00%).");
        }
        $this->data[self::IDX_PR_OWNERSHIP_SHARE] = $share;
        return $this;
    }

    public function setMrSociety(string $soc): self
    {
        $this->validateSocietyCode($soc);
        $this->data[self::IDX_MR_AFFILIATION_SOCIETY] = $soc;
        return $this;
    }

    public function setMrOwnershipShare(int $share): self
    {
        if ($share < 0 || $share > 10000) {
            throw new \InvalidArgumentException("MR Ownership Share must be between 0 and 10000 (100.00%).");
        }
        $this->data[self::IDX_MR_OWNERSHIP_SHARE] = $share;
        return $this;
    }

    public function setSrSociety(string $soc): self
    {
        $this->validateSocietyCode($soc);
        $this->data[self::IDX_SR_AFFILIATION_SOCIETY] = $soc;
        return $this;
    }

    public function setSrOwnershipShare(int $share): self
    {
        if ($share < 0 || $share > 10000) {
            throw new \InvalidArgumentException("SR Ownership Share must be between 0 and 10000 (100.00%).");
        }
        $this->data[self::IDX_SR_OWNERSHIP_SHARE] = $share;
        return $this;
    }

    public function setSpecialAgreementsIndicator(null|bool|string $flag): self
    {
        $this->data[self::IDX_SPECIAL_AGREEMENTS_IND] = $this->flagToValue($flag);
        return $this;
    }

    public function setFirstRecordingRefusalIndicator(null|bool|string $flag): self
    {
        $this->data[self::IDX_FIRST_RECORDING_REFUSAL_IND] = $this->flagToValue($flag);
        return $this;
    }

    public function setFiller(string $filler): self
    {
        $this->data[self::IDX_FILLER] = ' ';
        return $this;
    }

    protected function validateSocietyCode(string $soc): string
    {
         // If entered, must be numeric and match a SocietyCode
        if ($soc !== '') {
            if (!ctype_digit($soc)) {
                throw new \InvalidArgumentException("Society must be numeric: {$soc}");
            }
            if (SocietyCode::tryFrom((int) $soc) === null) {
                throw new \InvalidArgumentException("Invalid Society: {$soc}");
            }
        }

        return $soc;
    }
}
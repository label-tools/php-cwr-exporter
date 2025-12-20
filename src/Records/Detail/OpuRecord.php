<?php

namespace LabelTools\PhpCwrExporter\Records\Detail;

use LabelTools\PhpCwrExporter\Enums\PublisherType;

class OpuRecord extends SpuRecord
{
    protected static string $recordType = 'OPU';

    public function __construct(
        int $publisherSequence,
        ?string $interestedPartyNumber = '',
        ?string $publisherName = '',
        null|PublisherType|string $publisherType = '',
        ?string $taxId = '',
        ?string $publisherIpiName = '',
        ?string $submitterAgreementNumber = '',
        ?string $prAffiliationSociety = '',
        int|float|null $prOwnershipShare = 0,
        ?string $mrAffiliationSociety = '',
        int|float|null $mrOwnershipShare = 0,
        ?string $srAffiliationSociety = '',
        int|float|null $srOwnershipShare = 0,
        null|bool|string $specialAgreementsIndicator = null,
        null|bool|string $firstRecordingRefusalIndicator = null
    ) {
        parent::__construct(
            $publisherSequence, $interestedPartyNumber, $publisherName, $publisherType, $taxId, $publisherIpiName, $submitterAgreementNumber,
            $prAffiliationSociety, $prOwnershipShare, $mrAffiliationSociety, $mrOwnershipShare, $srAffiliationSociety, $srOwnershipShare,
            $specialAgreementsIndicator, $firstRecordingRefusalIndicator
        );
    }

    /**
     * Override to set interested party number as optional for OPU records.
     */
    public function setInterestedPartyNumber(null|string $partyNumber, bool $isRequired = true): self
    {
        return parent::setInterestedPartyNumber($partyNumber, isRequired:false);
    }

    /**
     * Override to set publisher type as optional for OPU records.
     */
    public function setPublisherType(null|PublisherType|string $type): self
    {
        $defaultType = PublisherType::ORIGINAL_PUBLISHER;

        if ($type instanceof PublisherType) {
            $publisherType = $type;
        } elseif (empty($type)) {
            $publisherType = $defaultType;
        } else {
            try {
                $publisherType = PublisherType::from($type);
            } catch (\ValueError) {
                $publisherType = $defaultType;
            }
        }

        $this->data[static::IDX_PUBLISHER_TYPE] = $publisherType->value;
        return $this;
    }

    /**
     * Publisher Unknown Indicator must be Y or N for OPU, defaulting to N.
     */
    public function setPublisherUnknownIndicator(null|bool|string $flag): self
    {
        if (is_null($flag) || (is_string($flag) && trim($flag) === '')) {
            $flag = 'N';
        } elseif (is_bool($flag)) {
            $flag = $flag ? 'Y' : 'N';
        } else {
            $flag = strtoupper((string) $flag);
        }

        if (!in_array($flag, ['Y', 'N'], true)) {
            throw new \InvalidArgumentException('Publisher Unknown Indicator must be "Y" or "N" for OPU records.');
        }

        if ($flag === 'Y' && !empty(trim($this->data[static::IDX_PUBLISHER_NAME] ?? ''))) {
            throw new \InvalidArgumentException('Publisher Name must be blank when Publisher Unknown Indicator is "Y" for OPU records.');
        }

        $this->data[static::IDX_PUBLISHER_UNKNOWN_IND] = $flag;
        return $this;
    }

    /**
     * Ensure publisher name stays blank when the unknown indicator is Y.
     */
    public function setPublisherName(string $name): self
    {
        if (($this->data[static::IDX_PUBLISHER_UNKNOWN_IND] ?? 'N') === 'Y' && trim($name) !== '') {
            throw new \InvalidArgumentException('Publisher Name must be blank when Publisher Unknown Indicator is "Y" for OPU records.');
        }

        return parent::setPublisherName($name);
    }

    /**
     * For OPU, Special Agreements Indicator can only be L or blank.
     */
    public function setSpecialAgreementsIndicator(null|bool|string $flag): self
    {
        if (is_null($flag)) {
            $value = ' ';
        } else {
            if (is_bool($flag)) {
                $flag = $flag ? 'L' : '';
            }

            $flag = strtoupper((string) $flag);

            if (trim($flag) === '') {
                $value = ' ';
            } elseif ($flag === 'L') {
                $value = $flag;
            } else {
                throw new \InvalidArgumentException('Special Agreements Indicator must be "L" or blank for OPU records.');
            }
        }

        $this->data[static::IDX_SPECIAL_AGREEMENTS_IND] = $value;
        return $this;
    }
}

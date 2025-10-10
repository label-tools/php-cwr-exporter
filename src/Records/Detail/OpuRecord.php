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
        ?string $publisherType = '',
        ?string $taxId = '',
        ?string $publisherIpiName = '',
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
        return $this->setEnumValue(static::IDX_PUBLISHER_TYPE, PublisherType::class, $type, isRequired: false);
    }

}
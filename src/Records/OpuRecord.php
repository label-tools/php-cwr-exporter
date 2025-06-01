<?php

namespace LabelTools\PhpCwrExporter\Records;

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



}
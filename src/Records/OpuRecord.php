<?php

namespace LabelTools\PhpCwrExporter\Records;

class OpuRecord extends SpuRecord
{
    protected static string $recordType = 'OPU';

     public function __construct(
        int $publisherSequence, //mandatory
        ?string $interestedPartyNumber = '', //mandatory for SPU
        ?string $publisherName = '',  //mandatory for SPU
        ?string $publisherType = '', //mandatory for SPU
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
        parent::__construct(
            $publisherSequence, $interestedPartyNumber, $publisherName, $publisherType, $taxId, $publisherIpiName, $submitterAgreementNumber,
            $prAffiliationSociety, $prOwnershipShare, $mrAffiliationSociety, $mrOwnershipShare, $srAffiliationSociety, $srOwnershipShare,
            $specialAgreementsIndicator, $firstRecordingRefusalIndicator
        );
    }



}
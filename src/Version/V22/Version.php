<?php

namespace LabelTools\PhpCwrExporter\Version\V22;

use LabelTools\PhpCwrExporter\Records\AltRecord;
use LabelTools\PhpCwrExporter\Contracts\VersionInterface;
use LabelTools\PhpCwrExporter\Version\V22\Records\HdrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\GrhRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\NwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\SwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\SwtRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\PwrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\SpuRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\SptRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\GrtRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\TrlRecord;

/**
 * CWR Version 2.2 implementation.
 */
class Version implements VersionInterface
{
    protected int $transactionSequence = 0;
    protected int $recordSequence = 0;

    public function getVersionNumber(): string
    {
        return '2.2';
    }

    public function getRevision(): string
    {
        return '2';
    }

    public function renderHeader(array $options): array
    {
        // Initialize first transaction
        $this->transactionSequence = 0;
        $this->recordSequence = 0;

        return [
            // File header
            new HdrRecord(
                senderType: $options['sender_type'],
                senderId: $options['sender_id'] ,
                senderName: $options['sender_name'],
                creationDate: $options['creation_date'] ?? null,
                creationTime: $options['creation_time'] ?? null,
                transmissionDate: $options['transmission_date'] ?? null,
                characterSet: $options['character_set'] ?? null,
                version: $this->getVersionNumber(),
                revision: $options['revision'] ?? $this->getRevision(), //@todo maybe in the future we can support different revisions, for now we are hardcoding to the one we support
                softwarePackage: $options['software_package'] ?? null,
                softwarePackageVersion: $options['software_version'] ?? null
            )->toString(),

            // Group header
            new GrhRecord('NWR', groupId:1)->toString(), //@todo type of group should be configurable. not all CWRs will be NWR
        ];
    }

    /**
     * @param \LabelTools\PhpCwrExporter\Definitions\WorkDefinition[] $works
     * @param array $options
     * @return string[]
     */

    public function renderDetailLines(array $works, array $options): array
    {
        $lines = [];

        foreach ($works as $work) {
            // Reset record sequence for this transaction
            $this->recordSequence = 0;

            // NWR work header
            $lines[] = new NwrRecord(
                workTitle:             $work->title,
                submitterWorkNumber:   $work->submitterWorkNumber,
                mwDistributionCategory: $work->distributionCategory,
                versionType:           $work->versionType,
                languageCode:          $work->language           ?? null,
                iswc:                  $work->iswc              ?? null,
                copyrightDate:         $work->copyright_date    ?? null,
                copyrightNumber:       $work->copyright_number  ?? null,
                duration:              $work->duration          ?? null,
                recordedIndicator:     $work->recorded          ?? false,
                textMusicRelationship: $work->text_music_relationship ?? ''
            )->setRecordPrefix($this->transactionSequence, $this->recordSequence)
            ->toString();

            // SPU & SPT for each publisher
            foreach ($work->publishers as $pubIndex => $pub) {
                // SPU publisher record
                $lines[] = (new SpuRecord(
                    publisherSequence:           $pubIndex + 1,
                    interestedPartyNumber:       $pub->interestedPartyNumber,
                    publisherName:               $pub->publisherName,
                    publisherType:               $pub->publisherType,
                    taxId:                       $pub->taxId,
                    publisherIpiName:            $pub->publisherIpiName,
                    submitterAgreementNumber:    $pub->submitterAgreementNumber,
                    prAffiliationSociety:        $pub->prAffiliationSociety,
                    prOwnershipShare:            $pub->prOwnershipShare,
                    mrAffiliationSociety:        $pub->mrAffiliationSociety,
                    mrOwnershipShare:            $pub->mrOwnershipShare,
                    srAffiliationSociety:        $pub->srAffiliationSociety,
                    srOwnershipShare:            $pub->srOwnershipShare
                ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                  ->toString();

                // SPT territory records
                foreach ($pub->territories ?? [] as $terrIndex => $terr) {
                    $lines[] = (new SptRecord(
                        interestedPartyNumber:        $pub->interestedPartyNumber,
                        prCollectionShare:            $terr['pr_collection_share'] ?? 0,
                        mrCollectionShare:            $terr['mr_collection_share'] ?? 0,
                        srCollectionShare:            $terr['sr_collection_share'] ?? 0,
                        tisNumericCode:               $terr['tis_code'],
                        inclusionExclusionIndicator:  $terr['inclusion_exclusion_indicator'] ?? 'I',
                        sharesChange:                 $terr['shares_change_flag'] ?? '',
                        sequenceNumber:               $terrIndex + 1
                    ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                      ->toString();
                }
            }

            // SWR & SWT for each writer
            foreach ($work->writers ?? [] as $writerIndex => $wr) {
                // SWR writer record
                $lines[] = (new SwrRecord(
                    interestedPartyNumber:   $wr->interestedPartyNumber,
                    writerLastName:          $wr->writerLastName,
                    writerFirstName:         $wr->writerFirstName,
                    writerDesignationCode:   $wr->writerDesignationCode,
                    taxId:                   '',
                    writerIpiNameNumber:     $wr->ipiNameNumber ?? '',
                    prAffiliationSociety:    $wr->prAffiliationSociety ?? null,
                    prOwnershipShare:        property_exists($wr, 'prOwnershipShare') ? (int) $wr->prOwnershipShare : 0,
                    mrAffiliationSociety:    property_exists($wr, 'mrAffiliationSociety') ? $wr->mrAffiliationSociety : null,
                    mrOwnershipShare:        property_exists($wr, 'mrOwnershipShare') ? (int) $wr->mrOwnershipShare : 0,
                    srAffiliationSociety:    property_exists($wr, 'srAffiliationSociety') ? $wr->srAffiliationSociety : null,
                    srOwnershipShare:        property_exists($wr, 'srOwnershipShare') ? (int) $wr->srOwnershipShare : 0,
                    reversionaryIndicator:   '',
                    firstRecordingRefusalIndicator: '',
                    workForHireIndicator:    '',
                    filler:                  '',
                    writerIpiBaseNumber:     property_exists($wr, 'writerIpiBaseNumber') ? (string) $wr->writerIpiBaseNumber : '',
                    personalNumber:          property_exists($wr, 'personalNumber') ? (string) $wr->personalNumber : '',
                    usaLicenseIndicator:     property_exists($wr, 'usaLicenseIndicator') ? (string) $wr->usaLicenseIndicator : ''
                ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                  ->toString();

                // SWT territory records for the writer (sequence starts at 1 per writer)
                $swtSeq = 0;
                foreach ($wr->territories ?? [] as $terr) {
                    $lines[] = (new SwtRecord(
                        interestedPartyNumber:       $wr->interestedPartyNumber,
                        tisNumericCode:              $terr['tis_code'] ?? null,
                        inclusionExclusionIndicator: $terr['inclusion_exclusion_indicator'] ?? 'I',
                        sequenceNumber:              ++$swtSeq
                    ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                      ->toString();
                }
            }

            // PWR link: connect each writer to the first publisher sequence if publishers exist
            if (!empty($work->publishers)) {
                $firstPublisherSequence = 1; // SPU sequence starts at 1
                $firstPublisher = $work->publishers[0];
                foreach ($work->writers ?? [] as $wr) {
                    $lines[] = (new PwrRecord(
                        publisherIpNumber:              $firstPublisher->interestedPartyNumber,
                        publisherName:                  $firstPublisher->publisherName,
                        submitterAgreementNumber:       $firstPublisher->submitterAgreementNumber ?? '',
                        societyAssignedAgreementNumber: (property_exists($firstPublisher, 'societyAssignedAgreementNumber') ? (string) $firstPublisher->societyAssignedAgreementNumber : ''),
                        writerIpNumber:                 $wr->interestedPartyNumber,
                        publisherSequenceNumber:        $firstPublisherSequence
                    ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                      ->toString();
                }
            }

            // ALT records for each alternate title
            if (!empty($work->alternateTitles)) {
                foreach ($work->alternateTitles as $alt) {
                    $lines[] = (new AltRecord(
                        alternateTitle: $alt['alternate_title'],
                        titleType:      $alt['title_type'],
                        languageCode:   $alt['language_code'] ?? null
                    ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                      ->toString();
                }
            }

            // Advance to next transaction
            $this->transactionSequence++;
        }

        return $lines;
    }

    public function renderTrailer(array $options): array
    {
        $groupCount       = $options['group_count']       ?? 1;
        $transactionCount = $options['transaction_count'] ?? 0;
        $detailCount      = $options['detail_count']      ?? 0;
        $headerCount      = $options['header_count']      ?? 0;

        // GRP record count includes GRH + detail lines + GRT itself
        $grtRecordCount = $detailCount + 2;
        // Total records includes headers + detail lines + GRT + TRL
        $totalRecords = $headerCount + $detailCount + 2;

        $grtLine = (new GrtRecord($groupCount, $transactionCount, $grtRecordCount))->toString();
        $trlLine = (new TrlRecord($groupCount, $transactionCount, $totalRecords))->toString();

        return [$grtLine, $trlLine];
    }
}
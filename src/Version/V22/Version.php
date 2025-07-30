<?php

namespace LabelTools\PhpCwrExporter\Version\V22;

use LabelTools\PhpCwrExporter\Contracts\VersionInterface;
use LabelTools\PhpCwrExporter\Version\V22\Records\HdrRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\GrhRecord;
use LabelTools\PhpCwrExporter\Version\V22\Records\NwrRecord;
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

    public function getVersion(): string
    {
        return '2.2';
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
            )->toString(),

            // Group header
            new GrhRecord('NWR', groupId:1)->toString(),
        ];
    }

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
                        prCollectionShare:            $pub->prOwnershipShare,
                        mrCollectionShare:            $pub->mrOwnershipShare,
                        srCollectionShare:            $pub->srOwnershipShare,
                        tisNumericCode:               $terr['tis_code'],
                        inclusionExclusionIndicator:  $terr['inclusion_exclusion_indicator'] ?? 'I',
                        sharesChange:                 $terr['shares_change_flag']        ?? '',
                        sequenceNumber:               $terrIndex + 1
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

        $grtLine = (new GrtRecord($groupCount, $transactionCount, $grtRecordCount))
            ->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
            ->toString();

        $trlLine = (new TrlRecord($groupCount, $transactionCount, $totalRecords))
            ->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
            ->toString();

        return [$grtLine, $trlLine];
    }
}
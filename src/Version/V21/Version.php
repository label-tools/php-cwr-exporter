<?php

namespace LabelTools\PhpCwrExporter\Version\V21;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Contracts\VersionInterface;
use LabelTools\PhpCwrExporter\Version\V21\Records\Control\GrtRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Control\GrhRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Control\HdrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Control\TrlRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\AltRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\PwrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\SptRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\SpuRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\SwrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\SwtRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Transaction\NwrRecord;

class Version implements VersionInterface
{
    private const SUPPORTED_REVISIONS = ['7', '8'];

    protected int $transactionSequence = 1;
    protected int $recordSequence = 1;
    private string $revision;

    public function __construct(?string $revision = null)
    {
        $this->revision = $this->normalizeRevision($revision ?? '8');
    }

    public function getVersionNumber(): string
    {
        return '2.1';
    }

    public function getRevision(): string
    {
        return $this->revision;
    }

    public function renderHeader(array $options): array
    {
        $this->applyRevisionOption($options);
        // Initialize first transaction
        $this->transactionSequence = 1;
        $this->recordSequence = 1;

        return [
            // File header
            new HdrRecord(
                senderType: $options['sender_type'],
                senderId: $options['sender_id'] ,
                senderName: $options['sender_name'],
                creationDate: $options['creation_date'] ?? null,
                creationTime: $options['creation_time'] ?? null,
                transmissionDate: $options['transmission_date'] ?? null,
                characterSet: $options['character_set'] ?? null
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
    public function renderDetailLines(array $works, array $options): \Generator
    {
        $this->applyRevisionOption($options);

        foreach ($works as $work) {
            try {
                // Reset record sequence for this transaction
                $this->recordSequence = 1;

                // NWR work header
                yield (new NwrRecord(
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
                    textMusicRelationship: $work->text_music_relationship ?? '',
                    priorityFlag: $work->priority ?? false, //
                ))->setRecordPrefix($this->transactionSequence, $this->recordSequence)
                ->toString();

                // SPU & SPT for each publisher
                foreach ($work->publishers as $pubIndex => $pub) {
                    // SPU publisher record
                    yield (new SpuRecord(
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
                        yield (new SptRecord(
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
                    yield (new SwrRecord(
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
                        usaLicenseIndicator:     $usaLicenseIndicator ?? '',
                    ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                      ->toString();

                    // SWT territory records for the writer (sequence starts at 1 per writer)
                    $swtSeq = 0;
                    foreach ($wr->territories ?? [] as $terr) {
                        yield (new SwtRecord(
                            interestedPartyNumber:       $wr->interestedPartyNumber,
                            tisNumericCode:              $terr['tis_code'],
                            inclusionExclusionIndicator: $terr['inclusion_exclusion_indicator'] ?? 'I',
                            sequenceNumber:              ++$swtSeq,
                            prCollectionShare:           $terr['pr_collection_share'] ?? 0,
                            mrCollectionShare:           $terr['mr_collection_share'] ?? 0,
                            srCollectionShare:           $terr['sr_collection_share'] ?? 0,
                        ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                          ->toString();
                    }
                }

                // ALT records for each alternate title
                if (!empty($work->alternateTitles)) {
                    foreach ($work->alternateTitles as $alt) {
                        yield (new AltRecord(
                            alternateTitle: $alt['alternate_title'],
                            titleType:      $alt['title_type'],
                            languageCode:   $alt['language_code'] ?? null
                        ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                          ->toString();
                    }
                }

                // PWR link: Link represented writers to their publishers
                if (!empty($work->publishers)) {
                    $publisherMap = [];
                    foreach ($work->publishers as $pubIndex => $pub) {
                        $publisherMap[$pub->interestedPartyNumber] = ['def' => $pub, 'seq' => $pubIndex + 1];
                    }

                    foreach ($work->writers ?? [] as $wr) {
                        // Only create a PWR link if the writer is represented by a publisher in this work
                        if ($wr->publisherInterestedPartyNumber && isset($publisherMap[$wr->publisherInterestedPartyNumber])) {
                            $publisherData = $publisherMap[$wr->publisherInterestedPartyNumber];
                            yield (new PwrRecord(
                                publisherIpNumber:              $publisherData['def']->interestedPartyNumber,
                                publisherName:                  $publisherData['def']->publisherName,
                                submitterAgreementNumber:       $publisherData['def']->submitterAgreementNumber ?? '',
                                societyAssignedAgreementNumber: (property_exists($publisherData['def'], 'societyAssignedAgreementNumber') ? (string) $publisherData['def']->societyAssignedAgreementNumber : ''),
                                writerIpNumber:                 $wr->interestedPartyNumber,
                            ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                              ->toString();
                        }
                    }
                }

                $this->transactionSequence++;
            } catch (\Throwable $e) {
                // Something went wrong with this work. You can log the error or handle it as needed.
                // For example: error_log("Skipping work {$work->submitterWorkNumber}: " . $e->getMessage());
                // The invalid work is skipped, and we continue to the next one.
                yield null; // Yield null to signify a skipped work
            }
        }
    }

    public function renderTrailer(array $options): array
    {
        $this->applyRevisionOption($options);
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

    private function applyRevisionOption(array $options): void
    {
        if (array_key_exists('revision', $options)) {
            $this->revision = $this->normalizeRevision((string) $options['revision']);
        }
    }

    private function normalizeRevision(string $revision): string
    {
        $normalized = trim($revision);

        if ($normalized === '') {
            throw new InvalidArgumentException('Revision value cannot be empty for CWR v2.1.');
        }

        if (!ctype_digit($normalized)) {
            throw new InvalidArgumentException("Revision must be numeric for CWR v2.1. Given: {$revision}");
        }

        $normalized = ltrim($normalized, '0');
        if ($normalized === '') {
            $normalized = '0';
        }

        if (!in_array($normalized, self::SUPPORTED_REVISIONS, true)) {
            $supported = implode(', ', self::SUPPORTED_REVISIONS);
            throw new InvalidArgumentException("CWR v2.1 supports revisions {$supported}. Given: {$revision}");
        }

        return $normalized;
    }

    private function supportsUsaLicenseIndicator(): bool
    {
        return $this->revision !== '7';
    }

    private function supportsPriorityFlag(): bool
    {
        return $this->revision !== '7';
    }
}

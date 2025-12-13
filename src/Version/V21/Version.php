<?php

namespace LabelTools\PhpCwrExporter\Version\V21;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Contracts\VersionInterface;
use LabelTools\PhpCwrExporter\Version\V21\Records\Control\GrtRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Control\GrhRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Control\HdrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Control\TrlRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\AltRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\OwrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\PwrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\SptRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\SpuRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\SwrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\SwtRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Transaction\NwrRecord;
use LabelTools\PhpCwrExporter\Validators\TransactionValidator;

class Version implements VersionInterface
{
    protected int $transactionSequence = 0;
    protected int $recordSequence = 0;
    protected array $skippedWorks = [];

    public function getVersionNumber(): string
    {
        return '2.1';
    }

    public function getRevision(): string
    {
        return '8';
    }

    public function renderHeader(array $options): array
    {
        // Initialize first transaction
        $this->transactionSequence = 0;
        $this->recordSequence = 0;
        $this->skippedWorks = [];

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

        $validator = new TransactionValidator();
        foreach ($works as $work) {
            $emittedRecords = false;
            try {
                // Reset record sequence for this transaction
                $this->recordSequence = 0;

                $publisherMap = $this->buildPublisherMap($work->publishers ?? []);
                $validator->validate($work);
                $this->assertPwrRequirementForControlledWriters($work, $publisherMap);

                // NWR work header
                $line = (new NwrRecord(
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
                $emittedRecords = true;
                yield $line;

                // SPU & SPT for each publisher
                foreach ($work->publishers as $pubIndex => $pub) {
                    // SPU publisher record
                    $line = (new SpuRecord(
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
                    $emittedRecords = true;
                    yield $line;

                    // SPT territory records
                    foreach ($pub->territories ?? [] as $terrIndex => $terr) {
                        $line = (new SptRecord(
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
                        $emittedRecords = true;
                        yield $line;
                    }
                }

                // Writers: controlled => SWR/SWT; uncontrolled => OWR
                foreach ($work->writers ?? [] as $writerIndex => $wr) {
                    $isControlled = property_exists($wr, 'controlled') ? (bool) $wr->controlled : true;

                    if ($isControlled) {
                        $line = (new SwrRecord(
                            interestedPartyNumber:   $wr->interestedPartyNumber,
                            writerLastName:          $wr->writerLastName,
                            writerFirstName:         $wr->writerFirstName,
                            writerDesignationCode:   $wr->writerDesignationCode,
                            taxId:                   $wr->taxId ?? '',
                            writerIpiNameNumber:     $wr->ipiNameNumber ?? '',
                            prAffiliationSociety:    $wr->prAffiliationSociety ?? null,
                            prOwnershipShare:        property_exists($wr, 'prOwnershipShare') ? (int) $wr->prOwnershipShare : 0,
                            mrAffiliationSociety:    property_exists($wr, 'mrAffiliationSociety') ? $wr->mrAffiliationSociety : null,
                            mrOwnershipShare:        property_exists($wr, 'mrOwnershipShare') ? (int) $wr->mrOwnershipShare : 0,
                            srAffiliationSociety:    property_exists($wr, 'srAffiliationSociety') ? $wr->srAffiliationSociety : null,
                            srOwnershipShare:        property_exists($wr, 'srOwnershipShare') ? (int) $wr->srOwnershipShare : 0,
                            reversionaryIndicator:   property_exists($wr, 'reversionaryIndicator') ? (string) $wr->reversionaryIndicator : '',
                            firstRecordingRefusalIndicator: property_exists($wr, 'firstRecordingRefusalIndicator') ? (string) $wr->firstRecordingRefusalIndicator : '',
                            workForHireIndicator:    property_exists($wr, 'workForHireIndicator') ? (string) $wr->workForHireIndicator : '',
                            filler:                  '',
                            writerIpiBaseNumber:     property_exists($wr, 'writerIpiBaseNumber') ? (string) $wr->writerIpiBaseNumber : '',
                            personalNumber:          property_exists($wr, 'personalNumber') ? (string) $wr->personalNumber : '',
                            usaLicenseIndicator:     property_exists($wr, 'usaLicenseIndicator') ? (string) $wr->usaLicenseIndicator : '',
                        ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                          ->toString();
                        $emittedRecords = true;
                        yield $line;

                        // SWT territory records for the writer (sequence starts at 1 per writer)
                        $swtSeq = 0;
                        foreach ($wr->territories ?? [] as $terr) {
                            $line = (new SwtRecord(
                                interestedPartyNumber:       $wr->interestedPartyNumber,
                                tisNumericCode:              $terr['tis_code'],
                                inclusionExclusionIndicator: $terr['inclusion_exclusion_indicator'] ?? 'I',
                                sequenceNumber:              ++$swtSeq,
                                prCollectionShare:           $terr['pr_collection_share'] ?? 0,
                                mrCollectionShare:           $terr['mr_collection_share'] ?? 0,
                                srCollectionShare:           $terr['sr_collection_share'] ?? 0,
                            ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                              ->toString();
                            $emittedRecords = true;
                            yield $line;
                        }

                        $publisherKey = $this->normalizePublisherKey($wr->publisherInterestedPartyNumber ?? null);
                        $publisherData = $publisherMap[$publisherKey] ?? null;
                        if ($publisherData === null) {
                            throw new InvalidArgumentException("SWR writer {$wr->interestedPartyNumber} must be linked to a publisher to emit a PWR record.");
                        }

                        $line = (new PwrRecord(
                            publisherIpNumber:              $publisherData['def']->interestedPartyNumber,
                            publisherName:                  $publisherData['def']->publisherName,
                            submitterAgreementNumber:       $publisherData['def']->submitterAgreementNumber ?? '',
                            societyAssignedAgreementNumber: (property_exists($publisherData['def'], 'societyAssignedAgreementNumber') ? (string) $publisherData['def']->societyAssignedAgreementNumber : ''),
                            writerIpNumber:                 $wr->interestedPartyNumber,
                        ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                          ->toString();
                        $emittedRecords = true;
                        yield $line;
                    } else {
                        $line = (new OwrRecord(
                            interestedPartyNumber:   $wr->interestedPartyNumber,
                            writerLastName:          $wr->writerLastName,
                            writerFirstName:         $wr->writerFirstName,
                            writerDesignationCode:   $wr->writerDesignationCode,
                            taxId:                   $wr->taxId ?? '',
                            writerIpiNameNumber:     $wr->ipiNameNumber ?? '',
                            prAffiliationSociety:    $wr->prAffiliationSociety ?? null,
                            prOwnershipShare:        property_exists($wr, 'prOwnershipShare') ? (int) $wr->prOwnershipShare : 0,
                            mrAffiliationSociety:    property_exists($wr, 'mrAffiliationSociety') ? $wr->mrAffiliationSociety : null,
                            mrOwnershipShare:        property_exists($wr, 'mrOwnershipShare') ? (int) $wr->mrOwnershipShare : 0,
                            srAffiliationSociety:    property_exists($wr, 'srAffiliationSociety') ? $wr->srAffiliationSociety : null,
                            srOwnershipShare:        property_exists($wr, 'srOwnershipShare') ? (int) $wr->srOwnershipShare : 0,
                            reversionaryIndicator:   property_exists($wr, 'reversionaryIndicator') ? (string) $wr->reversionaryIndicator : '',
                            firstRecordingRefusalIndicator: property_exists($wr, 'firstRecordingRefusalIndicator') ? (string) $wr->firstRecordingRefusalIndicator : '',
                            workForHireIndicator:    property_exists($wr, 'workForHireIndicator') ? (string) $wr->workForHireIndicator : '',
                            filler:                  '',
                            writerIpiBaseNumber:     property_exists($wr, 'writerIpiBaseNumber') ? (string) $wr->writerIpiBaseNumber : '',
                            personalNumber:          property_exists($wr, 'personalNumber') ? (string) $wr->personalNumber : '',
                            usaLicenseIndicator:     property_exists($wr, 'usaLicenseIndicator') ? (string) $wr->usaLicenseIndicator : '',
                        ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                          ->toString();
                        $emittedRecords = true;
                        yield $line;
                    }
                }

                // ALT records for each alternate title
                if (!empty($work->alternateTitles)) {
                    foreach ($work->alternateTitles as $alt) {
                        $line = (new AltRecord(
                            alternateTitle: $alt['alternate_title'],
                            titleType:      $alt['title_type'],
                            languageCode:   $alt['language_code'] ?? null
                        ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                          ->toString();
                        $emittedRecords = true;
                        yield $line;
                    }
                }

            } catch (\Throwable $e) {
                $this->skippedWorks[] = [
                    'work_number' => $work->submitterWorkNumber ?? null,
                    'error' => $e->getMessage(),
                    'exception' => $e,
                ];
                yield null; // Yield null to signify a skipped work
            } finally {
                // Advance the transaction sequence only when at least one record for this work was emitted.
                if ($emittedRecords) {
                    $this->transactionSequence++;
                }
            }
        }
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

    /**
     * @param array $publishers
     * @return array<string, array{def: object, seq: int}>
     */
    private function buildPublisherMap(array $publishers): array
    {
        $map = [];
        foreach ($publishers as $index => $publisher) {
            $map[$publisher->interestedPartyNumber] = ['def' => $publisher, 'seq' => $index + 1];
        }

        return $map;
    }

    /**
     * Validates that each controlled writer (SWR) can emit the required PWR link.
     */
    private function assertPwrRequirementForControlledWriters(object $work, array $publisherMap): void
    {
        foreach ($work->writers ?? [] as $writer) {
            if (!$this->isControlledWriter($writer)) {
                continue;
            }

            $publisherKey = $this->normalizePublisherKey($writer->publisherInterestedPartyNumber ?? null);
            if ($publisherKey === '') {
                throw new InvalidArgumentException("SWR writer {$writer->interestedPartyNumber} must include publisher_interested_party_number so a PWR record can follow.");
            }

            if (!isset($publisherMap[$publisherKey])) {
                $hint = empty($publisherMap) ? 'no publishers were provided for this work' : "publisher {$publisherKey} is not present for this work";
                throw new InvalidArgumentException("SWR writer {$writer->interestedPartyNumber} references {$publisherKey}, but {$hint}; cannot create required PWR record.");
            }
        }
    }

    private function isControlledWriter(object $writer): bool
    {
        return property_exists($writer, 'controlled') ? (bool) $writer->controlled : true;
    }

    private function normalizePublisherKey(null|int|string $publisherIp): string
    {
        return trim((string) $publisherIp);
    }

    public function getSkippedWorks(): array
    {
        return $this->skippedWorks;
    }
}

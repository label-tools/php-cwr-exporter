<?php

namespace LabelTools\PhpCwrExporter\Version\V21;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Contracts\VersionInterface;
use LabelTools\PhpCwrExporter\Enums\TransactionType;
use LabelTools\PhpCwrExporter\Version\V21\Records\Control\GrtRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Control\GrhRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Control\HdrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Control\TrlRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\AltRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\OwrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\PwrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\RecRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\PerRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\SptRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\SpuRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\OpuRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\SwrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Detail\SwtRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Transaction\NwrRecord;
use LabelTools\PhpCwrExporter\Version\V21\Records\Transaction\RevRecord;
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
        $groupedWorks = $this->groupWorksByTransactionType($works, $options);
        $groupId = 1;
        $emittedGroup = false;

        foreach ([TransactionType::NEW_WORK_REGISTRATION->value, TransactionType::REVISED_REGISTRATION->value] as $transactionType) {
            $groupWorks = $groupedWorks[$transactionType] ?? [];
            if ($groupWorks === []) {
                continue;
            }

            $groupTransactionCount = 0;
            $groupRecordCount = 1; // GRH
            $groupLines = [];
            $this->transactionSequence = 0;

            foreach ($groupWorks as $work) {
                $workLines = $this->renderWorkLines($work, $validator, $this->resolveTransactionRecordClassForType($transactionType));

                if ($workLines === null) {
                    continue;
                }

                $groupTransactionCount++;
                $groupRecordCount += count($workLines);
                array_push($groupLines, ...$workLines);
                $this->transactionSequence++;
            }

            if ($groupTransactionCount === 0) {
                continue;
            }

            yield (new GrhRecord($transactionType, groupId: $groupId))->toString();
            $emittedGroup = true;

            foreach ($groupLines as $line) {
                yield $line;
            }

            yield (new GrtRecord($groupId, $groupTransactionCount, $groupRecordCount + 1))->toString();
            $groupId++;
        }

        if (!$emittedGroup) {
            $defaultTransactionType = $this->resolveTransactionType($options);
            yield (new GrhRecord($defaultTransactionType, groupId: 1))->toString();
            yield (new GrtRecord(1, 0, 2))->toString();
        }
    }

    public function renderTrailer(array $options): array
    {
        $groupCount       = $options['group_count']       ?? 0;
        $transactionCount = $options['transaction_count'] ?? 0;
        $recordCount      = $options['record_count']      ?? (($options['header_count'] ?? 0) + ($options['body_count'] ?? 0) + 1);

        return [(new TrlRecord($groupCount, $transactionCount, $recordCount))->toString()];
    }

    /**
     * @param class-string<NwrRecord|RevRecord> $transactionRecordClass
     * @return string[]|null
     */
    private function renderWorkLines(object $work, TransactionValidator $validator, string $transactionRecordClass): ?array
    {
        try {
            $this->recordSequence = 0;
            $publisherMap = $this->buildPublisherMap($work->publishers ?? []);
            $validator->validate($work);
            $this->assertPwrRequirementForControlledWriters($work, $publisherMap);

            $workLines = [];

            $workLines[] = (new $transactionRecordClass(
                workTitle:             $work->title,
                submitterWorkNumber:   $work->submitterWorkNumber,
                mwDistributionCategory: $work->distributionCategory,
                versionType:           $work->versionType,
                languageCode:          $work->language ?? null,
                iswc:                  $work->iswc ?? null,
                copyrightDate:         $work->copyright_date ?? null,
                copyrightNumber:       $work->copyright_number ?? null,
                duration:              $work->duration ?? null,
                recordedIndicator:     $work->recorded ?? false,
                textMusicRelationship: $work->text_music_relationship ?? '',
                priorityFlag:          $work->priority ?? false,
            ))->setRecordPrefix($this->transactionSequence, $this->recordSequence)
                ->toString();

            foreach ($work->publishers ?? [] as $pubIndex => $pub) {
                $isControlledPublisher = property_exists($pub, 'controlled') ? (bool) $pub->controlled : true;
                $publisherRecordClass = $isControlledPublisher ? SpuRecord::class : OpuRecord::class;

                $publisherRecord = (new $publisherRecordClass(
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
                ));

                $publisherRecord->setPublisherUnknownIndicator($pub->publisherUnknownIndicator ?? null);

                $workLines[] = $publisherRecord->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                    ->toString();

                foreach ($pub->territories ?? [] as $terrIndex => $terr) {
                    $workLines[] = (new SptRecord(
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

            foreach ($this->orderWritersControlledFirst($work->writers ?? []) as $wr) {
                $isControlled = property_exists($wr, 'controlled') ? (bool) $wr->controlled : true;

                if ($isControlled) {
                    $workLines[] = (new SwrRecord(
                        interestedPartyNumber:   $wr->interestedPartyNumber,
                        writerLastName:          $wr->writerLastName,
                        writerFirstName:         $wr->writerFirstName,
                        writerDesignationCode:   $wr->writerDesignationCode,
                        taxId:                   $wr->taxId ?? '',
                        writerIpiNameNumber:     $wr->ipiNameNumber ?? '',
                        prAffiliationSociety:    $wr->prAffiliationSociety ?? null,
                        prOwnershipShare:        property_exists($wr, 'prOwnershipShare') ? $wr->prOwnershipShare : 0,
                        mrAffiliationSociety:    property_exists($wr, 'mrAffiliationSociety') ? $wr->mrAffiliationSociety : null,
                        mrOwnershipShare:        property_exists($wr, 'mrOwnershipShare') ? $wr->mrOwnershipShare : 0,
                        srAffiliationSociety:    property_exists($wr, 'srAffiliationSociety') ? $wr->srAffiliationSociety : null,
                        srOwnershipShare:        property_exists($wr, 'srOwnershipShare') ? $wr->srOwnershipShare : 0,
                        reversionaryIndicator:   property_exists($wr, 'reversionaryIndicator') ? (string) $wr->reversionaryIndicator : '',
                        firstRecordingRefusalIndicator: property_exists($wr, 'firstRecordingRefusalIndicator') ? (string) $wr->firstRecordingRefusalIndicator : '',
                        workForHireIndicator:    property_exists($wr, 'workForHireIndicator') ? (string) $wr->workForHireIndicator : '',
                        filler:                  '',
                        writerIpiBaseNumber:     property_exists($wr, 'writerIpiBaseNumber') ? (string) $wr->writerIpiBaseNumber : '',
                        personalNumber:          property_exists($wr, 'personalNumber') ? (string) $wr->personalNumber : '',
                        usaLicenseIndicator:     property_exists($wr, 'usaLicenseIndicator') ? (string) $wr->usaLicenseIndicator : '',
                    ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                        ->toString();

                    $swtSeq = 0;
                    foreach ($wr->territories ?? [] as $terr) {
                        $workLines[] = (new SwtRecord(
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

                    $publisherKey = $this->normalizePublisherKey($wr->publisherInterestedPartyNumber ?? null);
                    $publisherData = $publisherMap[$publisherKey] ?? null;
                    if ($publisherData === null) {
                        throw new InvalidArgumentException("SWR writer {$wr->interestedPartyNumber} must be linked to a publisher to emit a PWR record.");
                    }

                    $workLines[] = (new PwrRecord(
                        publisherIpNumber:              $publisherData['def']->interestedPartyNumber,
                        publisherName:                  $publisherData['def']->publisherName,
                        submitterAgreementNumber:       $publisherData['def']->submitterAgreementNumber ?? '',
                        societyAssignedAgreementNumber: (property_exists($publisherData['def'], 'societyAssignedAgreementNumber') ? (string) $publisherData['def']->societyAssignedAgreementNumber : ''),
                        writerIpNumber:                 $wr->interestedPartyNumber,
                    ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                        ->toString();
                } else {
                    $workLines[] = (new OwrRecord(
                        interestedPartyNumber:   $wr->interestedPartyNumber,
                        writerLastName:          $wr->writerLastName,
                        writerFirstName:         $wr->writerFirstName,
                        writerDesignationCode:   $wr->writerDesignationCode,
                        taxId:                   $wr->taxId ?? '',
                        writerIpiNameNumber:     $wr->ipiNameNumber ?? '',
                        prAffiliationSociety:    $wr->prAffiliationSociety ?? null,
                        prOwnershipShare:        property_exists($wr, 'prOwnershipShare') ? $wr->prOwnershipShare : 0,
                        mrAffiliationSociety:    property_exists($wr, 'mrAffiliationSociety') ? $wr->mrAffiliationSociety : null,
                        mrOwnershipShare:        property_exists($wr, 'mrOwnershipShare') ? $wr->mrOwnershipShare : 0,
                        srAffiliationSociety:    property_exists($wr, 'srAffiliationSociety') ? $wr->srAffiliationSociety : null,
                        srOwnershipShare:        property_exists($wr, 'srOwnershipShare') ? $wr->srOwnershipShare : 0,
                        reversionaryIndicator:   property_exists($wr, 'reversionaryIndicator') ? (string) $wr->reversionaryIndicator : '',
                        firstRecordingRefusalIndicator: property_exists($wr, 'firstRecordingRefusalIndicator') ? (string) $wr->firstRecordingRefusalIndicator : '',
                        workForHireIndicator:    property_exists($wr, 'workForHireIndicator') ? (string) $wr->workForHireIndicator : '',
                        filler:                  '',
                        writerIpiBaseNumber:     property_exists($wr, 'writerIpiBaseNumber') ? (string) $wr->writerIpiBaseNumber : '',
                        personalNumber:          property_exists($wr, 'personalNumber') ? (string) $wr->personalNumber : '',
                        usaLicenseIndicator:     property_exists($wr, 'usaLicenseIndicator') ? (string) $wr->usaLicenseIndicator : '',
                    ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                        ->toString();
                }
            }

            foreach ($work->alternateTitles ?? [] as $alt) {
                $workLines[] = (new AltRecord(
                    alternateTitle: $alt['alternate_title'],
                    titleType:      $alt['title_type'],
                    languageCode:   $alt['language_code'] ?? null
                ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                    ->toString();
            }

            foreach ($work->performingArtists ?? [] as $artist) {
                $workLines[] = (new PerRecord(
                    $artist->lastName,
                    $artist->firstName ?? '',
                    $artist->ipiNameNumber ?? '',
                    $artist->ipiBaseNumber ?? ''
                ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                    ->toString();
            }

            foreach ($work->recordings ?? [] as $recording) {
                $workLines[] = (new RecRecord(
                    firstReleaseDate:             $recording->firstReleaseDate,
                    firstReleaseDuration:         $recording->firstReleaseDuration,
                    firstAlbumTitle:              $recording->firstAlbumTitle,
                    firstAlbumLabel:              $recording->firstAlbumLabel,
                    firstReleaseCatalogNumber:    $recording->firstReleaseCatalogNumber,
                    firstReleaseEan:              $recording->firstReleaseEan,
                    firstReleaseIsrc:             $recording->firstReleaseIsrc,
                    recordingFormat:              $recording->recordingFormat,
                    recordingTechnique:           $recording->recordingTechnique,
                    mediaType:                    $recording->mediaType
                ))->setRecordPrefix($this->transactionSequence, ++$this->recordSequence)
                    ->toString();
            }

            return $workLines;
        } catch (\Throwable $e) {
            $this->skippedWorks[] = [
                'work_number' => $work->submitterWorkNumber ?? null,
                'error' => $e->getMessage(),
                'exception' => $e,
            ];

            return null;
        }
    }

    /**
     * @param array<int, object> $works
     * @return array<string, array<int, object>>
     */
    private function groupWorksByTransactionType(array $works, array $options): array
    {
        $grouped = [
            TransactionType::NEW_WORK_REGISTRATION->value => [],
            TransactionType::REVISED_REGISTRATION->value => [],
        ];

        foreach ($works as $work) {
            $transactionType = $this->resolveWorkTransactionType($work, $options);
            $grouped[$transactionType][] = $work;
        }

        return $grouped;
    }

    /**
     * @param array $publishers
     * @return array<string, array{def: object, seq: int}>
     */
    private function buildPublisherMap(array $publishers): array
    {
        $map = [];
        foreach ($publishers as $index => $publisher) {
            if (!empty($publisher->interestedPartyNumber)) {
                $map[$publisher->interestedPartyNumber] = ['def' => $publisher, 'seq' => $index + 1];
            }
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

    /**
     * Ensures controlled writers (SWR) are emitted before uncontrolled writers (OWR).
     */
    private function orderWritersControlledFirst(array $writers): array
    {
        $controlled = [];
        $uncontrolled = [];

        foreach ($writers as $writer) {
            if ($this->isControlledWriter($writer)) {
                $controlled[] = $writer;
            } else {
                $uncontrolled[] = $writer;
            }
        }

        return array_merge($controlled, $uncontrolled);
    }

    private function normalizePublisherKey(null|int|string $publisherIp): string
    {
        return trim((string) $publisherIp);
    }

    private function resolveTransactionType(array $options): string
    {
        $transactionType = strtoupper((string) ($options['transaction_type'] ?? TransactionType::NEW_WORK_REGISTRATION->value));

        if (!in_array($transactionType, [
            TransactionType::NEW_WORK_REGISTRATION->value,
            TransactionType::REVISED_REGISTRATION->value,
        ], true)) {
            throw new InvalidArgumentException("Unsupported transaction type [{$transactionType}] for CWR export. Supported types: NWR, REV.");
        }

        return $transactionType;
    }

    private function resolveWorkTransactionType(object $work, array $options): string
    {
        $workTransactionType = $work->transactionType ?? $work->transaction_type ?? null;

        if ($workTransactionType instanceof TransactionType) {
            $workTransactionType = $workTransactionType->value;
        }

        if ($workTransactionType === null || $workTransactionType === '') {
            return $this->resolveTransactionType($options);
        }

        $transactionType = strtoupper((string) $workTransactionType);

        if (!in_array($transactionType, [
            TransactionType::NEW_WORK_REGISTRATION->value,
            TransactionType::REVISED_REGISTRATION->value,
        ], true)) {
            throw new InvalidArgumentException("Unsupported work transaction type [{$transactionType}]. Supported types: NWR, REV.");
        }

        return $transactionType;
    }

    /**
     * @return class-string<NwrRecord|RevRecord>
     */
    private function resolveTransactionRecordClassForType(string $transactionType): string
    {
        return $transactionType === TransactionType::REVISED_REGISTRATION->value
            ? RevRecord::class
            : NwrRecord::class;
    }

    public function getSkippedWorks(): array
    {
        return $this->skippedWorks;
    }
}

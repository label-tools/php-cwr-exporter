<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules\Rules;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Validators\TransactionRules\AbstractTransactionRule;

class EnsureTransactionalOrderRule extends AbstractTransactionRule
{
    /**
     * Coarse global ordering buckets (Rule 18-style).
     * Key point: SPT must be strictly BEFORE OPU (not equal).
     */
    private const RECORD_ORDER = [
        'NWR' => 1,

        'SPU' => 2,
        'SPT' => 3,

        'OPU' => 4,

        'SWR' => 5,
        'SWT' => 6,
        'PWR' => 7,

        'OWR' => 8,

        'ALT' => 9,
        'PER' => 10,
        'REC' => 11,
    ];

    /**
     * Adjacency rules (subset) that catch invalid but "non-decreasing" sequences.
     */
    private const MUST_FOLLOW = [
        // SPT may only follow SPU, NPN, or another SPT.
        'SPT' => ['SPU', 'NPN', 'SPT'],

        // SWT may only follow SWR, NWN, or another SWT.
        'SWT' => ['SWR', 'NWN', 'SWT'],

        // PWR may only follow SWR, SWT, or another PWR.
        'PWR' => ['SWR', 'SWT', 'PWR'],
    ];

    public function validate(WorkDefinition $work): void
    {
        foreach ($work->writers ?? [] as $writer) {
            if (! $this->isControlledWriter($writer) && $this->writerHasPublisherLink($writer)) {
                $writerId = $writer->interestedPartyNumber ?? '(unknown writer)';

                throw new InvalidArgumentException(sprintf(
                    'PWR cannot be emitted for uncontrolled writer %s.',
                    $writerId
                ));
            }
        }

        $sequence = $this->buildRecordSequence($work);

        $lastPosition = 0;
        $lastRecord = null;

        foreach ($sequence as $record) {
            $position = self::RECORD_ORDER[$record] ?? null;
            if ($position === null) {
                // Unknown record types are ignored by this validator.
                $lastRecord = $record;
                continue;
            }

            // 1) Coarse global ordering
            if ($position < $lastPosition) {
                throw new InvalidArgumentException(sprintf(
                    'Record %s cannot follow %s per CWR ordering rules.',
                    $record,
                    $lastRecord ?? 'start'
                ));
            }

            // 2) Adjacency rules for special records
            if (isset(self::MUST_FOLLOW[$record])) {
                $allowed = self::MUST_FOLLOW[$record];
                if ($lastRecord === null || ! in_array($lastRecord, $allowed, true)) {
                    throw new InvalidArgumentException(sprintf(
                        'Record %s must follow one of [%s], but followed %s.',
                        $record,
                        implode(', ', $allowed),
                        $lastRecord ?? 'start'
                    ));
                }
            }

            $lastPosition = $position;
            $lastRecord = $record;
        }
    }

    /**
     * Builds the logical sequence of record types that will be emitted for the given work.
     *
     * IMPORTANT:
     * - Emit SPU (+ its SPTs) first, then OPU (no SPT).
     * - Emit SWR blocks first, then OWR.
     * - Emit PWR only when the controlled writer has a linked publisher.
     *
     * @return string[]
     */
    private function buildRecordSequence(WorkDefinition $work): array
    {
        $sequence = ['NWR'];

        // Publishers: controlled first (SPU + SPT), then uncontrolled (OPU).
        $controlledPublishers = [];
        $uncontrolledPublishers = [];

        foreach ($work->publishers as $publisher) {
            if ($publisher->controlled) {
                $controlledPublishers[] = $publisher;
            } else {
                $uncontrolledPublishers[] = $publisher;
            }
        }

        foreach ($controlledPublishers as $publisher) {
            $sequence[] = 'SPU';

            // SPU may have 0+ SPT records. If you require at least one, enforce it here.
            foreach ($publisher->territories as $territory) {
                $sequence[] = 'SPT';
            }
        }

        foreach ($uncontrolledPublishers as $publisher) {
            // OPU must not have SPT.
            if (! empty($publisher->territories)) {
                throw new InvalidArgumentException(sprintf(
                    'OPU publisher "%s" has territories; SPT is not allowed for OPU.',
                    $publisher->name ?? '(unknown)'
                ));
            }

            $sequence[] = 'OPU';
        }

        // Writers: controlled first, then uncontrolled.
        foreach ($this->orderWritersControlledFirst($work->writers) as $writer) {
            if ($this->isControlledWriter($writer)) {
                $sequence[] = 'SWR';

                foreach ($writer->territories as $territory) {
                    $sequence[] = 'SWT';
                }

                // Only emit PWR if there is a linked publisher reference.
                if (! empty($writer->publisher_interested_party_number)) {
                    $sequence[] = 'PWR';
                }
            } else {
                $sequence[] = 'OWR';
            }
        }

        foreach ($work->alternateTitles as $alt) {
            $sequence[] = 'ALT';
        }

        foreach ($work->performingArtists as $artist) {
            $sequence[] = 'PER';
        }

        foreach ($work->recordings as $recording) {
            $sequence[] = 'REC';
        }

        return $sequence;
    }

    /**
     * Ensures controlled writers (SWR) precede uncontrolled writers (OWR).
     *
     * @param array $writers
     * @return array
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

    private function writerHasPublisherLink(object $writer): bool
    {
        if (property_exists($writer, 'publisherInterestedPartyNumber')) {
            return !empty($writer->publisherInterestedPartyNumber);
        }

        if (property_exists($writer, 'publisher_interested_party_number')) {
            return !empty($writer->publisher_interested_party_number);
        }

        return false;
    }
}

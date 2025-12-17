<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules\Rules;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Validators\TransactionRules\AbstractTransactionRule;

class EnsureTransactionalOrderRule extends AbstractTransactionRule
{
    /**
     * Subset of record types emitted by the exporter in the same order defined by CWR Rule 18.
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

    public function validate(WorkDefinition $work): void
    {
        $sequence = $this->buildRecordSequence($work);

        $lastPosition = 0;
        foreach ($sequence as $record) {
            $position = self::RECORD_ORDER[$record] ?? null;

            if ($position === null) {
                continue;
            }

            if ($position < $lastPosition) {
                throw new InvalidArgumentException('CWR transactional records must follow the fixed ordering defined in Rule 18.');
            }

            $lastPosition = $position;
        }
    }

    /**
     * Builds the logical sequence of record types that will be emitted for the given work.
     *
     * @return string[]
     */
    private function buildRecordSequence(WorkDefinition $work): array
    {
        $sequence = ['NWR'];

        foreach ($work->publishers as $publisher) {
            $sequence[] = $publisher->controlled ? 'SPU' : 'OPU';
            foreach ($publisher->territories as $territory) {
                $sequence[] = 'SPT';
            }
        }

        foreach ($work->writers as $writer) {
            if ($this->isControlledWriter($writer)) {
                $sequence[] = 'SWR';
                foreach ($writer->territories as $territory) {
                    $sequence[] = 'SWT';
                }
                $sequence[] = 'PWR';
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
}

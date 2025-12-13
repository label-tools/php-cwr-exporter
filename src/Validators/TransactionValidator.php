<?php

namespace LabelTools\PhpCwrExporter\Validators;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\PublisherDefinition;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Definitions\WriterDefinition;
use LabelTools\PhpCwrExporter\Enums\PublisherType;
use LabelTools\PhpCwrExporter\Enums\WriterDesignation;

class TransactionValidator
{
    private const TOLERANCE = 6; // ±0.06% in CWR hundredths format

    public function validate(WorkDefinition $work): void
    {
        $publishers = $work->publishers ?? [];
        $writers    = $work->writers    ?? [];

        $this->ensureWriterPresence($writers);
        $this->ensureOriginalPublisherUniqueness($publishers);
        $this->ensureSpuOwnershipForOriginalOrAcquirer($publishers);
        $this->validatePublisherOwnershipTotals($publishers);
        $this->validateWriterOwnershipTotals($writers);
        $this->ensurePublisherTerritoriesForCollectionShares($publishers);
        $this->ensureWriterTerritoriesForCollectionShares($writers);
        $this->validateCollectionSharesByTerritory($publishers, $writers);
    }

    private function ensureWriterPresence(array $writers): void
    {
        $hasRequiredDesignation = false;
        foreach ($writers as $writer) {
            $designation = $writer->writerDesignationCode instanceof WriterDesignation
                ? $writer->writerDesignationCode->value
                : (string) $writer->writerDesignationCode;

            if (in_array($designation, [
                WriterDesignation::COMPOSER_AUTHOR->value,
                WriterDesignation::AUTHOR->value,
                WriterDesignation::COMPOSER->value,
            ], true)) {
                $hasRequiredDesignation = true;
                break;
            }
        }

        if (!$hasRequiredDesignation) {
            throw new InvalidArgumentException('Each transaction must contain at least one writer with designation CA, A, or C.');
        }
    }

    private function ensureOriginalPublisherUniqueness(array $publishers): void
    {
        $originalCount = 0;
        foreach ($publishers as $publisher) {
            $type = $publisher->publisherType instanceof PublisherType
                ? $publisher->publisherType->value
                : (string) $publisher->publisherType;
            if ($type === PublisherType::ORIGINAL_PUBLISHER->value) {
                $originalCount++;
            }
        }

        if ($originalCount > 1) {
            throw new InvalidArgumentException('Only one Original Publisher (type E) is allowed in a publisher chain.');
        }
    }

    private function ensureSpuOwnershipForOriginalOrAcquirer(array $publishers): void
    {
        $totals = ['pr' => 0, 'mr' => 0, 'sr' => 0];

        foreach ($publishers as $publisher) {
            $type = $publisher->publisherType instanceof PublisherType
                ? $publisher->publisherType->value
                : (string) $publisher->publisherType;

            if (!in_array($type, [PublisherType::ORIGINAL_PUBLISHER->value, PublisherType::ACQUIRER->value], true)) {
                continue;
            }

            $totals['pr'] += $this->normalizeShare($publisher->prOwnershipShare ?? 0);
            $totals['mr'] += $this->normalizeShare($publisher->mrOwnershipShare ?? 0, 100);
            $totals['sr'] += $this->normalizeShare($publisher->srOwnershipShare ?? 0, 100);
        }

        if ($totals['pr'] === 0 && $totals['mr'] === 0 && $totals['sr'] === 0) {
            throw new InvalidArgumentException('Publisher chain must include ownership (>0%) for at least one of PR/MR/SR on SPU records of type E or AQ.');
        }
    }

    private function validatePublisherOwnershipTotals(array $publishers): void
    {
        $prTotal = 0;
        $mrTotal = 0;
        $srTotal = 0;

        /** @var PublisherDefinition $publisher */
        foreach ($publishers as $publisher) {
            $prTotal += $this->normalizeShare($publisher->prOwnershipShare ?? 0, 50);
            $mrTotal += $this->normalizeShare($publisher->mrOwnershipShare ?? 0, 100);
            $srTotal += $this->normalizeShare($publisher->srOwnershipShare ?? 0, 100);
        }

        if ($prTotal > (5000 + self::TOLERANCE)) {
            throw new InvalidArgumentException(sprintf(
                'Total publisher PR ownership share %.2f%% exceeds allowed 50%% (±0.06%%).',
                $this->asPercent($prTotal)
            ));
        }

        foreach ([['mr', $mrTotal], ['sr', $srTotal]] as [$right, $total]) {
            if ($total > (10000 + self::TOLERANCE)) {
                throw new InvalidArgumentException(sprintf(
                    'Total publisher %s ownership share %.2f%% exceeds allowed 100%% (±0.06%%).',
                    strtoupper($right),
                    $this->asPercent($total)
                ));
            }
        }
    }

    private function validateWriterOwnershipTotals(array $writers): void
    {
        $prTotal = 0;
        $mrTotal = 0;
        $srTotal = 0;

        /** @var WriterDefinition $writer */
        foreach ($writers as $writer) {
            $prTotal += $this->normalizeShare($writer->prOwnershipShare ?? 0, 100);
            $mrTotal += $this->normalizeShare($writer->mrOwnershipShare ?? 0, 100);
            $srTotal += $this->normalizeShare($writer->srOwnershipShare ?? 0, 100);
        }

        if ($prTotal !== 0 && $prTotal < (5000 - self::TOLERANCE)) {
            throw new InvalidArgumentException(sprintf(
                'Total writer PR ownership share %.2f%% must be 0%% or at least 50%% (±0.06%%).',
                $this->asPercent($prTotal)
            ));
        }

        foreach ([['PR', $prTotal], ['MR', $mrTotal], ['SR', $srTotal]] as [$right, $total]) {
            if ($total > (10000 + self::TOLERANCE)) {
                throw new InvalidArgumentException(sprintf(
                    'Total writer %s ownership share %.2f%% exceeds allowed 100%% (±0.06%%).',
                    $right,
                    $this->asPercent($total)
                ));
            }
        }
    }

    private function ensurePublisherTerritoriesForCollectionShares(array $publishers): void
    {
        foreach ($publishers as $publisher) {
            $hasCollection = $this->publisherHasCollectionShares($publisher);
            $territories = $publisher->territories ?? [];
            if ($hasCollection && empty($territories)) {
                throw new InvalidArgumentException("Publisher {$publisher->interestedPartyNumber} has collection shares but no SPT territory records.");
            }
        }
    }

    private function ensureWriterTerritoriesForCollectionShares(array $writers): void
    {
        foreach ($writers as $writer) {
            $isControlled = property_exists($writer, 'controlled') ? (bool) $writer->controlled : true;
            if (!$isControlled) {
                continue;
            }

            $hasCollection = $this->writerHasCollectionShares($writer);
            $territories = $writer->territories ?? [];
            if ($hasCollection && empty($territories)) {
                throw new InvalidArgumentException("Writer {$writer->interestedPartyNumber} has collection shares but no SWT territory records.");
            }
        }
    }

    private function validateCollectionSharesByTerritory(array $publishers, array $writers): void
    {
        $territoryTotals = [];

        foreach ($publishers as $publisher) {
            foreach ($publisher->territories ?? [] as $territory) {
                $code = (string) ($territory['tis_code'] ?? '');
                if ($code === '') {
                    continue;
                }
                $this->addTerritoryShare($territoryTotals, $code, 'pr', $territory['pr_collection_share'] ?? 0, 50);
                $this->addTerritoryShare($territoryTotals, $code, 'mr', $territory['mr_collection_share'] ?? 0, 100);
                $this->addTerritoryShare($territoryTotals, $code, 'sr', $territory['sr_collection_share'] ?? 0, 100);
            }
        }

        foreach ($writers as $writer) {
            foreach ($writer->territories ?? [] as $territory) {
                $code = (string) ($territory['tis_code'] ?? '');
                if ($code === '') {
                    continue;
                }
                $this->addTerritoryShare($territoryTotals, $code, 'pr', $territory['pr_collection_share'] ?? 0, 100);
                $this->addTerritoryShare($territoryTotals, $code, 'mr', $territory['mr_collection_share'] ?? 0, 100);
                $this->addTerritoryShare($territoryTotals, $code, 'sr', $territory['sr_collection_share'] ?? 0, 100);
            }
        }

        foreach ($territoryTotals as $code => $shares) {
            foreach (['pr', 'mr', 'sr'] as $right) {
                if (($shares[$right] ?? 0) > (10000 + self::TOLERANCE)) {
                    throw new InvalidArgumentException(sprintf(
                        'Total %s collection share for territory %s is %.2f%% and exceeds 100%% (±0.06%%).',
                        strtoupper($right),
                        $code,
                        $this->asPercent($shares[$right])
                    ));
                }
            }
        }
    }

    private function addTerritoryShare(array &$totals, string $code, string $right, int|float $rawShare, int $maxPercent): void
    {
        $normalized = $this->normalizeShare($rawShare, $maxPercent);
        $totals[$code][$right] = ($totals[$code][$right] ?? 0) + $normalized;
    }

    private function publisherHasCollectionShares(PublisherDefinition $publisher): bool
    {
        foreach ($publisher->territories ?? [] as $territory) {
            foreach (['pr_collection_share', 'mr_collection_share', 'sr_collection_share'] as $key) {
                $value = $territory[$key] ?? 0;
                if ($this->normalizeShare($value, 100) > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    private function writerHasCollectionShares(WriterDefinition $writer): bool
    {
        foreach ($writer->territories ?? [] as $territory) {
            foreach (['pr_collection_share', 'mr_collection_share', 'sr_collection_share'] as $key) {
                $value = $territory[$key] ?? 0;
                if ($this->normalizeShare($value, 100) > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    private function normalizeShare(int|float|null $share, int $maxPercent = 100): int
    {
        $share ??= 0.0;
        if ($share < 0 || $share > $maxPercent) {
            throw new InvalidArgumentException(
                sprintf('Share must be between 0 and %d. Given: %s', $maxPercent, $share)
            );
        }

        return (int) round($share * 100);
    }

    private function asPercent(int $share): float
    {
        return $share / 100;
    }
}

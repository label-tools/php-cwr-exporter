<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\PublisherDefinition;
use LabelTools\PhpCwrExporter\Definitions\WriterDefinition;

abstract class AbstractTransactionRule implements TransactionRuleInterface
{
    protected const TOLERANCE = 6; // ±0.06% in CWR hundredths format

    protected function normalizeShare(int|float|null $share, int $maxPercent = 100): int
    {
        $share ??= 0.0;
        if ($share < 0 || $share > $maxPercent) {
            throw new InvalidArgumentException(
                sprintf('Share must be between 0 and %d. Given: %s', $maxPercent, $share)
            );
        }

        return (int) round($share * 100);
    }

    protected function asPercent(int $share): float
    {
        return $share / 100;
    }

    protected function publisherHasCollectionShares(PublisherDefinition $publisher): bool
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

    protected function writerHasCollectionShares(WriterDefinition $writer): bool
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

    protected function isControlledWriter(WriterDefinition $writer): bool
    {
        return property_exists($writer, 'controlled') ? (bool) $writer->controlled : true;
    }
}

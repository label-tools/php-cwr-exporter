<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules\Rules;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Validators\TransactionRules\AbstractTransactionRule;

class ValidateCollectionSharesByTerritoryRule extends AbstractTransactionRule
{
    public function validate(WorkDefinition $work): void
    {
        $publishers = $work->publishers ?? [];
        $writers    = $work->writers    ?? [];
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
}

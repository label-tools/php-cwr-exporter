<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules\Rules;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Enums\PublisherType;
use LabelTools\PhpCwrExporter\Validators\TransactionRules\AbstractTransactionRule;

class EnsureSpuOwnershipForOriginalOrAcquirerRule extends AbstractTransactionRule
{
    public function validate(WorkDefinition $work): void
    {
        $publishers = $work->publishers ?? [];
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
}

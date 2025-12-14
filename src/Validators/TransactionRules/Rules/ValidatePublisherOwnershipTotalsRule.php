<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules\Rules;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Validators\TransactionRules\AbstractTransactionRule;

class ValidatePublisherOwnershipTotalsRule extends AbstractTransactionRule
{
    public function validate(WorkDefinition $work): void
    {
        $publishers = $work->publishers ?? [];
        $prTotal = 0;
        $mrTotal = 0;
        $srTotal = 0;

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
}

<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules\Rules;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Validators\TransactionRules\AbstractTransactionRule;

class ValidateCombinedOwnershipTotalsRule extends AbstractTransactionRule
{
    public function validate(WorkDefinition $work): void
    {
        $publishers = $work->publishers ?? [];
        $writers = $work->writers ?? [];

        $prTotal = 0;
        $mrTotal = 0;
        $srTotal = 0;

        foreach ($publishers as $publisher) {
            $prTotal += $this->normalizeShare($publisher->prOwnershipShare ?? 0, 100);
            $mrTotal += $this->normalizeShare($publisher->mrOwnershipShare ?? 0, 100);
            $srTotal += $this->normalizeShare($publisher->srOwnershipShare ?? 0, 100);
        }

        foreach ($writers as $writer) {
            $prTotal += $this->normalizeShare($writer->prOwnershipShare ?? 0, 100);
            $mrTotal += $this->normalizeShare($writer->mrOwnershipShare ?? 0, 100);
            $srTotal += $this->normalizeShare($writer->srOwnershipShare ?? 0, 100);
        }

        $rights = [
            'PR' => $prTotal,
            'MR' => $mrTotal,
            'SR' => $srTotal,
        ];

        foreach ($rights as $right => $total) {
            $isZero = ($total >= 0 && $total <= self::TOLERANCE);
            $isHundred = ($total >= (10000 - self::TOLERANCE) && $total <= (10000 + self::TOLERANCE));

            if (!$isZero && !$isHundred) {
                throw new InvalidArgumentException(sprintf(
                    'Total combined %s ownership share must be 0%% or 100%% (±0.06%%), but it is %.2f%%.',
                    $right,
                    $this->asPercent($total)
                ));
            }
        }
    }
}

<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules\Rules;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Validators\TransactionRules\AbstractTransactionRule;

class ValidateWriterOwnershipTotalsRule extends AbstractTransactionRule
{
    public function validate(WorkDefinition $work): void
    {
        $writers = $work->writers ?? [];
        $prTotal = 0;
        $mrTotal = 0;
        $srTotal = 0;

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
}

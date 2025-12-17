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
        $mrTotal = 0;
        $srTotal = 0;

        foreach ($writers as $writer) {
            $mrTotal += $this->normalizeShare($writer->mrOwnershipShare ?? 0, 100);
            $srTotal += $this->normalizeShare($writer->srOwnershipShare ?? 0, 100);
        }

        foreach ([['MR', $mrTotal], ['SR', $srTotal]] as [$right, $total]) {
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

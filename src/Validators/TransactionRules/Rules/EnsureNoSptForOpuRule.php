<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules\Rules;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Validators\TransactionRules\AbstractTransactionRule;

class EnsureNoSptForOpuRule extends AbstractTransactionRule
{
    public function validate(WorkDefinition $work): void
    {
        foreach ($work->publishers ?? [] as $publisher) {
            $isControlled = property_exists($publisher, 'controlled') ? (bool) $publisher->controlled : true;
            if ($isControlled) {
                continue;
            }

            $territories = $publisher->territories ?? [];
            if (!empty($territories)) {
                $ipNumber = trim((string) ($publisher->interestedPartyNumber ?? '')) ?: 'unknown IP number';
                throw new InvalidArgumentException(
                    "Publisher {$ipNumber} is uncontrolled (OPU) but includes territories; SPT records cannot be used with OPU records."
                );
            }
        }
    }
}

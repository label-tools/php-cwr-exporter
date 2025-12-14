<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules\Rules;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Validators\TransactionRules\AbstractTransactionRule;

class EnsureWriterTerritoriesForCollectionSharesRule extends AbstractTransactionRule
{
    public function validate(WorkDefinition $work): void
    {
        $writers = $work->writers ?? [];
        foreach ($writers as $writer) {
            if (!$this->isControlledWriter($writer)) {
                continue;
            }

            $hasCollection = $this->writerHasCollectionShares($writer);
            $territories = $writer->territories ?? [];
            if ($hasCollection && empty($territories)) {
                throw new InvalidArgumentException("Writer {$writer->interestedPartyNumber} has collection shares but no SWT territory records.");
            }
        }
    }
}

<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules\Rules;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Validators\TransactionRules\AbstractTransactionRule;

class EnsurePublisherTerritoriesForCollectionSharesRule extends AbstractTransactionRule
{
    public function validate(WorkDefinition $work): void
    {
        $publishers = $work->publishers ?? [];
        foreach ($publishers as $publisher) {
            $hasCollection = $this->publisherHasCollectionShares($publisher);
            $territories = $publisher->territories ?? [];
            if ($hasCollection && empty($territories)) {
                throw new InvalidArgumentException("Publisher {$publisher->interestedPartyNumber} has collection shares but no SPT territory records.");
            }
        }
    }
}

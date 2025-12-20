<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules\Rules;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Enums\PublisherType;
use LabelTools\PhpCwrExporter\Validators\TransactionRules\AbstractTransactionRule;

class EnsureOriginalPublisherUniquenessRule extends AbstractTransactionRule
{
    public function validate(WorkDefinition $work): void
    {
        $publishers = $work->publishers ?? [];
        $originalCount = 0;
        foreach ($publishers as $publisher) {
            if (property_exists($publisher, 'controlled') && $publisher->controlled === false) {
                continue;
            }

            $type = $publisher->publisherType instanceof PublisherType
                ? $publisher->publisherType->value
                : (string) $publisher->publisherType;
            if ($type === PublisherType::ORIGINAL_PUBLISHER->value) {
                $originalCount++;
            }
        }

        if ($originalCount > 1) {
            throw new InvalidArgumentException('Only one Original Publisher (type E) is allowed in a publisher chain.');
        }
    }
}

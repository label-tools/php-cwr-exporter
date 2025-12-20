<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules\Rules;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Validators\TransactionRules\AbstractTransactionRule;

class ValidatePublisherPrOwnershipCapRule extends AbstractTransactionRule
{
    private const MAX_PUBLISHER_PR = 5000; // 50% in hundredths

    public function validate(WorkDefinition $work): void
    {
        foreach ($work->publishers ?? [] as $publisher) {
            $share = $this->normalizeShare($publisher->prOwnershipShare ?? 0, 100);

            if ($share > self::MAX_PUBLISHER_PR + self::TOLERANCE) {
                $name = $publisher->publisherName
                    ?? $publisher->interestedPartyNumber
                    ?? '(unknown publisher)';

                throw new InvalidArgumentException(sprintf(
                    'publisher PR ownership share %.2f%% exceeds allowed 50%% (±0.06%%) for %s.',
                    $this->asPercent($share),
                    $name
                ));
            }
        }
    }
}

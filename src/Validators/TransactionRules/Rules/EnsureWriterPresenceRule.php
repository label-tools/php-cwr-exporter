<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules\Rules;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Enums\WriterDesignation;
use LabelTools\PhpCwrExporter\Validators\TransactionRules\AbstractTransactionRule;

class EnsureWriterPresenceRule extends AbstractTransactionRule
{
    public function validate(WorkDefinition $work): void
    {
        $writers = $work->writers ?? [];
        $hasRequiredDesignation = false;
        foreach ($writers as $writer) {
            $designation = $writer->writerDesignationCode instanceof WriterDesignation
                ? $writer->writerDesignationCode->value
                : (string) $writer->writerDesignationCode;

            if (in_array($designation, [
                WriterDesignation::COMPOSER_AUTHOR->value,
                WriterDesignation::AUTHOR->value,
                WriterDesignation::COMPOSER->value,
            ], true)) {
                $hasRequiredDesignation = true;
                break;
            }
        }

        if (!$hasRequiredDesignation) {
            throw new InvalidArgumentException('Each transaction must contain at least one writer with designation CA, A, or C.');
        }
    }
}

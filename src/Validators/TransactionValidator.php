<?php

namespace LabelTools\PhpCwrExporter\Validators;

use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;
use LabelTools\PhpCwrExporter\Validators\TransactionRules\DefaultTransactionRuleSet;
use LabelTools\PhpCwrExporter\Validators\TransactionRules\TransactionRuleInterface;

class TransactionValidator
{
    /** @var TransactionRuleInterface[] */
    private array $rules;

    /**
     * @param TransactionRuleInterface[]|null $rules
     */
    public function __construct(?array $rules = null)
    {
        $this->rules = $rules ?? DefaultTransactionRuleSet::build();
    }

    public function validate(WorkDefinition $work): void
    {
        foreach ($this->rules as $rule) {
            $rule->validate($work);
        }
    }
}

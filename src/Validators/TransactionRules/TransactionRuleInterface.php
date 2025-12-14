<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules;

use LabelTools\PhpCwrExporter\Definitions\WorkDefinition;

interface TransactionRuleInterface
{
    public function validate(WorkDefinition $work): void;
}

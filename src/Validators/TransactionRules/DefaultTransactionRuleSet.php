<?php

namespace LabelTools\PhpCwrExporter\Validators\TransactionRules;

class DefaultTransactionRuleSet
{
    /**
     * @return TransactionRuleInterface[]
     */
    public static function build(): array
    {
        $namespace = __NAMESPACE__ . '\\Rules\\';
        $directory = __DIR__ . '/Rules';

        if (!is_dir($directory)) {
            return [];
        }

        $rules = [];
        foreach (glob($directory . '/*.php') as $file) {
            $fqcn = $namespace . pathinfo($file, PATHINFO_FILENAME);
            if (!class_exists($fqcn)) {
                continue;
            }

            $ref = new \ReflectionClass($fqcn);
            if (!$ref->implementsInterface(TransactionRuleInterface::class) || !$ref->isInstantiable()) {
                continue;
            }

            $rules[] = new $fqcn();
        }

        // Keep deterministic order (alphabetical by class name)
        usort($rules, fn($a, $b) => strcmp(get_class($a), get_class($b)));

        return $rules;
    }
}

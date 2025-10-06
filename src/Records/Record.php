<?php

namespace LabelTools\PhpCwrExporter\Records;

use BackedEnum;

abstract class Record
{
    /**
     * @var string The record type, e.g. 'HDR', 'GRH'
     */
    protected static string $recordType;

    protected int $transactionSequence = 0; // Default transaction sequence number
    protected int $recordSequence = 0; // Default record sequence number

    /**
     * @var array Data to be formatted into a string
     */
    protected array $data = [];

    /**
     * @var string Format for the record string
     */
    protected string $stringFormat;

    private const INDEX_RECORD_TYPE = 1;

    protected function validateBeforeToString(): void
    {
        if ($this->hasRecordPrefix() && empty($this->data[self::INDEX_RECORD_TYPE])) {
            throw new \LogicException(
                sprintf('The record prefix for %s has not been set. Please call setRecordPrefix() before generating the string.', static::class)
            );
        }
    }

    public function __construct()
    {
        if (empty(static::$recordType)) {
            throw new \LogicException('Record type must be defined in the subclass.');
        }

        // If the format expects a full 19-character prefix, set placeholders for transaction and record sequence numbers
        if (!$this->hasRecordPrefix()) {
            // Initialize the data array with the record type
            $this->data[self::INDEX_RECORD_TYPE] = static::$recordType;
        }
    }

    public function hasRecordPrefix(): bool
    {
        return str_starts_with($this->stringFormat, "%-19s");
    }

    public function setRecordSequence(int $recordSequence): self
    {
        $this->recordSequence = $recordSequence;
        return $this;
    }

    public function setTransactionSequence(int $transactionSequence): self
    {
        $this->transactionSequence = $transactionSequence;
        return $this;
    }

    /**
     * Sets the record prefix (record type + transaction sequence + record sequence) for formats with prefix.
     */
    public function setRecordPrefix($transactionSequence, $recordSequence): self
    {
        //@todo we need to block record that dont need record prefix from calling this
        $prefix = sprintf('%-3s%08d%08d', static::$recordType, $transactionSequence, $recordSequence);
        $this->data[self::INDEX_RECORD_TYPE] = $prefix;
        return $this;
    }

    public function toString(): string
    {
        $this->validateBeforeToString();
        $data = $this->data;
        ksort($data);

        return vsprintf($this->stringFormat, $data);
    }

    protected function defaultDate(?string $value = null, string $format = 'Ymd'): string
    {
        return $value ?? (new \DateTime())->format($format);
    }

    protected function boolToValue(null|bool|string $value): string
    {
        if (is_null($value) || is_bool($value)) {
            return is_null($value) ? '' : ($value ? 'Y' : 'N');
        }

        if (!in_array($value, ['Y', 'N'], true)) {
            throw new \InvalidArgumentException("Recorded Indicator must be Y, N");
        }

        return $value;

    }

    protected function flagToValue(null|bool|string $flag = null): string
    {
        if (is_null($flag) || is_bool($flag)) {
            return is_null($flag) ? '' : ($flag ? 'Y' : 'N');
        }

        if (!in_array($flag, ['Y', 'N', 'U'], true)) {
            throw new \InvalidArgumentException("Recorded Indicator must be Y, N, or U");
        }

        return $flag;
    }

    protected function setEnumValue(int $key, string $enumClass, BackedEnum|string $value, ?string $fieldLabel = null, bool $isRequired = true): self
    {
        $fieldLabel ??= preg_replace('/(?<!^)[A-Z]/', ' $0', (new \ReflectionClass($enumClass))->getShortName());

        if ($isRequired && empty($value)) {
            throw new \InvalidArgumentException("{$fieldLabel} is required.");
        } elseif (empty($value)) {
            $this->data[$key] = '';
            return $this;
        }

        try {
            $enumValue = $value instanceof $enumClass ? $value : $enumClass::from($value);
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException("Invalid {$fieldLabel}: {$value}");
        }

        $this->data[$key] = $enumValue->value;
        return $this;
    }
}
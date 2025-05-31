<?php

namespace LabelTools\PhpCwrExporter\Records;

abstract class Record
{
    /**
     * @var string The record type, e.g. 'HDR', 'GRH'
     */
    protected static string $recordType;

    /**
     * @var array Data to be formatted into a string
     */
    protected array $data = [];

    /**
     * @var string Format for the record string
     */
    protected string $stringFormat;

    private const INDEX_RECORD_TYPE = 1;

    abstract protected function validateBeforeToString(): void;

    public function __construct()
    {
        if (empty(static::$recordType)) {
            throw new \LogicException('Record type must be defined in the subclass.');
        }
        // Initialize the data array with the record type
        $this->data[self::INDEX_RECORD_TYPE] = static::$recordType;
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
}
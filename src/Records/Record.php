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
        $data = $this->data;
        ksort($data);
        return vsprintf($this->stringFormat, $data);
    }

    protected function defaultDate(?string $value = null, string $format = 'Ymd'): string
    {
        return $value ?? (new \DateTime())->format($format);
    }
}
<?php

namespace LabelTools\PhpCwrExporter\Contracts;

/**
 * Represents a single fixed-width CWR record.
 */
interface RecordInterface
{

    public function toString(): string;
}
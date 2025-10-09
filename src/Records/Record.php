<?php

namespace LabelTools\PhpCwrExporter\Records;

use BackedEnum;
use DateTime;

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


        return $this->mbVsprintf($this->stringFormat, $data);
    }

    /**
     * A multi-byte-safe version of vsprintf.
     *
     * It parses the format string and applies padding to string arguments ('s' type specifier)
     * using mb_strlen to correctly handle multi-byte characters. Other format specifiers are
     * passed through to the native sprintf.
     *
     * @param string $format The format string.
     * @param array  $args   The arguments.
     * @return string The formatted string.
     */
    private function mbVsprintf(string $format, array $args): string
    {
        $argIndex = 1;
        // Split the format string by '%' placeholders, keeping the delimiters
        $parts = preg_split('/(%[-]?\d*[sduxX])/', $format, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $newFormat = '';
        foreach ($parts as $part) {
            // Check if the part is a format specifier
            if (preg_match('/^%(?<align>[-]?)(?<pad>\d*)(?<type>[sduxX])$/', $part, $matches)) {
                $value = $args[$argIndex++] ?? '';
                $type = $matches['type'];
                $align = $matches['align'];
                $padWidth = (int)($matches['pad'] ?? 0);

                if ($type === 's') {
                    // Handle string padding with multi-byte awareness
                    $valueStr = (string)$value;
                    $valueLen = mb_strlen($valueStr);
                    $normalValueLen = strlen($valueStr);

                    if ($padWidth > 0 && $valueLen !== $normalValueLen) {
                        // Number of extra bytes beyond character count (e.g., 'Á' => 2 bytes vs 1 char => +1)
                        $byteOverhead = $normalValueLen - $valueLen; // always >= 0 for multibyte
                        // Build a new specifier that increases the width by the byte overhead
                        // so sprintf pads by enough bytes to yield the intended display width.
                        $adjustedWidth = $padWidth + $byteOverhead;
                        $part = '%' . $align . $adjustedWidth . $type;
                    }
                }
            }
            $newFormat .= $part;
        }

        return vsprintf($newFormat, $args);
    }

    protected function setAlphaNumeric($index, ?string $value): void
    {
        $value = trim($value);
        // Ensure ASCII only
        if (!mb_check_encoding($value, 'ASCII')) {
            throw new \InvalidArgumentException("Value must be ASCII: {$value}");
        }

        // Enforce uppercase for alphabetic characters, but allow ANY printable ASCII punctuation
        $upper = strtoupper($value);

        // Reject control characters (NUL..US and DEL). Allow space (0x20) through tilde (0x7E).
        if (!preg_match('/^[\x20-\x7E]+$/', $upper)) {
            throw new \InvalidArgumentException("Value must contain printable ASCII characters only: {$value}");
        }

        $this->data[$index] = $upper;
    }

    /**Numeric fields are to be right justified and zero filled. If there is an
    implied decimal point, it will be defined in the record layout. If there
    is no data to be entered in a numeric field, zeroes must be entered. */
    protected function setNumeric($index, ?int $value): void
    {
        if (empty($value)) {
            $this->data[$index] = 0;
            return;
        }
        $this->data[$index] = (int)$value;
    }

    /**
     * Dates are all formatted as YYYYMMDD. If there is no data to be entered in a date field, zeroes must be entered.
     */
    protected function setDate($index, null|string|DateTime $date = null, $defaultDateOnEmpty = false, $fieldName = null): void
    {
        $format = 'Ymd';
        $fieldName ??= 'Date';

        if (empty($date)) {
            $this->data[$index] = $defaultDateOnEmpty ? (new DateTime())->format($format) : '00000000';
            return;
        }

        if ($date instanceof DateTime) {
            $this->data[$index] = $date->format($format);
            return;
        }

        $d = DateTime::createFromFormat($format, $date);

        if (!$d || $d->format($format) !== $date) {
            throw new \InvalidArgumentException("{$fieldName} must be a valid date in 'YYYYMMDD' format. Given: {$date}");
        }

        $this->data[$index] = $d->format($format);
    }

    protected function setTime($index, null|string|DateTime $time = null, $defaultTimeOnEmpty = false, $fieldName = null): void
    {
        $format = 'His';
        $fieldName ??= 'Time';

        if (empty($time)) {
            $this->data[$index] = $defaultTimeOnEmpty ? (new DateTime())->format($format) : '000000';
            return;
        }

        $this->data[$index] = $time instanceof DateTime ? $time->format($format) : (new DateTime($time))->format($format);
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

    /**
     * Normalizes a share value from an int or float to a CWR-compliant integer.
     *
     * @param int|float|null $share The share value (e.g., 50.5 for 50.5%).
     * @param int $maxPercent The maximum allowed percentage (e.g., 50 or 100).
     * @param string $fieldName The name of the field for error messages.
     * @return int The CWR-formatted integer share (e.g., 5050).
     */
    protected function normalizeShare(int|float|null $share, int $maxPercent, string $fieldName): int
    {
        $share ??= 0.0;

        if ($share < 0 || $share > $maxPercent) {
            throw new \InvalidArgumentException(
                sprintf('%s must be between 0 and %d. Given: %s', $fieldName, $maxPercent, $share)
            );
        }

        // Convert to CWR format (e.g., 50.5 -> 5050)
        return (int) round($share * 100);
    }
}
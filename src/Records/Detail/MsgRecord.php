<?php

namespace LabelTools\PhpCwrExporter\Records\Detail;

use LabelTools\PhpCwrExporter\Records\Record;

class MsgRecord extends Record
{
    protected static string $recordType = 'MSG';

    protected const FIELD_MAP = [
        'message_type' => [20, 1],
        'original_record_sequence' => [21, 8],
        'record_type' => [29, 3],
        'message_level' => [32, 1],
        'validation_number' => [33, 3],
        'message_text' => [36, 150],
    ];

    protected string $stringFormat =
        "%-19s" . // Record Prefix
        "%-1s" .  // Message Type
        "%08d" .  // Original Record Sequence #
        "%-3s" .  // Record Type
        "%-1s" .  // Message Level
        "%-3s" .  // Validation Number
        "%-150s"; // Message Text

    protected const IDX_MESSAGE_TYPE = 2;
    protected const IDX_ORIGINAL_RECORD_SEQUENCE = 3;
    protected const IDX_CAUSING_RECORD_TYPE = 4;
    protected const IDX_MESSAGE_LEVEL = 5;
    protected const IDX_VALIDATION_NUMBER = 6;
    protected const IDX_MESSAGE_TEXT = 7;

    public function __construct(
        string $messageType,
        int $originalRecordSequence,
        string $recordType,
        string $messageLevel,
        string $validationNumber,
        string $messageText
    ) {
        parent::__construct();

        $this->setMessageType($messageType)
            ->setOriginalRecordSequence($originalRecordSequence)
            ->setRecordType($recordType)
            ->setMessageLevel($messageLevel)
            ->setValidationNumber($validationNumber)
            ->setMessageText($messageText);
    }

    public static function parseLine(string $line): array
    {
        return static::parseFixedWidth($line, static::FIELD_MAP);
    }

    public function setMessageType(string $messageType): self
    {
        $value = trim($messageType);
        $allowed = ['F', 'R', 'T', 'G', 'E'];
        if (!in_array($value, $allowed, true)) {
            throw new \InvalidArgumentException('Message Type must be one of: ' . implode(', ', $allowed));
        }
        $this->data[static::IDX_MESSAGE_TYPE] = $value;
        return $this;
    }

    public function setOriginalRecordSequence(int $sequence): self
    {
        $this->validateCount($sequence, 'Original Record Sequence', 0, 99999999);
        return $this->setNumeric(static::IDX_ORIGINAL_RECORD_SEQUENCE, $sequence, 'Original Record Sequence');
    }

    public function setRecordType(string $recordType): self
    {
        $value = trim($recordType);
        if ($value === '') {
            throw new \InvalidArgumentException('Record Type is required for MSG records.');
        }
        if (strlen($value) > 3) {
            throw new \InvalidArgumentException('Record Type must be 3 characters.');
        }
        return $this->setAlphaNumeric(static::IDX_CAUSING_RECORD_TYPE, $value, 'Record Type');
    }

    public function setMessageLevel(string $messageLevel): self
    {
        $value = trim($messageLevel);
        $allowed = ['E', 'G', 'T', 'R', 'F'];
        if (!in_array($value, $allowed, true)) {
            throw new \InvalidArgumentException('Message Level must be one of: ' . implode(', ', $allowed));
        }
        $this->data[static::IDX_MESSAGE_LEVEL] = $value;
        return $this;
    }

    public function setValidationNumber(string $validationNumber): self
    {
        $value = trim($validationNumber);
        if ($value === '') {
            throw new \InvalidArgumentException('Validation Number is required for MSG records.');
        }
        if (strlen($value) > 3) {
            throw new \InvalidArgumentException('Validation Number must be 3 characters.');
        }
        return $this->setAlphaNumeric(static::IDX_VALIDATION_NUMBER, $value, 'Validation Number');
    }

    public function setMessageText(string $messageText): self
    {
        $value = trim($messageText);
        if ($value === '') {
            throw new \InvalidArgumentException('Message Text is required for MSG records.');
        }
        if (strlen($value) > 150) {
            throw new \InvalidArgumentException('Message Text must be 150 characters or fewer.');
        }
        return $this->setAlphaNumeric(static::IDX_MESSAGE_TEXT, $value, 'Message Text');
    }
}

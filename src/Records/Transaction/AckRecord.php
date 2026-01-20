<?php

namespace LabelTools\PhpCwrExporter\Records\Transaction;

use DateTime;
use LabelTools\PhpCwrExporter\Records\Record;

class AckRecord extends Record
{
    protected static string $recordType = 'ACK';

    protected const FIELD_MAP = [
        'creation_date' => [20, 8],
        'creation_time' => [28, 6],
        'original_group_id' => [34, 5],
        'original_transaction_sequence' => [39, 8],
        'original_transaction_type' => [47, 3],
        'creation_title' => [50, 60],
        'submitter_creation_number' => [110, 20],
        'recipient_creation_number' => [130, 20],
        'processing_date' => [150, 8],
        'transaction_status' => [158, 2],
    ];

    protected string $stringFormat =
        "%-19s" . // Record Prefix
        "%-8s" .  // Creation Date
        "%-6s" .  // Creation Time
        "%05d" .  // Original Group ID
        "%08d" .  // Original Transaction Sequence #
        "%-3s" .  // Original Transaction Type
        "%-60s" . // Creation Title
        "%-20s" . // Submitter Creation #
        "%-20s" . // Recipient Creation #
        "%-8s" .  // Processing Date
        "%-2s";   // Transaction Status

    protected const IDX_CREATION_DATE = 2;
    protected const IDX_CREATION_TIME = 3;
    protected const IDX_ORIGINAL_GROUP_ID = 4;
    protected const IDX_ORIGINAL_TRANSACTION_SEQUENCE = 5;
    protected const IDX_ORIGINAL_TRANSACTION_TYPE = 6;
    protected const IDX_CREATION_TITLE = 7;
    protected const IDX_SUBMITTER_CREATION_NUMBER = 8;
    protected const IDX_RECIPIENT_CREATION_NUMBER = 9;
    protected const IDX_PROCESSING_DATE = 10;
    protected const IDX_TRANSACTION_STATUS = 11;

    public function __construct(
        string $creationDate,
        string $creationTime,
        int $originalGroupId,
        int $originalTransactionSequence,
        string $originalTransactionType,
        ?string $creationTitle = '',
        ?string $submitterCreationNumber = '',
        ?string $recipientCreationNumber = '',
        null|string|DateTime $processingDate = null,
        ?string $transactionStatus = null,
    ) {
        parent::__construct();

        $this->setCreationDate($creationDate)
            ->setCreationTime($creationTime)
            ->setOriginalGroupId($originalGroupId)
            ->setOriginalTransactionSequence($originalTransactionSequence)
            ->setOriginalTransactionType($originalTransactionType)
            ->setCreationTitle($creationTitle)
            ->setSubmitterCreationNumber($submitterCreationNumber)
            ->setRecipientCreationNumber($recipientCreationNumber)
            ->setProcessingDate($processingDate)
            ->setTransactionStatus($transactionStatus);
    }

    public static function parseLine(string $line): array
    {
        return static::parseFixedWidth($line, static::FIELD_MAP);
    }

    public function setCreationDate(string $creationDate): self
    {
        if (trim($creationDate) === '') {
            throw new \InvalidArgumentException('Creation Date is required for ACK records.');
        }
        return $this->setDate(static::IDX_CREATION_DATE, $creationDate, false, 'Creation Date');
    }

    public function setCreationTime(string $creationTime): self
    {
        $value = trim($creationTime);
        if ($value === '') {
            throw new \InvalidArgumentException('Creation Time is required for ACK records.');
        }
        if (!preg_match('/^\d{6}$/', $value)) {
            throw new \InvalidArgumentException('Creation Time must be HHMMSS.');
        }
        return $this->setTime(static::IDX_CREATION_TIME, $value, false, 'Creation Time');
    }

    public function setOriginalGroupId(int $groupId): self
    {
        $this->validateCount($groupId, 'Original Group ID', 0, 99999);
        return $this->setNumeric(static::IDX_ORIGINAL_GROUP_ID, $groupId, 'Original Group ID');
    }

    public function setOriginalTransactionSequence(int $sequence): self
    {
        $this->validateCount($sequence, 'Original Transaction Sequence', 0, 99999999);
        return $this->setNumeric(static::IDX_ORIGINAL_TRANSACTION_SEQUENCE, $sequence, 'Original Transaction Sequence');
    }

    public function setOriginalTransactionType(string $type): self
    {
        $value = trim($type);
        if ($value === '') {
            throw new \InvalidArgumentException('Original Transaction Type is required for ACK records.');
        }
        if (strlen($value) > 3) {
            throw new \InvalidArgumentException('Original Transaction Type must be 3 characters.');
        }
        return $this->setAlphaNumeric(static::IDX_ORIGINAL_TRANSACTION_TYPE, $value, 'Original Transaction Type');
    }

    public function setCreationTitle(?string $title): self
    {
        $value = trim((string) $title);
        if (strlen($value) > 60) {
            throw new \InvalidArgumentException('Creation Title must be 60 characters or fewer.');
        }
        return $this->setAlphaNumeric(static::IDX_CREATION_TITLE, $value, 'Creation Title');
    }

    public function setSubmitterCreationNumber(?string $number): self
    {
        $value = trim((string) $number);
        if (strlen($value) > 20) {
            throw new \InvalidArgumentException('Submitter Creation Number must be 20 characters or fewer.');
        }
        return $this->setAlphaNumeric(static::IDX_SUBMITTER_CREATION_NUMBER, $value, 'Submitter Creation Number');
    }

    public function setRecipientCreationNumber(?string $number): self
    {
        $value = trim((string) $number);
        if (strlen($value) > 20) {
            throw new \InvalidArgumentException('Recipient Creation Number must be 20 characters or fewer.');
        }
        return $this->setAlphaNumeric(static::IDX_RECIPIENT_CREATION_NUMBER, $value, 'Recipient Creation Number');
    }

    public function setProcessingDate(null|string|DateTime $processingDate): self
    {
        if (empty($processingDate)) {
            throw new \InvalidArgumentException('Processing Date is required for ACK records.');
        }
        return $this->setDate(static::IDX_PROCESSING_DATE, $processingDate, false, 'Processing Date');
    }

    public function setTransactionStatus(?string $status): self
    {
        $value = trim((string) $status);
        if ($value === '') {
            throw new \InvalidArgumentException('Transaction Status is required for ACK records.');
        }
        if (strlen($value) > 2) {
            throw new \InvalidArgumentException('Transaction Status must be 2 characters.');
        }
        return $this->setAlphaNumeric(static::IDX_TRANSACTION_STATUS, $value, 'Transaction Status');
    }
}

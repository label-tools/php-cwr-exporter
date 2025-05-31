<?php

namespace LabelTools\PhpCwrExporter\Records;

class GrtRecord extends Record
{
    protected static string $recordType = 'GRT'; // Always "GRT" *A{3}
    protected string $stringFormat = "%-3s%05d%08d%08d";

    private const INDEX_GROUP_ID = 2;
    private const INDEX_TRANSACTION_COUNT = 3;
    private const INDEX_RECORD_COUNT = 4;

    public function __construct(
        ?int $groupId = null,
        ?int $transactionCount = null,
        ?int $recordCount = null
    ) {
        parent::__construct();

        $this->data[self::INDEX_GROUP_ID] = 0;
        $this->data[self::INDEX_TRANSACTION_COUNT] = 0;
        $this->data[self::INDEX_RECORD_COUNT] = 0;

        if (! is_null($groupId)) {
            $this->setGroupId($groupId);
        }
        if (! is_null($transactionCount)) {
            $this->setTransactionCount($transactionCount);
        }
        if (! is_null($recordCount)) {
            $this->setRecordCount($recordCount);
        }
    }

    protected function validateBeforeToString(): void
    {

    }

    public function setGroupId(int $groupId): self
    {
        $this->validateGroupId($groupId);
        $this->data[self::INDEX_GROUP_ID] = $groupId;
        return $this;
    }

    public function setTransactionCount(int $transactionCount): self
    {
        $this->validateCount($transactionCount, 'Transaction Count');
        $this->data[self::INDEX_TRANSACTION_COUNT] = $transactionCount;
        return $this;
    }

    public function setRecordCount(int $recordCount): self
    {
        $this->validateCount($recordCount, 'Record Count');
        $this->data[self::INDEX_RECORD_COUNT] = $recordCount;
        return $this;
    }

    private function validateGroupId(int $groupId): void
    {
        if ($groupId < 1 || $groupId > 99999) {
            throw new \InvalidArgumentException("Group ID must be between 1 and 99999.");
        }

        // Additional rule: Ensure the Group ID is unique within this file
        if (! $this->isGroupIdUnique($groupId)) {
            throw new \InvalidArgumentException("Group ID must be unique within this file.");
        }
    }

    private function validateCount(int $count, string $fieldName): void
    {
        if ($count < 0 || $count > 99999999) {
            throw new \InvalidArgumentException("$fieldName must be between 0 and 99999999.");
        }
    }

    /**
     * Placeholder: implement actual file‑wide uniqueness check.
     */
    private function isGroupIdUnique(int $groupId): bool
    {
        return true;
    }
}
<?php

namespace LabelTools\PhpCwrExporter\Records;

class TrlRecord extends Record
{
    protected static string $recordType = 'TRL'; // Always "TRL" *A{3}
    protected string $stringFormat = "%-3s%05d%08d%08d";

    private const INDEX_GROUP_COUNT = 2;
    private const INDEX_TRANSACTION_COUNT = 3;
    private const INDEX_RECORD_COUNT = 4;

    public function __construct(
        ?int $groupCount = null,
        ?int $transactionCount = null,
        ?int $recordCount = null
    ) {
        parent::__construct();

        $this->data[self::INDEX_GROUP_COUNT] = 0;
        $this->data[self::INDEX_TRANSACTION_COUNT] = 0;
        $this->data[self::INDEX_RECORD_COUNT] = 0;

        if (! is_null($groupCount)) {
            $this->setGroupCount($groupCount);
        }
        if (! is_null($transactionCount)) {
            $this->setTransactionCount($transactionCount);
        }
        if (! is_null($recordCount)) {
            $this->setRecordCount($recordCount);
        }
    }

    public function setGroupCount(int $groupCount): self
    {
        $this->validateCount($groupCount, 'Group Count', 1, 99999);
        $this->data[self::INDEX_GROUP_COUNT] = $groupCount;
        return $this;
    }

    public function setTransactionCount(int $transactionCount): self
    {
        $this->validateCount($transactionCount, 'Transaction Count', 0, 99999999);
        $this->data[self::INDEX_TRANSACTION_COUNT] = $transactionCount;
        return $this;
    }

    public function setRecordCount(int $recordCount): self
    {
        $this->validateCount($recordCount, 'Record Count', 0, 99999999);
        $this->data[self::INDEX_RECORD_COUNT] = $recordCount;
        return $this;
    }

    private function validateCount(int $value, string $fieldName, int $min, int $max): void
    {
        if ($value < $min || $value > $max) {
            throw new \InvalidArgumentException("$fieldName must be between $min and $max.");
        }
    }
}

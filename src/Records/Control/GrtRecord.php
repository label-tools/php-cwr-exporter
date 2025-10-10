<?php

namespace LabelTools\PhpCwrExporter\Records\Control;

use LabelTools\PhpCwrExporter\Fields\HasGroupId;
use LabelTools\PhpCwrExporter\Records\Record;

class GrtRecord extends Record
{
    use HasGroupId;

    protected static string $recordType = 'GRT'; // Always "GRT" *A{3}
    protected string $stringFormat = "%-3s%05d%08d%08d";

    protected const IDX_GROUP_ID = 2;
    protected const IDX_TRANSACTION_COUNT = 3;
    protected const IDX_RECORD_COUNT = 4;

    public function __construct(
        ?int $groupId = null,
        ?int $transactionCount = null,
        ?int $recordCount = null
    ) {
        parent::__construct();
        $this->setGroupId($groupId ?? 1);
        $this->setTransactionCount($transactionCount ?? 0);
        $this->setRecordCount($recordCount ?? 0);
    }

    public function setTransactionCount(int $transactionCount): self
    {
        $this->validateCount($transactionCount, 'Transaction Count');
        $this->data[static::IDX_TRANSACTION_COUNT] = $transactionCount;
        return $this;
    }

    public function setRecordCount(int $recordCount): self
    {
        $this->validateCount($recordCount, 'Record Count');
        $this->data[static::IDX_RECORD_COUNT] = $recordCount;
        return $this;
    }

}
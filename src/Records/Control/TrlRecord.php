<?php

namespace LabelTools\PhpCwrExporter\Records\Control;

use LabelTools\PhpCwrExporter\Records\Record;

class TrlRecord extends Record
{
    protected static string $recordType = 'TRL'; // Always "TRL" *A{3}
    protected string $stringFormat = "%-3s%05d%08d%08d";

    protected const IDX_GROUP_COUNT = 2;
    protected const IDX_TRANSACTION_COUNT = 3;
    protected const IDX_RECORD_COUNT = 4;

    public function __construct(
        ?int $groupCount = null,
        ?int $transactionCount = null,
        ?int $recordCount = null
    ) {
        parent::__construct();

        $this->setGroupCount($groupCount ?? 1);
        $this->setTransactionCount($transactionCount ?? 0);
        $this->setRecordCount($recordCount ?? 0);
    }

    public function setGroupCount(int $groupCount): self
    {
        $this->validateCount($groupCount, 'Group Count', 1, 99999);
        return $this->setNumeric(static::getIdxFromString('IDX_GROUP_COUNT'), $groupCount, 'Group Count');
    }

    public function setTransactionCount(int $transactionCount): self
    {
        $this->validateCount($transactionCount, 'Transaction Count');
        return $this->setNumeric(static::getIdxFromString('IDX_TRANSACTION_COUNT'), $transactionCount, 'Transaction Count');
    }

    public function setRecordCount(int $recordCount): self
    {
        $this->validateCount($recordCount, 'Record Count');
        return $this->setNumeric(static::getIdxFromString('IDX_RECORD_COUNT'), $recordCount, 'Record Count');
    }
}

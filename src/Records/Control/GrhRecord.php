<?php

namespace LabelTools\PhpCwrExporter\Records\Control;

use LabelTools\PhpCwrExporter\Enums\TransactionType;
use LabelTools\PhpCwrExporter\Fields\HasGroupId;
use LabelTools\PhpCwrExporter\Records\Record;

class GrhRecord extends Record
{
    use HasGroupId;

    protected static string $recordType = 'GRH'; // Always "GRH" *A{3}
    protected string $stringFormat = "%-3s%-3s%05d";

    protected const IDX_TRANSACTION_TYPE= 2;
    protected const IDX_GROUP_ID = 3;

    public function __construct(
        string|TransactionType $transactionType,
        ?int $groupId = null,
    ) {
        parent::__construct();

        $this->setTransactionType($transactionType);
        $this->setGroupId($groupId ?? 1);
    }

    public function setTransactionType(string|TransactionType $transactionType): self
    {
        return $this->setEnumValue(static::IDX_TRANSACTION_TYPE, TransactionType::class, $transactionType);
    }

}
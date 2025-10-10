<?php

namespace LabelTools\PhpCwrExporter\Version\V21\Records\Control;

use LabelTools\PhpCwrExporter\Enums\TransactionType;
use LabelTools\PhpCwrExporter\Records\Control\GrhRecord as BaseGrhRecord;

class GrhRecord extends BaseGrhRecord
{
    protected static string $versionNumber = '02.10'; // CWR version number (fixed: "02.10") *A{$}
    protected static string $submissionDistributionType = ''; //Set to blank - Not used for CWR

    protected const IDX_VERSION_NUMBER= 4;
    protected const IDX_BATCH_REQUEST = 5;
    protected const IDX_SUBMISSION_DISTRO_TYPE = 6;

    public function __construct(
        string|TransactionType $transactionType,
        ?int $groupId = null,
        ?int $batchRequest = null
    ) {
        parent::__construct($transactionType, $groupId);

        // Initialize character set
        $this->stringFormat .= "%-5s%-10s%-2s";

        $this->data[self::IDX_VERSION_NUMBER] = static::$versionNumber;
        $this->data[self::IDX_SUBMISSION_DISTRO_TYPE] = static::$submissionDistributionType;

        $this->setBatchRequest($batchRequest);
    }

    public function setBatchRequest(?int $batchRequest): self
    {
        if (!is_null($batchRequest) && $batchRequest < 0) {
            throw new \InvalidArgumentException("Batch request must be a non-negative integer.");
        }
        $this->data[self::IDX_BATCH_REQUEST] = isset($batchRequest) ? str_pad($batchRequest, 10, '0', STR_PAD_LEFT) : '';
        return $this;
    }
}
<?php

namespace LabelTools\PhpCwrExporter\Records\Control;

use LabelTools\PhpCwrExporter\Enums\TransactionType;
use LabelTools\PhpCwrExporter\Records\Record;

class GrhRecord extends Record
{

    protected static string $recordType = 'GRH'; // Always "GRH" *A{3}
    protected string $stringFormat = "%-3s%-3s%05d";

    private const INDEX_TRANSACTION_TYPE= 2;
    private const INDEX_GROUP_ID = 3;

    public function __construct(
        null|string|TransactionType $transactionType = null,
        ?int $groupId = null,
    ) {
        parent::__construct();

        $this->setTransactionType($transactionType ?? '');
        $this->setGroupId($groupId ?? 1);
    }

    public function setTransactionType(string|TransactionType $transactionType): self
    {
        return $this->setList(self::INDEX_TRANSACTION_TYPE, TransactionType::class, $transactionType);
    }

    public function setGroupId(int $groupId): self
    {
        $this->validateGroupId($groupId);
        $this->setNumeric(self::INDEX_GROUP_ID, $groupId);
        return $this;
    }

    /**
     * Validates the group ID.
     *
     * @param int $groupId
     * @return void
     */
    private function validateGroupId(int $groupId): void
    {
        if ($groupId < 1 || $groupId > 99999) {
            throw new \InvalidArgumentException("Group ID must start at 1 and increment sequentially. Max sequence is 99999.");
        }

        // Additional rule: Ensure the Group ID is unique within this file
        if (!$this->isGroupIdUnique($groupId)) {
            throw new \InvalidArgumentException("Group ID must be unique within this file.");
        }
    }

    /**
     * Checks if the Group ID is unique within the file.
     * @todo Implement actual logic to track used Group IDs.
     *
     * @param int $groupId
     * @return bool
     */
    private function isGroupIdUnique(int $groupId): bool
    {
        return true;
        // @todo Placeholder logic: Replace with the actual logic to check against previously used Group IDs
        $usedGroupIds = []; // This would come from the file context or a tracking mechanism
        return !in_array($groupId, $usedGroupIds, true);
    }
}
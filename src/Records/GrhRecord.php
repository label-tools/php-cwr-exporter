<?php

namespace LabelTools\PhpCwrExporter\Records;

use LabelTools\PhpCwrExporter\Enums\TransactionType;

class GrhRecord extends Record
{

    protected static string $recordType = 'GRH'; // Always "GRH" *A{3}
    protected string $stringFormat = "%-3s%-3s%05d";

    public function __construct(
        ?string $transactionType = null,
        ?int $groupId = null,
    ) {
        parent::__construct();

        if (!is_null($transactionType)) {
            $this->setTransactionType($transactionType);
        }
        if (!is_null($groupId)) {
            $this->setGroupId($groupId);
        }
    }

    public function setTransactionType(string $transactionType): self
    {
        $this->validateTransactionType($transactionType);
        $this->data[2] = $transactionType;
        return $this;
    }

    public function setGroupId(int $groupId): self
    {
        $this->validateGroupId($groupId);
        $this->data[3] = $groupId;
        return $this;
    }

    private function validateTransactionType(string $transactionType): void
    {
        try {
            TransactionType::from($transactionType);
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException("Transaction Type must match an entry in the Transaction Type table.");
        }
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
        // Placeholder logic: Replace with the actual logic to check against previously used Group IDs
        $usedGroupIds = []; // This would come from the file context or a tracking mechanism
        return !in_array($groupId, $usedGroupIds, true);
    }
}
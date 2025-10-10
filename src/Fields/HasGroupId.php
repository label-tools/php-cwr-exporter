<?php

namespace LabelTools\PhpCwrExporter\Fields;

trait HasGroupId
{

    public function setGroupId(int $groupId): self
    {
        $this->validateGroupId($groupId);
        return $this->setNumeric(static::getIdxFromString('IDX_GROUP_ID'), $groupId, 'Group ID');
    }

    protected function validateGroupId(int $groupId): void
    {
        if ($groupId < 1 || $groupId > 99999) {
            throw new \InvalidArgumentException("Group ID must be between 1 and 99999.");
        }

        // Additional rule: Ensure the Group ID is unique within this file
        if (! $this->isGroupIdUnique($groupId)) {
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
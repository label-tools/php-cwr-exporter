<?php

namespace LabelTools\PhpCwrExporter\Fields;

trait HasCollectionShare
{
    protected function setCollectionShare(int $index, null|int $share, $min = 0, $max = 10000, bool $isRequired = true): self
    {
        $share ??= 0;
        if ($share < $min || $share > $max) {
            throw new \InvalidArgumentException("Shares must be between $min and $max.");
        }

        $this->data[$index] = $share;
        return $this;
    }


     /**
     * @param int $share 0–10000 (0.00%–100.00%)
     */
    public function setPrCollectionShare(int $share): static
    {
        $const = get_called_class() . '::IDX_PR_COLLECTION_SHARE';
        if (!defined($const)) {
            throw new \LogicException(static::class . ' must define constant IDX_PR_COLLECTION_SHARE.');
        }
        return $this->setCollectionShare(constant($const), $share, 0, 5000);
    }

    public function setMrCollectionShare(int $share): self
    {
        $const = get_called_class() . '::IDX_MR_COLLECTION_SHARE';
        if (!defined($const)) {
            throw new \LogicException(static::class . ' must define constant IDX_MR_COLLECTION_SHARE.');
        }
        return $this->setCollectionShare(constant($const), $share, 0, 10000);
    }

    public function setSrCollectionShare(int $share): self
    {
        $const = get_called_class() . '::IDX_SR_COLLECTION_SHARE';
        if (!defined($const)) {
            throw new \LogicException(static::class . ' must define constant IDX_SR_COLLECTION_SHARE.');
        }
        return $this->setCollectionShare(constant($const), $share, 0, 10000);
    }
}
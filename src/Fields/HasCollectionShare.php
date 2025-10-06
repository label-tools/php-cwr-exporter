<?php

namespace LabelTools\PhpCwrExporter\Fields;

trait HasCollectionShare
{
     /**
     * @param int $share 0–10000 (0.00%–100.00%)
     */
    public function setPrCollectionShare(int|float $share): static
    {
        $this->data[static::IDX_PR_COLLECTION_SHARE] = $this->normalizeShare(
            $share,
            50,
            'PR Collection Share'
        );
        return $this;
    }

    public function setMrCollectionShare(int|float $share): self
    {
        $this->data[static::IDX_MR_COLLECTION_SHARE] = $this->normalizeShare(
            $share,
            100,
            'MR Collection Share'
        );
        return $this;
    }

    public function setSrCollectionShare(int|float $share): self
    {
        $this->data[static::IDX_SR_COLLECTION_SHARE] = $this->normalizeShare(
            $share,
            100,
            'SR Collection Share'
        );
        return $this;
    }
}
<?php

namespace LabelTools\PhpCwrExporter\Fields;

trait HasOwnershipShare
{
    public function setPrOwnershipShare(null|int|float $share): self
    {
        $this->data[static::IDX_PR_OWNERSHIP_SHARE] = $this->normalizeShare(
            $share,
            maxPercent: 50,
            fieldName: 'PR Ownership Share'
        );
        return $this;
    }

    public function setMrOwnershipShare(null|int|float $share): self
    {
        $this->data[static::IDX_MR_OWNERSHIP_SHARE] = $this->normalizeShare(
            $share,
            maxPercent:100,
            fieldName: 'MR Ownership Share'
        );
        return $this;
    }

    public function setSrOwnershipShare(null|int|float $share): self
    {
        $this->data[static::IDX_SR_OWNERSHIP_SHARE] = $this->normalizeShare(
            $share,
            100,
            fieldName: 'SR Ownership Share'
        );
        return $this;
    }
}
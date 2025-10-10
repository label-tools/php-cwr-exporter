<?php

namespace LabelTools\PhpCwrExporter\Fields;

trait HasOwnershipShare
{
    public function setPrOwnershipShare(null|int|float $share): self
    {
        $fieldName = 'PR Ownership Share';
        $share = $this->normalizeShare($share, 50, $fieldName);
        return $this->setNumeric(static::getIdxFromString('IDX_PR_OWNERSHIP_SHARE'), $share, $fieldName);
    }

    public function setMrOwnershipShare(null|int|float $share): self
    {
        $fieldName = 'MR Ownership Share';
        $share = $this->normalizeShare($share, 100, $fieldName);
        return $this->setNumeric(static::getIdxFromString('IDX_MR_OWNERSHIP_SHARE'), $share, $fieldName);
    }

    public function setSrOwnershipShare(null|int|float $share): self
    {
        $fieldName = 'SR Ownership Share';
        $share = $this->normalizeShare($share, 100, $fieldName);
        return $this->setNumeric(static::getIdxFromString('IDX_SR_OWNERSHIP_SHARE'), $share, $fieldName);
    }
}
<?php

namespace LabelTools\PhpCwrExporter\Fields;

trait HasCollectionShare
{

    public function setPrCollectionShare(int|float $share): static
    {
        $field = 'PR Collection Share';
        $share = $this->normalizeShare($share, 50, $field);
        return $this->setNumeric(static::getIdxFromString('IDX_PR_COLLECTION_SHARE'), $share, $field);
    }

    public function setMrCollectionShare(int|float $share): self
    {
        $field = 'MR Collection Share';
        $share = $this->normalizeShare($share, 100, $field);
        return $this->setNumeric(static::getIdxFromString('IDX_MR_COLLECTION_SHARE'), $share, $field);
    }

    public function setSrCollectionShare(int|float $share): self
    {
        $field = 'SR Collection Share';
        $share = $this->normalizeShare($share, 100, $field);
        return $this->setNumeric(static::getIdxFromString('IDX_SR_COLLECTION_SHARE'), $share, $field);
    }
}
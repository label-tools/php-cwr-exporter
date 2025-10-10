<?php

namespace LabelTools\PhpCwrExporter\Fields;

trait HasInterestedPartyNumber
{
    public function setInterestedPartyNumber(null|string $partyNumber, bool $isRequired = true): self
    {
        if ($isRequired && (empty($partyNumber) || strlen($partyNumber) > 9)) {
            throw new \InvalidArgumentException('Interested Party Number must be non-empty and at most 9 characters.');
        }
        return $this->setAlphaNumeric(static::getIdxFromString('IDX_INTERESTED_PARTY_NUMBER'), $partyNumber, 'Interested Party Number');
    }
}
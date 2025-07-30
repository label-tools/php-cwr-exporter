<?php

namespace LabelTools\PhpCwrExporter\Fields;

trait HasInterestedPartyNumber
{
    public function setInterestedPartyNumber(null|string $partyNumber, bool $isRequired = true): self
    {
        $const = get_called_class() . '::IDX_INTERESTED_PARTY_NUMBER';

        if (!defined($const)) {
            throw new \LogicException(static::class . ' must define constant IDX_INTERESTED_PARTY_NUMBER.');
        }

        if ($isRequired && (empty($partyNumber) || strlen($partyNumber) > 9)) {
            throw new \InvalidArgumentException('Interested Party Number must be non-empty and at most 9 characters.');
        }

        $this->data[constant($const)] = $partyNumber;
        return $this;
    }
}
<?php

namespace LabelTools\PhpCwrExporter\Fields;

use LabelTools\PhpCwrExporter\Enums\LanguageCode;

trait HasSequenceNumber
{

    public function setSequenceNumber(null|int $sequenceNumber): self
    {
        $sequenceNumber ??= 1;
        if ($sequenceNumber < 1 || $sequenceNumber > 999) {
            throw new \InvalidArgumentException('Sequence number must be greater than 0 and less than or equal to 999.');
        }
        $this->data[$this->getSequenceNumberIndex()] = $sequenceNumber;
        return $this;
    }

    abstract protected function getSequenceNumberIndex(): int;
}
<?php

namespace LabelTools\PhpCwrExporter\Version\V21\Records;

use LabelTools\PhpCwrExporter\Records\HdrRecord as RecordsHdrRecord;

class HdrRecord extends RecordsHdrRecord
{
    public ?string $characterSet; // optional L {15}

    private const INDEX_CHARACTER_SET = 9;

    public function __construct(
        string $senderType,
        string $senderId,
        string $senderName,
        ?string $creationDate = null,
        ?string $creationTime = null,
        ?string $transmissionDate = null,
        ?string $characterSet = null
    ) {
        parent::__construct($senderType, $senderId, $senderName, $creationDate, $creationTime, $transmissionDate);

        // Initialize character set
        $this->stringFormat .= "%-15s";

        $this->setCharacterSet($characterSet);
    }

    public function setCharacterSet(?string $characterSet): self
    {
        $this->validateCharacterSet($characterSet);
        $this->characterSet = $characterSet ?? '';
        $this->data[self::INDEX_CHARACTER_SET] = $this->characterSet;
        return $this;
    }

    private function validateCharacterSet(?string $characterSet): void
    {
        if(empty($characterSet)) {
            return;
        }
        $validCharacterSets = ['ASCII', 'UTF-8', 'ISO-8859-1'];
        if (!in_array($characterSet, $validCharacterSets, true)) {
            throw new \InvalidArgumentException("Character Set must be one of 'ASCII', 'UTF-8', 'ISO-8859-1'.");
        }
    }
}
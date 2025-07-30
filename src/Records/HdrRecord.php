<?php

namespace LabelTools\PhpCwrExporter\Records;

use LabelTools\PhpCwrExporter\Enums\SenderType;

class HdrRecord extends Record
{
    protected static string $recordType = 'HDR'; // Always "HDR" - * A {3}
    protected static string $ediVersion = '01.10'; // Fixed version number for this standard * A {5}

    // Format: RecordType(3) + SenderType(2) + SenderID(9) + SenderName(45) + EdiVersion(5) + CreationDate(8) + CreationTime(6) + TransmissionDate(8)
    protected string $stringFormat = "%-3s%-2s%-9s%-45s%-5s%-8s%-6s%-8s";

    private const INDEX_SENDER_TYPE = 2;
    private const INDEX_SENDER_ID = 3;
    private const INDEX_SENDER_NAME = 4;
    private const INDEX_EDI_VERSION = 5;
    private const INDEX_CREATION_DATE = 6;
    private const INDEX_CREATION_TIME = 7;
    private const INDEX_TRANSMISSION_DATE = 8;

    protected string $senderType;
    protected string $senderId;

    public function __construct(null|SenderType|string $senderType = null, ?string $senderId = null, ?string $senderName = null, ?string $creationDate = null, ?string $creationTime = null, ?string $transmissionDate = null)
    {
        parent::__construct(); //ALWAYS CALL PARENT CONSTRUCTOR FIRST

        if (!empty($senderType) && !empty($senderId)) {
            $this->setSenderTypeAndId($senderType, $senderId);
        }
        if (!empty($senderName)) {
            $this->setSenderName($senderName);
        }
        $this->setCreationDate($creationDate);
        $this->setCreationTime($creationTime);
        $this->setTransmissionDate($transmissionDate);

        $this->data[self::INDEX_EDI_VERSION] = static::$ediVersion;
    }

    protected function validateBeforeToString(): void
    {

    }

    public function setSenderTypeAndId(string|SenderType $senderType, string $senderId): self
    {
        try {
            $senderTypeEnum = $senderType instanceof SenderType ? $senderType : SenderType::from($senderType);
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException("Invalid sender type");
        }

        $this->validateSenderId($senderTypeEnum, $senderId);
        $this->senderType = $this->resolveSenderType($senderTypeEnum, $senderId);
        $this->senderId = $this->resolveSenderId($senderTypeEnum, $senderId);
        $this->data[self::INDEX_SENDER_TYPE] = $this->senderType;
        $this->data[self::INDEX_SENDER_ID] = $this->senderId;
        return $this;
    }

    public function setSenderName(?string $senderName): self
    {
        $this->validateSenderName($senderName);
        $this->data[self::INDEX_SENDER_NAME] = $senderName;
        return $this;
    }

    public function setCreationDate(?string $creationDate): self
    {
        $creationDate = $this->defaultDate($creationDate, 'Ymd');
        $this->validateDate($creationDate, 'Creation Date');
        $this->data[self::INDEX_CREATION_DATE] = $creationDate;
        return $this;
    }

    public function setCreationTime(?string $creationTime): self
    {
        $creationTime = $this->defaultDate($creationTime, 'His');
        $this->data[self::INDEX_CREATION_TIME] = $creationTime;
        return $this;
    }

    public function setTransmissionDate(?string $transmissionDate): self
    {
        $transmissionDate = $this->defaultDate($transmissionDate, 'Ymd');
        $this->validateDate($transmissionDate, 'Transmission Date');
        $this->data[self::INDEX_TRANSMISSION_DATE] = $transmissionDate;
        return $this;
    }

    private function validateSenderId(?SenderType $senderType, string $senderId): void
    {
        if ($this->requiresWorkaround($senderType, $senderId)) {
            // Validate first 2 and remaining 9 digits for senderId as a workaround
            $leadingDigits = substr($senderId, 0, 2);
            $remainingDigits = substr($senderId, 2);

            if (!ctype_digit($leadingDigits) || strlen($leadingDigits) !== 2) {
                throw new \InvalidArgumentException("The first 2 digits of Sender ID must be numeric and exactly 2 digits.");
            }

            if (!ctype_digit($remainingDigits) || strlen($remainingDigits) !== 9) {
                throw new \InvalidArgumentException("The remaining 9 digits of Sender ID must be numeric and exactly 9 digits.");
            }
        } elseif (!ctype_digit($senderId) || strlen($senderId) !== 9) {
            throw new \InvalidArgumentException("Sender ID must be numeric and exactly 9 digits when Sender Type is not PB, AA, or WR.");
        }
    }

    private function validateSenderName(string $senderName): void
    {
        if (strlen($senderName) > 45) {
            throw new \InvalidArgumentException("Sender Name must not exceed 45 characters.");
        }
    }

    private function validateDate(string $date, string $fieldName): void
    {
        $format = 'Ymd';
        $d = \DateTime::createFromFormat($format, $date);
        if (!$d || $d->format($format) !== $date) {
            throw new \InvalidArgumentException("$fieldName must be a valid date in 'YYYYMMDD' format.");
        }
    }

    private function resolveSenderType(SenderType $senderType, string $senderId): string
    {
        // Extract the first 2 digits from senderId if it's an IPI number > 9 digits
       return (strlen($senderId) > 9 && strlen($senderId) <= 11 && $senderType->isRegular()) ? substr($senderId, 0, 2) : $senderType->value;
    }

    private function resolveSenderId(SenderType $senderType, string $senderId): int
    {
        return (int) ((strlen($senderId) > 9 && strlen($senderId) <= 11 && $senderType->isRegular()) ? substr($senderId, 2) : $senderId);
    }

    private function requiresWorkaround(SenderType $senderType, string $senderId): bool
    {

        return strlen($senderId) > 9 && strlen($senderId) <= 11 && $senderType->isRegular();
    }
}
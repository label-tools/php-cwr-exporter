<?php

namespace LabelTools\PhpCwrExporter\Records\Control;

use DateTime;
use LabelTools\PhpCwrExporter\Enums\SenderType;
use LabelTools\PhpCwrExporter\Records\Record;

class HdrRecord extends Record
{
    protected static string $recordType = 'HDR'; // Always "HDR" - * A {3}
    protected static string $ediVersion = '01.10'; // Fixed version number for this standard * A {5}

    protected const BASE_FIELD_MAP = [
        'sender_type' => [4, 2],
        'sender_id' => [6, 9],
        'sender_name' => [15, 45],
        'edi_version' => [60, 5],
        'creation_date' => [65, 8],
        'creation_time' => [73, 6],
        'transmission_date' => [79, 8],
    ];

    // Format: RecordType(3) + SenderType(2) + SenderID(9) + SenderName(45) + EdiVersion(5) + CreationDate(8) + CreationTime(6) + TransmissionDate(8)
    protected string $stringFormat = "%-3s%-2s%-9s%-45s%-5s%-8s%-6s%-8s";

    protected const IDX_SENDER_TYPE = 2;
    protected const IDX_SENDER_ID = 3;
    protected const IDX_SENDER_NAME = 4;
    protected const IDX_EDI_VERSION = 5;
    protected const IDX_CREATION_DATE = 6;
    protected const IDX_CREATION_TIME = 7;
    protected const IDX_TRANSMISSION_DATE = 8;

    protected string $senderType;
    protected string $senderId;

    public function __construct(
        SenderType|string $senderType,
        string $senderId,
        string $senderName = '',
        null|string|DateTime $creationDate = null,
        null|string|DateTime $creationTime = null,
        null|string|DateTime $transmissionDate = null,
    ){
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
        $this->setAlphaNumeric(static::IDX_EDI_VERSION, static::$ediVersion);
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
        $this->setAlphaNumeric(static::IDX_SENDER_TYPE, $this->senderType);
        $this->setNumeric(static::IDX_SENDER_ID, $this->senderId);
        return $this;
    }

    public function setSenderName(?string $senderName): self
    {
        $this->validateSenderName($senderName);
        $this->setAlphaNumeric(static::IDX_SENDER_NAME, $senderName);
        return $this;
    }

    public function setCreationDate(null|string|DateTime $creationDate): self
    {
        $this->setDate(static::IDX_CREATION_DATE, $creationDate, true, 'Creation Date');
        return $this;
    }

    public function setCreationTime(null|string|DateTime $creationTime): self
    {
        $this->setTime(static::IDX_CREATION_TIME, $creationTime, true, 'Creation Time');
        return $this;
    }

    public function setTransmissionDate(null|string|DateTime $transmissionDate): self
    {
        $this->setDate(static::IDX_TRANSMISSION_DATE, $transmissionDate, true, 'Transmission Date');
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

    protected static function parseBaseLine(string $line): array
    {
        return static::parseFixedWidth($line, static::BASE_FIELD_MAP);
    }
}

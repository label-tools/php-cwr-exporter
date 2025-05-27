<?php

namespace LabelTools\PhpCwrExporter\Records;


class HdrRecord extends Record
{
    protected static string $recordType = 'HDR'; // Always "HDR" - * A {3}
    protected static string $ediVersion = '01.10'; // Fixed version number for this standard * A {5}

    protected string $stringFormat = "%-3s%-2s%-9s%-45s%-5s%-8s%-6s%-8s";

    protected string $senderType;
    protected int $senderId;

    public function __construct(?string $senderType, ?string $senderId, ?string $senderName, ?string $creationDate = null, ?string $creationTime = null, ?string $transmissionDate = null)
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

        $this->data[5] = static::$ediVersion;
    }

    public function setSenderTypeAndId(string $senderType, int $senderId): self
    {
        $this->validateSenderType($senderType, $senderId);
        $this->validateSenderId($senderType, $senderId);
        $this->senderType = $this->resolveSenderType($senderType, $senderId);
        $this->senderId = $this->resolveSenderId($senderType, $senderId);
        $this->data[2] = $this->senderType;
        $this->data[3] = $this->senderId;
        return $this;
    }

    public function setSenderName(?string $senderName): self
    {
        $this->validateSenderName($senderName);
        $this->data[4] = $senderName;
        return $this;
    }

    public function setCreationDate(?string $creationDate): self
    {
        $currentDateTime = new \DateTime();
        $creationDate = $creationDate ?? $currentDateTime->format('Ymd');
        $this->validateDate($creationDate, 'Creation Date');
        $this->data[6] = $creationDate;
        return $this;
    }

    public function setCreationTime(?string $creationTime): self
    {
        $currentDateTime = new \DateTime();
        $creationTime = $creationTime ?? $currentDateTime->format('His');
        $this->data[7] = $creationTime;
        return $this;
    }

    public function setTransmissionDate(?string $transmissionDate): self
    {
        $currentDateTime = new \DateTime();
        $transmissionDate = $transmissionDate ?? $currentDateTime->format('Ymd');
        $this->validateDate($transmissionDate, 'Transmission Date');
        $this->data[8] = $transmissionDate;
        return $this;
    }

    private function validateSenderType(string $senderType, string $senderId): void
    {
        if ($this->requiresWorkaround($senderType, $senderId)) {
            // Allow senderType to be treated as the first 2 digits of senderId in this case
            if (!ctype_digit(substr($senderId, 0, 2))) {
                throw new \InvalidArgumentException("The first 2 characters of Sender ID must be numeric when Sender Type is PB, AA, or WR.");
            }
        } else {
            $validTypes = ['PB', 'SO', 'AA', 'WR'];
            if (!in_array($senderType, $validTypes, true)) {
                throw new \InvalidArgumentException("Sender Type must be one of 'PB', 'SO', 'AA', 'WR'.");
            }
        }
    }

    private function validateSenderId(string $senderType, string $senderId): void
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

    private function resolveSenderType(string $senderType, string $senderId): string
    {
        // Extract the first 2 digits from senderId if it's an IPI number > 9 digits
        return (strlen($senderId) > 9 && strlen($senderId) <= 11 && in_array($senderType, ['PB', 'AA', 'WR'], true))
            ? substr($senderId, 0, 2)
            : $senderType;
    }

    private function resolveSenderId(string $senderType, string $senderId): int
    {
        // Extract the last 9 digits from senderId if it's an IPI number > 9 digits
        return (strlen($senderId) > 9 && strlen($senderId) <= 11 && in_array($senderType, ['PB', 'AA', 'WR'], true))
            ? substr($senderId, 2)
            : $senderId;
    }

    private function requiresWorkaround(string $senderType, string $senderId): bool
    {
        return strlen($senderId) > 9 && strlen($senderId) <= 11 && in_array($senderType, ['PB', 'AA', 'WR'], true);
    }

}
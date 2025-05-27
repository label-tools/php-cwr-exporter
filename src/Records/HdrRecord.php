<?php

namespace LabelTools\PhpCwrExporter\Records;

use LabelTools\PhpCwrExporter\Contracts\RecordInterface;

class HdrRecord implements RecordInterface
{
    public string $recordType = 'HDR'; // Always "HDR" - * A {3}
    public string $senderType; // PB, SO, AA, WR (or first 2 digits of CWR Sender ID) * A {2}
    public string $senderId; // Remaining 9 digits of CWR Sender ID * N {9}
    public string $senderName; // Name of the sender * A {45}
    public string $ediVersion = '01.10'; // Fixed version number for this standard * A {5}
    public string $creationDate; // File creation date (YYYYMMDD) * D {8}
    public string $creationTime; // File creation time (HHMMSS) * T {6}
    public string $transmissionDate; // File transmission date (YYYYMMDD) * D {8}

    protected string $stringFormat = "%-3s%-2s%-9s%-45s%-5s%-8s%-6s%-8s";

    protected array $data = [];

    public function __construct(
        string $senderType,
        string $senderId,
        string $senderName,
        ?string $creationDate = null,
        ?string $creationTime = null,
        ?string $transmissionDate = null,
    ) {
        $currentDateTime = new \DateTime();
        $creationDate = $creationDate ?? $currentDateTime->format('Ymd');
        $creationTime = $creationTime ?? $currentDateTime->format('His');
        $transmissionDate = $transmissionDate ?? $currentDateTime->format('Ymd');

        $this->validateSenderType($senderType, $senderId);
        $this->validateSenderId($senderType, $senderId);
        $this->validateSenderName($senderName);
        $this->validateDate($creationDate, 'Creation Date');
        $this->validateDate($transmissionDate, 'Transmission Date');

        // Assign values only after validation
        $this->senderType = $this->resolveSenderType($senderType, $senderId);
        $this->senderId = $this->resolveSenderId($senderType, $senderId);

        $this->senderName = $senderName;
        $this->creationDate = $creationDate;
        $this->creationTime = $creationTime;
        $this->transmissionDate = $transmissionDate;

        $this->data = [
            'record_type' => $this->recordType,
            'sender_type' => $this->senderType,
            'sender_id' => $this->senderId,
            'sender_name' => $this->senderName,
            'edi_standard' => $this->ediVersion,
            'creation_date' => $this->creationDate,
            'creation_time' => $this->creationTime,
            'transmission_date' => $this->transmissionDate,
        ];
    }

    public function toString(): string
    {
        return sprintf($this->stringFormat, ...array_values($this->data));
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

    private function resolveSenderId(string $senderType, string $senderId): string
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
<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum SenderType: string
{
    case PUBLISHER = 'PB';
    case SOCIETY = 'SO';
    case AGENCY = 'AA';
    case WRITER = 'WR';

    public function getName(): string
    {
        return match ($this) {
            // Override for non-specific category if desired
            default => ucwords(strtolower(str_replace('_', ' ', $this->name))),
        };
    }

    public static function regularTypes(): array
    {
        return [self::PUBLISHER, self::AGENCY, self::WRITER];
    }

    public function isRegular(): bool
    {
        return in_array($this, self::regularTypes(), true);
    }
}
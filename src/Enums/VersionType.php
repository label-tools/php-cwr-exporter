<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum VersionType: string
{
    case MODIFIED_VERSION_OF_A_MUSICAL_WORK = 'MOD';
    case ORIGINAL_WORK = 'ORI';

    public function getName(): string
    {
        return match ($this) {
            // Override for non-specific category if desired
            default => ucwords(strtolower(str_replace('_', ' ', $this->name))),
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::MODIFIED_VERSION_OF_A_MUSICAL_WORK => 'A work resulting from the modification of a musical work.',
            self::ORIGINAL_WORK => 'The first established form of a work.',
        };
    }
}
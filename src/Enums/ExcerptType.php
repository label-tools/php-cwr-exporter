<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum ExcerptType: string
{
    case MOVEMENT = 'MOV';
    case UNSPECIFIED_EXCERPT = 'UEX';
    case NON_EXCERPT = '';

    /**
     * Returns the human-readable name of the excerpt type.
     */
    public function getName(): string
    {
        return match ($this) {
            self::NON_EXCERPT => 'Non-Excerpt',
            default => ucwords(strtolower(str_replace('_', ' ', $this->name))),
        };
    }

    /**
     * Returns a detailed description of the excerpt type.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::MOVEMENT => 'A principal division of a musical work.',
            self::UNSPECIFIED_EXCERPT => 'A work that is known to be an excerpt from another work, however the type of excerpt is unknown.',
            self::NON_EXCERPT => 'Not an excerpt.',
        };
    }
}
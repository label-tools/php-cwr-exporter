<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum TextMusicRelationship: string
{
    case MUSIC = 'MUS';
    case MUSIC_AND_TEXT = 'MTX';
    case TEXT = 'TXT';
    case MUSIC_AND_TEXT_NON_SPECIFIC = 'MTN';

    /**
     * Returns the human-readable name of the text-music relationship.
     */
    public function getName(): string
    {
        return match ($this) {
            // Override for non-specific category if desired
            self::MUSIC_AND_TEXT_NON_SPECIFIC => 'Music and Text (Non-Specific)',
            default => ucwords(strtolower(str_replace('_', ' ', $this->name))),
        };
    }

    /**
     * Returns a detailed description of the text-music relationship.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::MUSIC => 'Music only (as in an instrumental work or a wordless chorus without text e.g. Daphnis and Cloe).',
            self::MUSIC_AND_TEXT => 'Music and text combined (as in a vocal and instrumental work such as the musical JESUS CHRIST SUPERSTAR), where both contributions were specifically created for the respective musical composition with words.',
            self::TEXT => 'Text only.',
            self::MUSIC_AND_TEXT_NON_SPECIFIC => 'Music and text combined (as in a vocal and instrumental work such as an opera), where both contributions were not specifically created for the respective musical composition with words (as in the musical CATS).',
        };
    }
}
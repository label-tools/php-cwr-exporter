<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum LyricAdaptation: string
{
    case NEW_LYRICS = 'NEW';
    case MODIFICATION = 'MOD';
    case NONE = 'NON';
    case ORIGINAL = 'ORI';
    case REPLACEMENT = 'REP';
    case ADDITION = 'ADL';
    case UNSPECIFIED = 'UNS';
    case TRANSLATION = 'TRA';

    /**
     * Returns the human-readable name of the lyric adaptation.
     */
    public function getName(): string
    {
        return match ($this) {
            // Override for non-specific category if desired
            default => ucwords(strtolower(str_replace('_', ' ', $this->name))),
        };
    }

    /**
     * Returns the detailed description of the lyric adaptation.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::NEW_LYRICS   => 'New lyrics added to the existing lyrics.',
            self::MODIFICATION => 'Lyrics modified in the original language.',
            self::NONE         => 'No lyrics have been included in the work.',
            self::ORIGINAL     => 'Lyrics have been used in the original form.',
            self::REPLACEMENT  => 'Lyrics have been totally replaced.',
            self::ADDITION     => 'Lyrics added to a pre-existing instrumental work.',
            self::UNSPECIFIED  => 'Details of the lyric adaptation are not known at this time.',
            self::TRANSLATION  => 'Lyrics translated into another language.',
        };
    }
}
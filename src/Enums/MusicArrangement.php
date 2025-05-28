<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum MusicArrangement: string
{
    case NEW_MUSIC = 'NEW';
    case ARRANGEMENT = 'ARR';
    case ADDITION = 'ADM';
    case UNSPECIFIED_ARRANGEMENT = 'UNS';
    case ORIGINAL = 'ORI';

    /**
     * Returns the human-readable name of the music arrangement.
     */
    public function getName(): string
    {
        return match ($this) {
            self::NEW_MUSIC => 'New',
            self::UNSPECIFIED_ARRANGEMENT => 'Unspecified Arrangement',
            default => ucwords(strtolower(str_replace('_', ' ', $this->name))),
        };
    }

    /**
     * Returns the detailed description of the music arrangement.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::NEW_MUSIC => 'New music added to existing music.',
            self::ARRANGEMENT => 'A version of a work in which musical elements have been modified.',
            self::ADDITION => 'Music added to a pre-existing text.',
            self::UNSPECIFIED_ARRANGEMENT => 'To be used when it is known the work is an arrangement, but no further details are available.',
            self::ORIGINAL => 'Music used in its original form.',
        };
    }
}
<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum MusicalWorkDistributionCategory: string
{
    case JAZZ = 'JAZ';
    case POPULAR = 'POP';
    case SERIOUS = 'SER';
    case UNCLASSIFIED_DISTRIBUTION_CATEGORY = 'UNC';

    /**
     * Returns the human-readable name of the distribution category.
     */
    public function getName(): string
    {
        return match ($this) {
            // Override for non-specific category if desired
            default => ucwords(strtolower(str_replace('_', ' ', $this->name))),
        };
    }

    /**
     * Returns a detailed description of the distribution category.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::JAZZ => "Music originating in black America from the early 20th century, incorporating strands of Euro-American and African music and frequently containing improvisation. For the use of certain societies only. Societies who do not need “Jazz” for distribution purposes should use the code “Pop” instead.",
            self::POPULAR => "The musical mainstream, usually song-based and melody-orientated, created for mass consumption.",
            self::SERIOUS => "Classical or art music.",
            self::UNCLASSIFIED_DISTRIBUTION_CATEGORY => "The catch-all for societies who do not track genres; all works are paid the same regardless of genre.",
        };
    }
}
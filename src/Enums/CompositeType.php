<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum CompositeType: string
{
    case COMPOSITE_OF_SAMPLES = 'COS';
    case MEDLEY = 'MED';
    case POTPOURRI = 'POT';
    case UNSPECIFIED_COMPOSITE  = 'UCO';
    case NON_COMPOSITE = '';

    /**
     * Returns the human-readable name of the composite type.
     */
    public function getName(): string
    {
        return match ($this) {
            self::NON_COMPOSITE => 'Non-Composite',
            default => ucwords(strtolower(str_replace('_', ' ', $this->name))),
        };
    }

    /**
     * Returns a detailed description of the composite type.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::COMPOSITE_OF_SAMPLES => 'A composite work containing new material and one or more samples of pre-existing recorded works.',
            self::MEDLEY => 'A continuous and sequential combination of existing works or excerpts.',
            self::POTPOURRI => 'A composite work with the addition of original material which have been combined to form a new work, that has been published and printed.',
            self::UNSPECIFIED_COMPOSITE => 'Works known to be a composite but where the type of composite is unknown.',
            self::NON_COMPOSITE  => 'Not a composite work.',
        };
    }
}
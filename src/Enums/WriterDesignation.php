<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum WriterDesignation: string
{
    case ADAPTOR = 'AD';
    case ARRANGER = 'AR';
    case AUTHOR  = 'A';
    case COMPOSER = 'C';
    case COMPOSER_AUTHOR = 'CA';
    case SUB_ARRANGER = 'SR';
    case SUB_AUTHOR = 'SA';
    case TRANSLATOR = 'TR';
    case INCOME_PARTICIPANT  = 'PA';

    public function getName(): string
    {
        return match ($this) {
            self::AUTHOR => 'Author, Writer, Author of Lyrics',
            self::COMPOSER => 'Composer, Writer',
            self::COMPOSER_AUTHOR => 'Composer/Author',
            default => ucwords(strtolower(str_replace('_', ' ', $this->name))),
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::ADAPTOR =>
                'The author or one of the authors of an adapted text of a musical work.',
            self::ARRANGER =>
                'A modifier of musical elements of a musical work.',
            self::AUTHOR =>
                'The creator or one of the creators of a text of a musical work.',
            self::COMPOSER =>
                'The creator or one of the creators of the musical elements of a musical work.',
            self::COMPOSER_AUTHOR =>
                'The creator or one of the creators of text and musical elements within a musical work.',
            self::SUB_ARRANGER =>
                'A creator of arrangements authorized by the Sub-Publisher.',
            self::SUB_AUTHOR =>
                'The author of text which substitutes or modifies an existing text of musical work.',
            self::TRANSLATOR =>
                'A modifier of a text in a different language.',
            self::INCOME_PARTICIPANT =>
                'A person that receives royalty payments for a work but is not a copyright owner.',
        };
    }
}
<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum TitleType: string
{
    case ALTERNATIVE_TITLE = 'AT';
    case FIRST_LINE_OF_TEXT = 'TE';
    case FORMAL_TITLE = 'FT';
    case INCORRECT_TITLE = 'IT';
    case ORIGINAL_TITLE = 'OT';
    case ORIGINAL_TITLE_TRANSLATED = 'TT';
    case PART_TITLE = 'PT';
    case RESTRICTED_TITLE = 'RT';
    case EXTRA_SEARCH_TITLE= 'ET';
    case ORIGINAL_TITLE_NATIONAL_CHARACTERS = 'OL';
    case ALTERNATIVE_TITLE_NATIONAL_CHARACTERS = 'AL';

    public function getName(): string
    {
        return match ($this) {
            self::ALTERNATIVE_TITLE => 'Alternative Title',
            self::FIRST_LINE_OF_TEXT => 'First Line of Text',
            self::FORMAL_TITLE => 'Formal Title',
            self::INCORRECT_TITLE => 'Incorrect Title',
            self::ORIGINAL_TITLE => 'Original Title',
            self::ORIGINAL_TITLE_TRANSLATED => 'Original Title Translated',
            self::PART_TITLE => 'Part Title',
            self::RESTRICTED_TITLE  => 'Restricted Title',
            self::EXTRA_SEARCH_TITLE => 'Extra Search Title',
            self::ORIGINAL_TITLE_NATIONAL_CHARACTERS => 'Original Title with National Characters',
            self::ALTERNATIVE_TITLE_NATIONAL_CHARACTERS => 'Alternative Title with National Characters',
        };
    }
}
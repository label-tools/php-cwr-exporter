<?php

namespace LabelTools\PhpCwrExporter\Records\Detail;

use LabelTools\PhpCwrExporter\Enums\TitleType;
use LabelTools\PhpCwrExporter\Enums\LanguageCode;
use LabelTools\PhpCwrExporter\Fields\HasLanguageCode;
use LabelTools\PhpCwrExporter\Records\Record;

class AltRecord extends Record
{
    use HasLanguageCode;

    protected static string $recordType = 'ALT';

    // Indexes into Record::$data for each field
    protected const IDX_ALTERNATE_TITLE = 2; // 60 chars (positions 20–79)
    protected const IDX_TITLE_TYPE = 3; // 2 chars  (80–81)
    protected const IDX_LANGUAGE  = 4; // 2 chars  (82–83)

    // String format: prefix + alternate title + title type + language code
    protected string $stringFormat =
        "%-19s" .  // Record Prefix (19A)
        "%-60s" .  // Alternate Title (60A)
        "%-2s"  .  // Title Type (2A)
        "%-2s";    // Language Code (2A)

    public function __construct(
        string $alternateTitle,
        string|TitleType $titleType,
        null|string|LanguageCode $languageCode = null
    ) {
        parent::__construct();
        $this
            ->setAlternateTitle($alternateTitle)
            ->setTitleType($titleType)
            ->setLanguageCode($languageCode);
    }

    public function setAlternateTitle(string $title): self
    {
        $title = trim(mb_strtoupper($title));
        if ($title === '' || mb_strlen($title) > 60) {
            throw new \InvalidArgumentException('Alternate Title must be 1-60 characters: ' . $title);
        }
        // Defer validation to validateBeforeToString(), so we can check title type
        $this->data[static::IDX_ALTERNATE_TITLE] = $title;
        return $this;
    }

    public function setTitleType(string|TitleType $type): self
    {
        return $this->setEnumValue(static::IDX_TITLE_TYPE, TitleType::class, $type);
    }

    protected function validateBeforeToString(): void
    {
        parent::validateBeforeToString();

        // Alternate Title is always required
        $title = $this->data[static::IDX_ALTERNATE_TITLE] ?? '';
        if (empty($title)) {
            throw new \InvalidArgumentException('ALT: Alternate Title is required.');
        }

        $type = $this->data[static::IDX_TITLE_TYPE] ?? '';
        $lang = $this->getLanguageCode();

        $isNationalCharacters = in_array($type, [
            TitleType::ORIGINAL_TITLE_NATIONAL_CHARACTERS->value,
            TitleType::ALTERNATIVE_TITLE_NATIONAL_CHARACTERS->value,
        ], true);

        // If the title type is one of the “national-characters” variants,
        // then language code must be present
        if (
            $isNationalCharacters
            && empty($lang)
        ) {
            throw new \InvalidArgumentException(
                "ALT: Language Code is required when Title Type is '{$type}'."
            );
        }

        // Now validate the title content based on the type
        if ($isNationalCharacters) {
            $this->data[static::IDX_ALTERNATE_TITLE] = \voku\helper\ASCII::remove_invisible_characters($title);
        } else {
            // Ensure ASCII only
            if (!mb_check_encoding($title, 'ASCII')) {
                throw new \InvalidArgumentException("Alternate Title must be ASCII: {$title}");
            }

            // Reject control characters (NUL..US and DEL). Allow space (0x20) through tilde (0x7E).
            if (!empty($title) && !preg_match('/^[\x20-\x7E]+$/', $title)) {
                throw new \InvalidArgumentException("Alternate Title must contain printable ASCII characters only: {$title}");
            }
        }
    }
}
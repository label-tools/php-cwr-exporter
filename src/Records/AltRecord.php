<?php

namespace LabelTools\PhpCwrExporter\Records;

use LabelTools\PhpCwrExporter\Enums\TitleType;
use LabelTools\PhpCwrExporter\Enums\LanguageCode;
use LabelTools\PhpCwrExporter\Fields\HasLanguageCode;

class AltRecord extends Record
{
    use HasLanguageCode;

    protected static string $recordType = 'ALT';

    // Indexes into Record::$data for each field
    protected const IDX_ALTERNATE_TITLE = 2; // 60 chars (positions 20–79)
    protected const IDX_TITLE_TYPE = 3; // 2 chars  (80–81)
    protected const IDX_LANG  = 4; // 2 chars  (82–83)

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
        $title = trim($title);
        if ($title === '' || strlen($title) > 60) {
            throw new \InvalidArgumentException('Alternate Title must be 1–60 characters.');
        }
        $this->data[self::IDX_ALTERNATE_TITLE] = $title;
        return $this;
    }

    public function setTitleType(string|TitleType $type): self
    {
        // enum guarantees a valid 2-character code
        $titleType = $this->validateTitleType($type);
        $this->data[self::IDX_TITLE_TYPE] = $titleType->value;
        return $this;
    }

    private function validateTitleType(string|TitleType $titleType): TitleType
    {
        try {
            return $titleType instanceof TitleType ? $titleType : TitleType::from($titleType);
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException("Title Type must match an entry in the Title Type table.");
        }
    }

    protected function validateBeforeToString(): void
    {
        // Alternate Title is always required
        if (empty($this->data[self::IDX_ALTERNATE_TITLE])) {
            throw new \InvalidArgumentException('ALT: Alternate Title is required.');
        }

        $type = $this->data[self::IDX_TITLE_TYPE] ?? '';
        $lang = $this->getLanguageCode();

        // If the title type is one of the “national-characters” variants,
        // then language code must be present
        if (
            in_array($type, [
                TitleType::ORIGINAL_TITLE_NATIONAL_CHARACTERS->value,
                TitleType::ALTERNATIVE_TITLE_NATIONAL_CHARACTERS->value,
            ], true)
            && empty($lang)
        ) {
            throw new \InvalidArgumentException(
                "ALT: Language Code is required when Title Type is '{$type}'."
            );
        }
    }

    protected function getLanguageCodeIndex(): int
    {
        return self::IDX_LANG;
    }
}
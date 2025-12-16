<?php
namespace LabelTools\PhpCwrExporter\Definitions;

use BackedEnum;
use LabelTools\PhpCwrExporter\Definitions\RecordingDefinition;
use LabelTools\PhpCwrExporter\Enums\{
    TitleType,
    LanguageCode,
    MusicalWorkDistributionCategory,
    VersionType
};

class WorkDefinition
{
    public function __construct(
        public readonly string $submitterWorkNumber,
        public readonly string $title,
        public readonly TitleType|string $titleType,
        public readonly null|LanguageCode|string $language,
        public readonly MusicalWorkDistributionCategory|string $distributionCategory,
        public readonly VersionType|string $versionType,
        public readonly ?string $iswc = null,
        public readonly ?string $copyrightDate = null,
        public readonly ?string $copyrightNumber = null,
        public readonly ?string $distribution_category = null,
        public readonly ?string $duration = null,
        public readonly bool $recorded = false,
        public readonly string $textMusicRelationship = '',
        public readonly array $recordings = [],
        public readonly array $writers = [],
        public readonly array $publishers = [],
        public readonly array $alternateTitles = []

    ) {
        // field‐level sanity checks:
        if ($this->submitterWorkNumber === '') {
            throw new \InvalidArgumentException('submitterWorkNumber is required.');
        }
        if (strlen($this->title) > 90) {
            throw new \InvalidArgumentException('title cannot exceed 90 characters.');
        }
        // Writers can be empty in some flows (e.g., stubs), but most societies expect at least one writer.
        // Validation for presence of writers, roles, and shares should occur in the exporter/transaction layer.
    }

    public static function fromArray(array $data): self
    {
        return new self(
            submitterWorkNumber: $data['submitter_work_number'] ?? throw new \InvalidArgumentException('submitter_work_number missing'),
            title: $data['title'] ?? throw new \InvalidArgumentException('title missing'),
            titleType: static::getEnumValue($data, TitleType::class, 'title_type'),
            language: static::getEnumValue($data, LanguageCode::class, 'language', isRequired: false),
            distributionCategory: static::getEnumValue($data, MusicalWorkDistributionCategory::class, 'distribution_category'),
            versionType: static::getEnumValue($data, VersionType::class, 'version_type'),
            iswc: $data['iswc'] ?? null,
            copyrightDate: $data['copyright_date'] ?? null,
            copyrightNumber: $data['copyright_number'] ?? null,
            duration: $data['duration'] ?? null,
            recorded: $data['recorded'] ?? false,
            textMusicRelationship: $data['text_music_relationship'] ?? '',
            recordings: isset($data['recordings']) && is_array($data['recordings'])
                ? array_map(fn($recording) => RecordingDefinition::fromArray($recording), $data['recordings'])
                : [],
            writers: static::buildWriters($data),
            publishers: isset($data['publishers']) && is_array($data['publishers'])
                ? array_map(fn($pub) => PublisherDefinition::fromArray($pub), $data['publishers'])
                : [],
            alternateTitles: isset($data['alternate_titles']) && is_array($data['alternate_titles'])
                ? $data['alternate_titles']
                : [],
        );
    }


    protected static function getEnumValue(array $data, string $enumClass, string $key, bool $isRequired = true): BackedEnum|null
    {
        $value = $data[$key] ?? null;

        if ($value instanceof $enumClass) {
            /** @var BackedEnum $value */
            return $value;
        }

        if (empty($value) && $isRequired) {
            throw new \InvalidArgumentException("{$key} must be a non-empty string or {$enumClass} enum instance");
        } elseif (empty($value)) {
            return null; // Allow null for optional fields
        }

        $enumValue = $enumClass::from($value);
        return $enumValue;
    }

    /**
     * @return WriterDefinition[]
     */
    private static function buildWriters(array $data): array
    {
        $writers = isset($data['writers']) && is_array($data['writers'])
            ? array_map(fn($w) => WriterDefinition::fromArray($w), $data['writers'])
            : [];

        // Backwards compatibility: merge any provided other_writers as uncontrolled writers
        if (isset($data['other_writers']) && is_array($data['other_writers'])) {
            $other = array_map(function ($w) {
                $w['controlled'] = false;
                return WriterDefinition::fromArray($w);
            }, $data['other_writers']);
            $writers = array_merge($writers, $other);
        }

        return $writers;
    }
}

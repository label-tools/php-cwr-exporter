<?php
namespace LabelTools\PhpCwrExporter\Definitions;

class RecordingDefinition
{
    public function __construct(
        public readonly ?string $firstReleaseDate = null,
        public readonly ?string $firstReleaseDuration = null,
        public readonly ?string $firstAlbumTitle = null,
        public readonly ?string $firstAlbumLabel = null,
        public readonly ?string $firstReleaseCatalogNumber = null,
        public readonly ?string $firstReleaseEan = null,
        public readonly ?string $firstReleaseIsrc = null,
        public readonly ?string $recordingFormat = null,
        public readonly ?string $recordingTechnique = null,
        public readonly ?string $mediaType = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            firstReleaseDate: $data['first_release_date'] ?? null,
            firstReleaseDuration: $data['first_release_duration'] ?? null,
            firstAlbumTitle: $data['first_album_title'] ?? null,
            firstAlbumLabel: $data['first_album_label'] ?? null,
            firstReleaseCatalogNumber: $data['first_release_catalog_number'] ?? null,
            firstReleaseEan: $data['first_release_ean'] ?? null,
            firstReleaseIsrc: $data['first_release_isrc'] ?? null,
            recordingFormat: $data['recording_format'] ?? null,
            recordingTechnique: $data['recording_technique'] ?? null,
            mediaType: $data['media_type'] ?? null,
        );
    }
}

<?php
namespace LabelTools\PhpCwrExporter\Definitions;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Definitions\PerformingArtistDefinition;

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
        public readonly array $performingArtists = [],
    ) {
        if ($this->performingArtists === [] &&
            $this->firstReleaseDate === null &&
            $this->firstReleaseDuration === null &&
            $this->firstAlbumTitle === null &&
            $this->firstAlbumLabel === null &&
            $this->firstReleaseCatalogNumber === null &&
            $this->firstReleaseEan === null &&
            $this->firstReleaseIsrc === null &&
            $this->recordingFormat === null &&
            $this->recordingTechnique === null &&
            $this->mediaType === null
        ) {
            throw new InvalidArgumentException('RecordingDefinition must contain at least one field.');
        }
    }

    public static function fromArray(array $data): self
    {
        $performers = [];
        foreach ($data['performing_artists'] ?? [] as $artist) {
            $performers[] = PerformingArtistDefinition::fromArray($artist);
        }

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
            performingArtists: $performers,
        );
    }
}

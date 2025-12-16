<?php
namespace LabelTools\PhpCwrExporter\Records\Detail;

use DateTime;
use LabelTools\PhpCwrExporter\Records\Record;

class RecRecord extends Record
{
    protected static string $recordType = 'REC';

    protected string $stringFormat =
        "%-19s" .  // Record Prefix
        "%-8s"  .  // First Release Date
        "%-60s" .  // Constant (blanks)
        "%-6s"  .  // First Release Duration
        "%-5s"  .  // Constant (blanks)
        "%-60s" .  // First Album Title
        "%-60s" .  // First Album Label
        "%-18s" .  // First Release Catalog #
        "%-13s" .  // EAN
        "%-12s" .  // ISRC
        "%-1s"  .  // Recording Format
        "%-1s"  .  // Recording Technique
        "%-3s";    // Media Type

    protected const IDX_RECORD_PREFIX = 1;
    protected const IDX_FIRST_RELEASE_DATE = 2;
    protected const IDX_CONSTANT_BLANK_60 = 3;
    protected const IDX_FIRST_RELEASE_DURATION = 4;
    protected const IDX_CONSTANT_BLANK_5 = 5;
    protected const IDX_FIRST_ALBUM_TITLE = 6;
    protected const IDX_FIRST_ALBUM_LABEL = 7;
    protected const IDX_FIRST_RELEASE_CATALOG_NUMBER = 8;
    protected const IDX_FIRST_RELEASE_EAN = 9;
    protected const IDX_FIRST_RELEASE_ISRC = 10;
    protected const IDX_RECORDING_FORMAT = 11;
    protected const IDX_RECORDING_TECHNIQUE = 12;
    protected const IDX_MEDIA_TYPE = 13;

    public function __construct(
        null|string $firstReleaseDate = null,
        null|string $firstReleaseDuration = null,
        ?string $firstAlbumTitle = '',
        ?string $firstAlbumLabel = '',
        ?string $firstReleaseCatalogNumber = '',
        ?string $firstReleaseEan = '',
        ?string $firstReleaseIsrc = '',
        ?string $recordingFormat = '',
        ?string $recordingTechnique = '',
        ?string $mediaType = ''
    ) {
        parent::__construct();

        $this->data[static::IDX_CONSTANT_BLANK_60] = '';
        $this->data[static::IDX_CONSTANT_BLANK_5] = '';

        $this->setFirstReleaseDate($firstReleaseDate)
            ->setFirstReleaseDuration($firstReleaseDuration)
            ->setFirstAlbumTitle($firstAlbumTitle)
            ->setFirstAlbumLabel($firstAlbumLabel)
            ->setFirstReleaseCatalogNumber($firstReleaseCatalogNumber)
            ->setFirstReleaseEan($firstReleaseEan)
            ->setFirstReleaseIsrc($firstReleaseIsrc)
            ->setRecordingFormat($recordingFormat)
            ->setRecordingTechnique($recordingTechnique)
            ->setMediaType($mediaType);
    }

    public function setFirstReleaseDate(null|string $date): self
    {
        return $this->setDate(static::IDX_FIRST_RELEASE_DATE, $date, false, 'First Release Date');
    }

    public function setFirstReleaseDuration(null|string|DateTime $duration): self
    {
        if ($duration === null || $duration === '') {
            $this->data[static::IDX_FIRST_RELEASE_DURATION] = '';
            return $this;
        }

        if ($duration instanceof DateTime) {
            $value = $duration->format('His');
        } else {
            $value = trim($duration);
            if (!preg_match('/^\d{6}$/', $value)) {
                throw new \InvalidArgumentException("First Release Duration must be a 6-digit HHMMSS string. Given: {$duration}");
            }
        }

        $this->data[static::IDX_FIRST_RELEASE_DURATION] = $value;
        return $this;
    }

    public function setFirstAlbumTitle(?string $title): self
    {
        if ($title === null) {
            $this->data[static::IDX_FIRST_ALBUM_TITLE] = '';
            return $this;
        }

        $value = trim($title);
        if ($value !== '' && mb_strlen($value) > 60) {
            throw new \InvalidArgumentException('First Album Title must be 60 characters or less.');
        }

        return $this->setAlphaNumeric(static::IDX_FIRST_ALBUM_TITLE, $value, 'First Album Title');
    }

    public function setFirstAlbumLabel(?string $label): self
    {
        if ($label === null) {
            $this->data[static::IDX_FIRST_ALBUM_LABEL] = '';
            return $this;
        }

        $value = trim($label);
        if ($value !== '' && mb_strlen($value) > 60) {
            throw new \InvalidArgumentException('First Album Label must be 60 characters or less.');
        }

        return $this->setAlphaNumeric(static::IDX_FIRST_ALBUM_LABEL, $value, 'First Album Label');
    }

    public function setFirstReleaseCatalogNumber(?string $number): self
    {
        if ($number === null) {
            $this->data[static::IDX_FIRST_RELEASE_CATALOG_NUMBER] = '';
            return $this;
        }

        $value = trim($number);
        if ($value !== '' && mb_strlen($value) > 18) {
            throw new \InvalidArgumentException('First Release Catalog Number must be 18 characters or less.');
        }

        return $this->setAlphaNumeric(static::IDX_FIRST_RELEASE_CATALOG_NUMBER, $value, 'First Release Catalog #');
    }

    public function setFirstReleaseEan(?string $ean): self
    {
        if ($ean === null) {
            $this->data[static::IDX_FIRST_RELEASE_EAN] = '';
            return $this;
        }

        $value = trim($ean);
        if ($value !== '' && !preg_match('/^\d{13}$/', $value)) {
            throw new \InvalidArgumentException('EAN must be a 13-digit numeric code.');
        }

        return $this->setAlphaNumeric(static::IDX_FIRST_RELEASE_EAN, $value, 'EAN');
    }

    public function setFirstReleaseIsrc(?string $isrc): self
    {
        if ($isrc === null) {
            $this->data[static::IDX_FIRST_RELEASE_ISRC] = '';
            return $this;
        }

        $value = trim($isrc);
        if ($value !== '' && !preg_match('/^[A-Z]{2}[A-Z0-9]{3}\d{7}$/', $value)) {
            throw new \InvalidArgumentException('ISRC must follow the ISO-3901 format.');
        }

        return $this->setAlphaNumeric(static::IDX_FIRST_RELEASE_ISRC, $value, 'ISRC');
    }

    public function setRecordingFormat(?string $format): self
    {
        if ($format === null) {
            $this->data[static::IDX_RECORDING_FORMAT] = '';
            return $this;
        }

        $value = strtoupper(trim($format));
        if ($value === '') {
            $this->data[static::IDX_RECORDING_FORMAT] = '';
            return $this;
        }

        if (!in_array($value, ['A', 'V'], true)) {
            throw new \InvalidArgumentException('Recording Format must be "A" (audio) or "V" (video).');
        }

        $this->data[static::IDX_RECORDING_FORMAT] = $value;
        return $this;
    }

    public function setRecordingTechnique(?string $technique): self
    {
        if ($technique === null) {
            $this->data[static::IDX_RECORDING_TECHNIQUE] = '';
            return $this;
        }

        $value = strtoupper(trim($technique));
        if ($value === '') {
            $this->data[static::IDX_RECORDING_TECHNIQUE] = '';
            return $this;
        }

        if (!in_array($value, ['A', 'D', 'U'], true)) {
            throw new \InvalidArgumentException('Recording Technique must be "A" (Analogue), "D" (Digital), or "U" (Unknown).');
        }

        $this->data[static::IDX_RECORDING_TECHNIQUE] = $value;
        return $this;
    }

    public function setMediaType(?string $mediaType): self
    {
        if ($mediaType === null) {
            $this->data[static::IDX_MEDIA_TYPE] = '';
            return $this;
        }

        $value = trim($mediaType);
        if ($value !== '' && mb_strlen($value) > 3) {
            throw new \InvalidArgumentException('Media Type must be 3 characters or less.');
        }

        return $this->setAlphaNumeric(static::IDX_MEDIA_TYPE, $value, 'Media Type');
    }

    /**
     * Always keep constants blanks explicitly populated.
     */
    protected function validateBeforeToString(): void
    {
        parent::validateBeforeToString();
        $this->data[static::IDX_CONSTANT_BLANK_60] = $this->data[static::IDX_CONSTANT_BLANK_60] ?? '';
        $this->data[static::IDX_CONSTANT_BLANK_5] = $this->data[static::IDX_CONSTANT_BLANK_5] ?? '';
        $this->ensureHasData();
    }

    private function ensureHasData(): void
    {
        $keys = [
            static::IDX_FIRST_RELEASE_DATE,
            static::IDX_FIRST_RELEASE_DURATION,
            static::IDX_FIRST_ALBUM_TITLE,
            static::IDX_FIRST_ALBUM_LABEL,
            static::IDX_FIRST_RELEASE_CATALOG_NUMBER,
            static::IDX_FIRST_RELEASE_EAN,
            static::IDX_FIRST_RELEASE_ISRC,
            static::IDX_RECORDING_FORMAT,
            static::IDX_RECORDING_TECHNIQUE,
            static::IDX_MEDIA_TYPE,
        ];

        foreach ($keys as $key) {
            if (!empty($this->data[$key] ?? '')) {
                return;
            }
        }

        throw new \InvalidArgumentException('REC record requires at least one data field to be populated.');
    }
}

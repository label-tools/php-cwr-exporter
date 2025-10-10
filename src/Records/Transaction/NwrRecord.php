<?php


namespace LabelTools\PhpCwrExporter\Records\Transaction;

use DateTime;
use LabelTools\PhpCwrExporter\Enums\LanguageCode;
use LabelTools\PhpCwrExporter\Enums\MusicalWorkDistributionCategory;
use LabelTools\PhpCwrExporter\Enums\TextMusicRelationship;
use LabelTools\PhpCwrExporter\Enums\CompositeType;
use LabelTools\PhpCwrExporter\Enums\VersionType;
use LabelTools\PhpCwrExporter\Enums\ExcerptType;
use LabelTools\PhpCwrExporter\Enums\MusicArrangement;
use LabelTools\PhpCwrExporter\Enums\LyricAdaptation;
use LabelTools\PhpCwrExporter\Enums\CwrWorkType;
use LabelTools\PhpCwrExporter\Fields\HasLanguageCode;
use LabelTools\PhpCwrExporter\Records\Record;

class NwrRecord extends Record
{
    use HasLanguageCode;

    protected static string $recordType = 'NWR'; // A{3}
    protected string $stringFormat =
        "%-19s" .  // Record Prefix (19 A)
        "%-60s" .  // Work Title (60 A)
        "%-2s"  .  // Language Code (2 L)
        "%-14s" .  // Submitter Work # (14 A)
        "%-11s" .  // ISWC (11 A)
        "%-8s"  .  // Copyright Date (8 D)
        "%-12s" .  // Copyright Number (12 A)
        "%-3s"  .  // Musical Work Distribution Category (3 L)
        "%-6s"  .  // Duration (6 T)
        "%-1s"  .  // Recorded Indicator (1 F)
        "%-3s"  .  // Text Music Relationship (3 L)
        "%-3s"  .  // Composite Type (3 L)
        "%-3s"  .  // Version Type (3 L)
        "%-3s"  .  // Excerpt Type (3 L)
        "%-3s"  .  // Music Arrangement (3 L)
        "%-3s"  .  // Lyric Adaptation (3 L)
        "%-30s" .  // Contact Name (30 A)
        "%-10s" .  // Contact ID (10 A)
        "%-2s"  .  // CWR Work Type (2 L)
        "%-1s"  .  // Grand Rights Ind (1 B)
        "%03d"  .  // Composite Component Count (3 N)
        "%-8s"  .  // Date of Publication (8 D)
        "%-1s"  .  // Exceptional Clause (1 F)
        "%-25s" .  // Opus Number (25 A)
        "%-25s";   // Catalogue Number (25 A)


    protected const IDX_WORK_TITLE = 2;
    protected const IDX_LANGUAGE = 3;
    protected const IDX_SUBMITTER_WORK_NUMBER = 4;
    protected const IDX_ISWC = 5;
    protected const IDX_COPYRIGHT_DATE = 6;
    protected const IDX_COPYRIGHT_NUMBER = 7;
    protected const IDX_MUSICAL_WORK_DISTRIBUTION_CATEGORY = 8;
    protected const IDX_DURATION = 9;
    protected const IDX_RECORDED_INDICATOR = 10;
    protected const IDX_TEXT_MUSIC_RELATIONSHIP = 11;
    protected const IDX_COMPOSITE_TYPE = 12;
    protected const IDX_VERSION_TYPE = 13;
    protected const IDX_EXCERPT_TYPE = 14;
    protected const IDX_MUSICAL_ARRANGEMENT = 15;
    protected const IDX_LYRIC_ADAPTATION = 16;
    protected const IDX_CONTACT_NAME = 17;
    protected const IDX_CONTACT_ID = 18;
    protected const IDX_CWR_WORK_TYPE = 19;
    protected const IDX_GRAND_RIGHTS_INDICATOR = 20;
    protected const IDX_COMPOSITE_COMPONENT_COUNT = 21;
    protected const IDX_PUBLICATION_DATE = 22;
    protected const IDX_EXCEPTION_CLAUSE = 23;
    protected const IDX_OPUS_NUMBER = 24;
    protected const IDX_CATALOG_NUMBER = 25;

    public function __construct(
        string $workTitle,
        string $submitterWorkNumber,
        MusicalWorkDistributionCategory|string $mwDistributionCategory,
        VersionType|string $versionType,
        LanguageCode|null|string $languageCode = null,
        ?string $iswc = null,
        ?string $copyrightDate = null,
        ?string $copyrightNumber = null,
        ?string $duration = null,
        null|bool|string $recordedIndicator = null,
        ?string $textMusicRelationship = null,
        ?string $compositeType = null,
        ?string $excerptType = null,
        ?string $musicArrangement = null,
        ?string $lyricAdaptation = null,
        ?string $contactName = null,
        ?string $contactId = null,
        ?string $cwrWorkType = null,
        null|bool|string $grandRightsInd = null,
        ?int $compositeComponentCount = 0,
        ?string $publicationDate = null,
        null|bool|string $exceptionalClause = null,
        ?string $opusNumber = null,
        ?string $catalogueNumber = null
    ) {
        parent::__construct();

        // Mandatory fields
        $this->setWorkTitle($workTitle)
             ->setSubmitterWorkNumber($submitterWorkNumber)
             ->setMwDistributionCategory($mwDistributionCategory)
             ->setVersionType($versionType);

        // Optional fields
        $this->setLanguageCode($languageCode ?? '')
             ->setIswc($iswc ?? '')
             ->setCopyrightDate($copyrightDate)
             ->setCopyrightNumber($copyrightNumber)
             ->setDuration($duration)
             ->setRecordedIndicator($recordedIndicator)
             ->setTextMusicRelationship($textMusicRelationship ?? '')
             ->setCompositeType($compositeType ?? '')
             ->setExcerptType($excerptType ?? '')
             ->setMusicArrangement($musicArrangement ?? '')
             ->setLyricAdaptation($lyricAdaptation ?? '')
             ->setContactName($contactName ?? '')
             ->setContactId($contactId ?? '')
             ->setCwrWorkType($cwrWorkType ?? '')
             ->setGrandRightsInd($grandRightsInd)
             ->setCompositeComponentCount($compositeComponentCount)
             ->setPublicationDate($publicationDate)
             ->setExceptionalClause($exceptionalClause)
             ->setOpusNumber($opusNumber ?? '')
             ->setCatalogueNumber($catalogueNumber ?? '');
    }

    protected function validateBeforeToString(): void
    {
        parent::validateBeforeToString();
        $this->validateCompositeComponentCount();
    }

    public function setWorkTitle(string $title): self
    {
        $title = trim($title);
        // TR: mandatory, ASCII only
        if (empty($title)) {
            throw new \InvalidArgumentException("Work Title is required.");
        }
        if (!preg_match('/^[\x20-\x7E]+$/', $title)) {
            throw new \InvalidArgumentException("Work Title contains invalid characters; only ASCII 32-126 allowed: {$title}");
        }
        // TR: max 60 characters
        if (strlen($title) > 60) {
            throw new \InvalidArgumentException("Work Title cannot exceed 60 characters : {$title}");
        }
        return $this->setAlphaNumeric(static::IDX_WORK_TITLE, $title, 'Work Title');
    }


    public function setSubmitterWorkNumber(string $num): self
    {
        // TR: mandatory, unique per publisher
        if ($num === '') {
            throw new \InvalidArgumentException("Submitter Work Number is required.");
        }
        return $this->setAlphaNumeric(static::IDX_SUBMITTER_WORK_NUMBER, $num, 'Submitter Work Number');
    }

    public function setIswc(string $iswc): self
    {
        // If entered, must be valid ISWC, else default spaces
        if ($iswc !== '' && !preg_match('/^T\d{10}$/', $iswc)) {
            throw new \InvalidArgumentException("Invalid ISWC: {$iswc}");
        }
        return $this->setAlphaNumeric(static::IDX_ISWC, $iswc, 'ISWC');
    }

    public function setCopyrightDate(?string $date): self
    {
        return $this->setDate(static::IDX_COPYRIGHT_DATE, $date ?? '', defaultDateOnEmpty:false, fieldName:'Copyright Date');
    }

    public function setCopyrightNumber(?string $num): self
    {
        return $this->setAlphaNumeric(static::IDX_COPYRIGHT_NUMBER, $num ?? '', 'Copyright Number');
    }

    public function setMwDistributionCategory(MusicalWorkDistributionCategory|string $cat): self
    {
        return $this->setEnumValue(static::IDX_MUSICAL_WORK_DISTRIBUTION_CATEGORY, MusicalWorkDistributionCategory::class, $cat, 'Musical Work Distribution Category', isRequired:true);
    }

    public function setDuration(?string $dur): self
    {
        if (empty($this->data[static::IDX_MUSICAL_WORK_DISTRIBUTION_CATEGORY])) {
            throw new \LogicException("usical Work Distribution Category must be set before setting Duration.");
        }
        if (!empty($dur) && !preg_match('/^[0-9]{6}$/', $dur)) {
            throw new \InvalidArgumentException("Duration must be HHMMSS: {$dur}");
        }
        $mwdc = $this->data[static::IDX_MUSICAL_WORK_DISTRIBUTION_CATEGORY] ?? '';
        if ($mwdc === MusicalWorkDistributionCategory::SERIOUS->value && ($dur === '000000' || empty($dur))) {
            throw new \InvalidArgumentException("Duration must be > 000000 when category is SER");
        }

        //@todo Note that some societies may also require
        //duration for works where the Musical Work Distribution
        //Category is equal to JAZ (e.g. BMI).

        $this->data[static::IDX_DURATION] = $dur ?? '';
        return $this;
    }

    public function setRecordedIndicator(null|bool|string $ind): self
    {
        return $this->setFlag(static::IDX_RECORDED_INDICATOR, $ind, 'Recorded Indicator');
    }

    public function setTextMusicRelationship(null|string|TextMusicRelationship $rel): self
    {
        return $this->setEnumValue(static::IDX_TEXT_MUSIC_RELATIONSHIP, TextMusicRelationship::class, $rel, 'Text Music Relationship', isRequired:false);
    }

    public function setCompositeType(null|string|CompositeType $type): self
    {
        return $this->setEnumValue(static::IDX_COMPOSITE_TYPE, CompositeType::class, $type, 'Composite Type', isRequired:false);
    }

    public function setVersionType(VersionType|string $type): self
    {
        return $this->setEnumValue(static::IDX_VERSION_TYPE, VersionType::class, $type, 'Version Type', isRequired:true);
    }

    public function setExcerptType(null|string|ExcerptType $type): self
    {
        return $this->setEnumValue(static::IDX_EXCERPT_TYPE, ExcerptType::class, $type, 'Excerpt Type', isRequired:false);
    }

    public function setMusicArrangement(null|string|MusicArrangement $arr): self
    {
        if (empty($this->data[static::IDX_VERSION_TYPE])) {
            throw new \LogicException("Version Type must be set before setting Music Arrangement.");
        }

        if ($this->data[static::IDX_VERSION_TYPE] === VersionType::MODIFIED_VERSION_OF_A_MUSICAL_WORK->value && $arr === '') {
            throw new \InvalidArgumentException("Music Arrangement is required when Version Type is MOD.");
        }

        return $this->setEnumValue(static::IDX_MUSICAL_ARRANGEMENT, MusicArrangement::class, $arr, 'Music Arrangement', isRequired:false);
    }

    public function setLyricAdaptation(string $lya): self
    {
        if (empty($this->data[static::IDX_VERSION_TYPE])) {
            throw new \LogicException("Version Type must be set before setting Lyric Adaptation.");
        }

        if ($this->data[static::IDX_VERSION_TYPE] === VersionType::MODIFIED_VERSION_OF_A_MUSICAL_WORK->value && $lya === '') {
            throw new \InvalidArgumentException("Lyric Adaptation is required when Version Type is MOD.");
        }

        return $this->setEnumValue(static::IDX_LYRIC_ADAPTATION, LyricAdaptation::class, $lya, 'Lyric Adaptation', isRequired:false);
    }

    public function setContactName(null|string $name): self
    {
        return $this->setAlphaNumeric(static::IDX_CONTACT_NAME, $name, 'Contact Name');
    }

    public function setContactId(null|string $id): self
    {
        return $this->setAlphaNumeric(static::IDX_CONTACT_ID, $id, 'Contact ID');
    }

    public function setCwrWorkType(null|string|CwrWorkType $type): self
    {
        return $this->setEnumValue(static::IDX_CWR_WORK_TYPE, CwrWorkType::class, $type, 'CWR Work Type', isRequired:false);
    }

    public function setGrandRightsInd(null|bool|string $indicator): self
    {
        return $this->setBoolean(static::IDX_GRAND_RIGHTS_INDICATOR, $indicator, 'Grand Rights Indicator');
    }

    public function setCompositeComponentCount(int $count): self
    {
        $fieldName = 'Composite Component Count';
        $this->validateCount($count, $fieldName, 0, 999);
        return $this->setNumeric(static::IDX_COMPOSITE_COMPONENT_COUNT, $count, $fieldName);
    }

    protected function validateCompositeComponentCount(): void
    {
        $compositeType = $this->data[static::IDX_COMPOSITE_TYPE] ?? '';
        $componentCount = $this->data[static::IDX_COMPOSITE_COMPONENT_COUNT] ?? 0;

        if ($compositeType && $componentCount === 0) {
            throw new \InvalidArgumentException("Composite Type is set but Component Count is missing.");
        }

        if (!$compositeType && $componentCount > 0) {
            throw new \InvalidArgumentException("Component Count is set but Composite Type is missing.");
        }
    }

    public function setPublicationDate(null|string|DateTime $date): self
    {
        return $this->setDate(static::IDX_PUBLICATION_DATE, $date, defaultDateOnEmpty:false, fieldName:'Publication Date');
    }

    public function setExceptionalClause(null|bool|string $clause = null): self
    {
        return $this->setFlag(static::IDX_EXCEPTION_CLAUSE, $clause, 'Exceptional Clause');
    }

    public function setOpusNumber(string $opus): self
    {
        return $this->setAlphaNumeric(static::IDX_OPUS_NUMBER, $opus, 'Opus Number');
    }

    public function setCatalogueNumber(string $cat): self
    {
        return $this->setAlphaNumeric(static::IDX_CATALOG_NUMBER, $cat, 'Catalogue Number');
    }
}
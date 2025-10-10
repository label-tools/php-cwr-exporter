<?php


namespace LabelTools\PhpCwrExporter\Records\Transaction;

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


    protected const IDX_TITLE = 2;
    protected const IDX_LANG = 3;
    protected const IDX_SUBMITTER = 4;
    protected const IDX_ISWC = 5;
    protected const IDX_COPYDATE = 6;
    protected const IDX_COPYNUM = 7;
    protected const IDX_MWDC = 8;
    protected const IDX_DURATION = 9;
    protected const IDX_REC = 10;
    protected const IDX_TEXTREL = 11;
    protected const IDX_COMP = 12;
    protected const IDX_VER = 13;
    protected const IDX_EXCERPT = 14;
    protected const IDX_ARRANGE = 15;
    protected const IDX_LYRIC = 16;
    protected const IDX_CONTACT = 17;
    protected const IDX_CONTACTID = 18;
    protected const IDX_WORKTYPE = 19;
    protected const IDX_GRAND = 20;
    protected const IDX_COMPCT = 21;
    protected const IDX_PUBDATE = 22;
    protected const IDX_EXCEPTION = 23;
    protected const IDX_OPUS = 24;
    protected const IDX_CATNUM = 25;

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
        // TR: mandatory, ASCII only
        if ($title === '') {
            throw new \InvalidArgumentException("Work Title is required.");
        }
        if (!preg_match('/^[\x20-\x7E]+$/', $title)) {
            throw new \InvalidArgumentException("Work Title contains invalid characters; only ASCII 32–126 allowed: {$title}");
        }
        // TR: max 60 characters
        if (strlen($title) > 60) {
            throw new \InvalidArgumentException("Work Title cannot exceed 60 characters : {$title}");
        }

        $this->data[self::IDX_TITLE] = $title;
        return $this;
    }


    public function setSubmitterWorkNumber(string $num): self
    {
        // TR: mandatory, unique per publisher
        if ($num === '') {
            throw new \InvalidArgumentException("Submitter Work Number is required.");
        }
        $this->data[self::IDX_SUBMITTER] = $num;
        return $this;
    }

    public function setIswc(string $iswc): self
    {
        // If entered, must be valid ISWC, else default spaces
        if ($iswc !== '' && !preg_match('/^T\d{10}$/', $iswc)) {
            throw new \InvalidArgumentException("Invalid ISWC: {$iswc}");
        }
        $this->data[self::IDX_ISWC] = $iswc;
        return $this;
    }

    public function setCopyrightDate(?string $date): self
    {
        // If entered, must be YYYYMMDD, else default zeros
        if (!empty($date) && !preg_match('/^\d{8}$/', $date)) {
            throw new \InvalidArgumentException("Date must be YYYYMMDD: {$date}");
        }
        $this->data[self::IDX_COPYDATE] = $date ?? '';
        return $this;
    }

    public function setCopyrightNumber(?string $num): self
    {
        $this->data[self::IDX_COPYNUM] = $num ?? '';
        return $this;
    }

    public function setMwDistributionCategory(MusicalWorkDistributionCategory|string $cat): self
    {
        if (empty($cat)) {
            throw new \InvalidArgumentException("Musical Work Distribution Category is required.");
        }
        try {
            $cat = $cat instanceof MusicalWorkDistributionCategory ? $cat : MusicalWorkDistributionCategory::from($cat);
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException("Invalid Musical Work Distribution Category: {$cat}");
        }
        $this->data[self::IDX_MWDC] = $cat->value;
        return $this;
    }

    public function setDuration(?string $dur): self
    {
        if (!empty($dur) && !preg_match('/^[0-9]{6}$/', $dur)) {
            throw new \InvalidArgumentException("Duration must be HHMMSS: {$dur}");
        }
        $mwdc = $this->data[self::IDX_MWDC] ?? '';
        if ($mwdc === MusicalWorkDistributionCategory::SERIOUS->value && ($dur === '000000' || empty($dur))) {
            throw new \InvalidArgumentException("Duration must be > 000000 when category is SER");
        }

        //@todo Note that some societies may also require
        //duration for works where the Musical Work Distribution
        //Category is equal to JAZ (e.g. BMI).

        $this->data[self::IDX_DURATION] = $dur ?? '';
        return $this;
    }

    public function setRecordedIndicator(null|bool|string $ind): self
    {
        $this->data[self::IDX_REC] = $this->flagToValue($ind);
        return $this;
    }

    public function setTextMusicRelationship(string $rel): self
    {
        if ($rel !== '') {
            try {
                $rel = TextMusicRelationship::from($rel)->value;
            } catch (\ValueError $e) {
                throw new \InvalidArgumentException("Invalid Text Music Relationship: {$rel}");
            }
        }
        $this->data[self::IDX_TEXTREL] = $rel;
        return $this;
    }

    public function setCompositeType(string $type): self
    {
        if ($type !== '') {
            try {
                $type = CompositeType::from($type)->value;
            } catch (\ValueError $e) {
                throw new \InvalidArgumentException("Invalid Composite Type: {$type}");
            }
        }
        $this->data[self::IDX_COMP] = $type;
        return $this;
    }

    public function setVersionType(VersionType|string $type): self
    {
        if (empty($type)) {
            throw new \InvalidArgumentException("Version Type is required.");
        }
        try {
             $type = $type instanceof VersionType ? $type : VersionType::from($type);
        } catch (\ValueError $e) {
            throw new \InvalidArgumentException("Invalid Version Type: {$type}");
        }
        $this->data[self::IDX_VER] = $type->value;
        return $this;
    }

    public function setExcerptType(string $type): self
    {
        if ($type !== '') {
            try {
                $type = ExcerptType::from($type)->value;
            } catch (\ValueError $e) {
                throw new \InvalidArgumentException("Invalid Excerpt Type: {$type}");
            }
        }
        $this->data[self::IDX_EXCERPT] = $type;
        return $this;
    }

    public function setMusicArrangement(string $arr): self
    {
        if (empty($this->data[self::IDX_VER])) {
            throw new \LogicException("Version Type must be set before setting Music Arrangement.");
        }

        if ($this->data[self::IDX_VER] === VersionType::MODIFIED_VERSION_OF_A_MUSICAL_WORK->value && $arr === '') {
            throw new \InvalidArgumentException("Music Arrangement is required when Version Type is MOD.");
        }

        if ($arr !== '') {
            try {
                $arr = MusicArrangement::from($arr)->value;
            } catch (\ValueError $e) {
                throw new \InvalidArgumentException("Invalid Music Arrangement: {$arr}");
            }
        }

        $this->data[self::IDX_ARRANGE] = $arr;
        return $this;
    }

    public function setLyricAdaptation(string $lya): self
    {
        if ($lya !== '') {
            try {
                $lya = LyricAdaptation::from($lya)->value;
            } catch (\ValueError $e) {
                throw new \InvalidArgumentException("Invalid Lyric Adaptation: {$lya}");
            }
        }
        $this->data[self::IDX_LYRIC] = $lya;
        return $this;
    }

    public function setContactName(string $name): self
    {
        $this->data[self::IDX_CONTACT] = $name;
        return $this;
    }

    public function setContactId(string $id): self
    {
        $this->data[self::IDX_CONTACTID] = $id;
        return $this;
    }

    public function setCwrWorkType(string $type): self
    {
        if ($type !== '') {
            try {
                $type = CwrWorkType::from($type)->value;
            } catch (\ValueError $e) {
                throw new \InvalidArgumentException("Invalid CWR Work Type: {$type}");
            }
        }
        $this->data[self::IDX_WORKTYPE] = $type;
        return $this;
    }

    public function setGrandRightsInd(null|bool|string $ind): self
    {
        $this->data[self::IDX_GRAND] = $this->boolToValue($ind);
        return $this;
    }

    public function setCompositeComponentCount(int $count): self
    {
        if ($count < 0) {
            throw new \InvalidArgumentException("Composite Component Count must be >= 0.");
        }

        $this->data[self::IDX_COMPCT] = $count;
        return $this;
    }

    protected function validateCompositeComponentCount(): void
    {
        $compositeType = $this->data[self::IDX_COMP] ?? '';
        $componentCount = $this->data[self::IDX_COMPCT] ?? 0;

        if ($compositeType && $componentCount === 0) {
            throw new \InvalidArgumentException("Composite Type is set but Component Count is missing.");
        }

        if (!$compositeType && $componentCount > 0) {
            throw new \InvalidArgumentException("Component Count is set but Composite Type is missing.");
        }
    }

    public function setPublicationDate(?string $date): self
    {
        if (!empty($date) && !preg_match('/^\d{8}$/', $date)) {
            throw new \InvalidArgumentException("Date must be YYYYMMDD: {$date}");
        }
        $this->data[self::IDX_PUBDATE] = $date ?? '';
        return $this;
    }

    public function setExceptionalClause(null|bool|string $clause = null): self
    {

        $this->data[self::IDX_EXCEPTION] = $this->flagToValue($clause);
        return $this;
    }

    public function setOpusNumber(string $opus): self
    {
        $this->data[self::IDX_OPUS] = $opus;
        return $this;
    }

    public function setCatalogueNumber(string $cat): self
    {
        $this->data[self::IDX_CATNUM] = $cat;
        return $this;
    }
}
<?php

namespace LabelTools\PhpCwrExporter\Version\V21\Records\Transaction;

use LabelTools\PhpCwrExporter\Enums\LanguageCode;
use LabelTools\PhpCwrExporter\Enums\MusicalWorkDistributionCategory;
use LabelTools\PhpCwrExporter\Enums\VersionType;
use LabelTools\PhpCwrExporter\Records\Transaction\NwrRecord as BaseNwrRecord;

class NwrRecord extends BaseNwrRecord
{

    protected const IDX_PRIORITY_FLAG = 26;

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
        ?string $catalogueNumber = null,
        null|bool|string $priorityFlag = null
    ) {
        parent::__construct(
            $workTitle,
            $submitterWorkNumber,
            $mwDistributionCategory,
            $versionType,
            $languageCode,
            $iswc,
            $copyrightDate,
            $copyrightNumber,
            $duration,
            $recordedIndicator,
            $textMusicRelationship,
            $compositeType,
            $excerptType,
            $musicArrangement,
            $lyricAdaptation,
            $contactName,
            $contactId,
            $cwrWorkType,
            $grandRightsInd,
            $compositeComponentCount,
            $publicationDate,
            $exceptionalClause,
            $opusNumber,
            $catalogueNumber
        );


        // Initialize character set
        $this->stringFormat .= '%-1s';

        $this->setPriorityFlag($priorityFlag);
    }

    /**
     * Set the priority flag (Y/N or space).
     *
     * @param bool|string|null $flag
     * @return $this
     */
    public function setPriorityFlag(null|bool|string $flag): self
    {
        $this->data[self::IDX_PRIORITY_FLAG] = $this->flagToValue($flag);
        return $this;
    }
}

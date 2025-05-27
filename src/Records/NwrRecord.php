<?php

namespace LabelTools\PhpCwrExporter\Records;

use LabelTools\PhpCwrExporter\Contracts\RecordInterface;

/**
 * v2.2 New Work Registration (NWR) record — fixed-width 167 chars:
 *  • Record Prefix                1–19   = 'NWR' + transaction prefix (built externally)
 *  • Work Title                  20–79   (60 chars)
 *  • Language Code               80–81   (2 chars, optional)
 *  • Submitter Work Number       82–95   (14 chars)
 *  • ISWC                       96–106   (11 chars, optional)
 *  • Copyright Date            107–114   (8 chars, optional)
 *  • Copyright Number          115–126   (12 chars, optional)
 *  • Musical Work Distribution 127–129   (3 chars)
 *  • Duration                  130–135   (6 chars, optional)
 *  • Recorded Indicator        136–136   (1 char, 'Y' or 'N')
 *  • Text Music Relationship   137–139   (3 chars, optional)
 *  • Padding                   140–167   spaces
 *
 */
class NwrRecord implements RecordInterface
{
    public function __construct(
        protected string $workTitle,
        protected ?string $languageCode,
        protected string $submitterWorkNumber,
        protected ?string $iswc,
        protected ?string $copyrightDate,
        protected ?string $copyrightNumber,
        protected string $distributionCategory,
        protected ?string $duration,
        protected bool   $recorded,
        protected ?string $textMusicRelation = ''
    ) {}

    public function toString(int $transactionSequence, int $recordSequence): string
    {
        // Build the fixed-width fields
        $line  = str_pad('NWR', 3);                                       // Record Type
        // Transaction prefix (group & txn seq) should be handled by the Version class
        $line .= str_pad($this->workTitle, 60);
        $line .= str_pad($this->languageCode ?? '', 2);
        $line .= str_pad($this->submitterWorkNumber, 14);
        $line .= str_pad($this->iswc ?? '', 11);
        $line .= str_pad($this->copyrightDate ?? '', 8);
        $line .= str_pad($this->copyrightNumber ?? '', 12);
        $line .= str_pad($this->distributionCategory, 3);
        $line .= str_pad($this->duration ?? '', 6);
        $line .= $this->recorded ? 'Y' : 'N';                             // Recorded Indicator
        $line .= str_pad($this->textMusicRelation ?? '', 3);
        // Pad the remainder
        $line .= str_repeat(' ', 167 - mb_strlen($line));

        return $line;
    }
}

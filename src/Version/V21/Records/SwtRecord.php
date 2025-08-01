<?php

namespace LabelTools\PhpCwrExporter\Version\V21\Records;

use LabelTools\PhpCwrExporter\Enums\TisCode;
use LabelTools\PhpCwrExporter\Fields\HasSequenceNumber;

class SwtRecord extends \LabelTools\PhpCwrExporter\Records\SwtRecord
{
    use HasSequenceNumber;

    protected const INDEX_SEQUENCE_NUM = 9;

    public function __construct(
        string $interestedPartyNumber,
        string $inclusionExclusionIndicator,
        int $prCollectionShare = 0,
        int $mrCollectionShare = 0,
        int $srCollectionShare = 0,
        null|int|TisCode $tisNumericCode = null,
        string $sharesChange = '',
        ?int $sequenceNumber = 1
    ) {
        parent::__construct(
            $interestedPartyNumber,$inclusionExclusionIndicator, $prCollectionShare, $mrCollectionShare, $srCollectionShare, $tisNumericCode, $sharesChange
        );
        $this->stringFormat .= "%03d";

        $this->setSequenceNumber($sequenceNumber);
    }

    protected function getSequenceNumberIndex(): int
    {
        return self::INDEX_SEQUENCE_NUM;
    }
}


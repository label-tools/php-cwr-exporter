<?php

namespace LabelTools\PhpCwrExporter\Version\V21\Records\Detail;

use LabelTools\PhpCwrExporter\Enums\TisCode;
use LabelTools\PhpCwrExporter\Fields\HasSequenceNumber;

class SwtRecord extends \LabelTools\PhpCwrExporter\Records\Detail\SwtRecord
{
    use HasSequenceNumber;

    protected const IDX_SEQUENCE_NUMBER = 9;

    public function __construct(
        string $interestedPartyNumber,
        string $inclusionExclusionIndicator,
        float|int $prCollectionShare,
        float|int $mrCollectionShare,
        float|int $srCollectionShare,
        int|TisCode $tisNumericCode,
        string $sharesChange = '',
        ?int $sequenceNumber = 1
    ) {
        parent::__construct(
            $interestedPartyNumber,$inclusionExclusionIndicator, $prCollectionShare, $mrCollectionShare, $srCollectionShare, $tisNumericCode, $sharesChange
        );
        $this->stringFormat .= "%03d";

        $this->setSequenceNumber($sequenceNumber);
    }

}


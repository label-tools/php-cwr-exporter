<?php

namespace LabelTools\PhpCwrExporter\Version\V21\Records;

use LabelTools\PhpCwrExporter\Enums\TisCode;

class SwtRecord extends \LabelTools\PhpCwrExporter\Records\SwtRecord
{
    protected const IDX_SEQUENCE_NUMBER = 9;

    public function __construct(
        string $interestedPartyNumber,
        ?int $prCollectionShare = null,
        ?int $mrCollectionShare = null,
        ?int $srCollectionShare = null,
        string $inclusionExclusionIndicator = '',
        null|int|TisCode $tisNumericCode = null,
        string $sharesChange = '',
        ?int $sequenceNum = 0
    ) {
        parent::__construct(
            $interestedPartyNumber, $prCollectionShare, $mrCollectionShare, $srCollectionShare,
            $inclusionExclusionIndicator, $tisNumericCode, $sharesChange
        );
        $this->stringFormat .= "%03d";

        $this->setSequenceNumber($sequenceNum);
    }

    public function setSequenceNumber(int $seq): static
    {
        if ($seq < 0 || $seq > 999) {
            throw new \InvalidArgumentException("Sequence Number must be between 0 and 999.");
        }
        $this->data[self::IDX_SEQUENCE_NUMBER] = $seq;
        return $this;
    }


}

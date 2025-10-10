<?php

namespace LabelTools\PhpCwrExporter\Version\V21\Records\Detail;

use LabelTools\PhpCwrExporter\Fields\HasSequenceNumber;

class SptRecord extends \LabelTools\PhpCwrExporter\Records\Detail\SptRecord
{
    use HasSequenceNumber;

    protected const IDX_SEQUENCE_NUMBER = 10;

    public function __construct(
        string $interestedPartyNumber,
        int|float $prCollectionShare,
        int|float $mrCollectionShare,
        int|float $srCollectionShare,
        string $inclusionExclusionIndicator, // 'I' for inclusion, 'E' for exclusion
        string $tisNumericCode,
        ?string $sharesChange = null,
        ?int $sequenceNumber = 1
    ) {
        // Initialize character set
        $this->stringFormat .= "%03d";

        parent::__construct($interestedPartyNumber, $prCollectionShare, $mrCollectionShare, $srCollectionShare, $inclusionExclusionIndicator, $tisNumericCode, $sharesChange);
        $this->setSequenceNumber($sequenceNumber);
    }

    protected function validateBeforeToString(): void
    {
        parent::validateBeforeToString();

        if (!isset($this->data[self::IDX_SEQUENCE_NUMBER]) || $this->data[self::IDX_SEQUENCE_NUMBER] < 1 || $this->data[self::IDX_SEQUENCE_NUMBER] > 999) {
            throw new \InvalidArgumentException('Sequence number must be set and greater than 0 and less than or equal to 999.');
        }
    }

}
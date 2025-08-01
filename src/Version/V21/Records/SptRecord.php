<?php

namespace LabelTools\PhpCwrExporter\Version\V21\Records;

use LabelTools\PhpCwrExporter\Fields\HasSequenceNumber;

class SptRecord extends \LabelTools\PhpCwrExporter\Records\SptRecord
{
    use HasSequenceNumber;

    protected const INDEX_SEQUENCE_NUM = 10;

    public function __construct(
        protected string $interestedPartyNumber,
        protected int $prCollectionShare,
        protected int $mrCollectionShare,
        protected int $srCollectionShare,
        protected string $inclusionExclusionIndicator, // 'I' for inclusion, 'E' for exclusion
        protected string $tisNumericCode,
        protected ?string $sharesChange = null,
        protected ?int $sequenceNumber = 1
    ) {
        // Initialize character set
        $this->stringFormat .= "%03d";

        parent::__construct($interestedPartyNumber, $prCollectionShare, $mrCollectionShare, $srCollectionShare, $inclusionExclusionIndicator, $tisNumericCode, $sharesChange);
        $this->setSequenceNumber($sequenceNumber);
    }

    protected function getSequenceNumberIndex(): int
    {
        return self::INDEX_SEQUENCE_NUM;
    }

    protected function validateBeforeToString(): void
    {
        parent::validateBeforeToString();

        if (!isset($this->data[self::INDEX_SEQUENCE_NUM]) || $this->data[self::INDEX_SEQUENCE_NUM] < 1 || $this->data[self::INDEX_SEQUENCE_NUM] > 999) {
            throw new \InvalidArgumentException('Sequence number must be set and greater than 0 and less than or equal to 999.');
        }
    }

}
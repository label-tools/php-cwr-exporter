<?php

namespace LabelTools\PhpCwrExporter\Records\Detail;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Enums\TisCode;
use LabelTools\PhpCwrExporter\Fields\HasCollectionShare;
use LabelTools\PhpCwrExporter\Fields\HasInterestedPartyNumber;
use LabelTools\PhpCwrExporter\Fields\HasTisNumericCode;
use LabelTools\PhpCwrExporter\Records\Record;

//The SPT record defines the territory and the collection shares for the preceding SPU publisher.
class SptRecord extends Record
{
    use HasInterestedPartyNumber;
    use HasCollectionShare;
    use HasTisNumericCode;

    protected static string $recordType = 'SPT';

    protected string $stringFormat =
        "%-19s" .  // Record Prefix (19 A)
        "%-9s"  .  // Interested Party # (9 A)
        "%-6s"  .  // Constant (6 A, spaces)
        "%05d"  .  // PR Collection Share (5 N)
        "%05d"  .  // MR Collection Share (5 N)
        "%05d"  .  // SR Collection Share (5 N)
        "%-1s"  .  // Inclusion/Exclusion Indicator (1 L)
        "%-4s"  .  // TIS Numeric Code (4 L)
        "%-1s";    // Shares Change (1 B)

    protected const IDX_INTERESTED_PARTY_NUMBER = 2;
    protected const IDX_CONSTANT = 3;
    protected const IDX_PR_COLLECTION_SHARE = 4;
    protected const IDX_MR_COLLECTION_SHARE = 5;
    protected const IDX_SR_COLLECTION_SHARE = 6;
    protected const IDX_INCL_EXCL = 7;
    protected const IDX_TIS_NUMERIC_CODE = 8;
    protected const IDX_SHARES_CHANGE = 9;

    public function __construct(
         string $interestedPartyNumber,
         int|float $prCollectionShare,
         int|float $mrCollectionShare,
         int|float $srCollectionShare,
         string $inclusionExclusionIndicator, // 'I' for inclusion, 'E' for exclusion
         string $tisNumericCode,
         ?string $sharesChange = null,
    ) {
        parent::__construct();
        $this->setInterestedPartyNumber($interestedPartyNumber)
             ->setConstant()
             ->setPrCollectionShare($prCollectionShare)
             ->setMrCollectionShare($mrCollectionShare)
             ->setSrCollectionShare($srCollectionShare)
             ->setInclusionExclusionIndicator($inclusionExclusionIndicator)
             ->setTisNumericCode($tisNumericCode)
             ->setSharesChange($sharesChange ?? '');
    }

    private function setConstant(): self
    {
        return $this->setAlphaNumeric(static::IDX_CONSTANT, '', 'Constant');
    }

    private function setInclusionExclusionIndicator(string $indicator): self
    {
        if ($indicator !== 'I' && $indicator !== 'E') {
            throw new InvalidArgumentException('Inclusion/Exclusion Indicator must be "I" or "E".');
        }
        $this->data[static::IDX_INCL_EXCL] = $indicator;
        return $this;
    }

    public function setSharesChange(?string $flag): self
    {
        if (!empty($flag) && $flag !== 'Y') {
            throw new InvalidArgumentException('Shares Change flag must be empty or "Y".');
        }
        $this->data[static::IDX_SHARES_CHANGE] = $flag;
        return $this;
    }

}
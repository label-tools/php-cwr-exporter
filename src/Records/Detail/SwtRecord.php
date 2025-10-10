<?php

namespace LabelTools\PhpCwrExporter\Records\Detail;

use LabelTools\PhpCwrExporter\Enums\TisCode;
use LabelTools\PhpCwrExporter\Fields\HasCollectionShare;
use LabelTools\PhpCwrExporter\Fields\HasInterestedPartyNumber;
use LabelTools\PhpCwrExporter\Fields\HasTisNumericCode;
use LabelTools\PhpCwrExporter\Records\Record;

class SwtRecord extends Record
{
    use HasInterestedPartyNumber;
    use HasTisNumericCode;
    use HasCollectionShare;

    protected static string $recordType = 'SWT';

    // Field indices for data array
    protected const IDX_INTERESTED_PARTY_NUMBER = 2;
    protected const IDX_PR_COLLECTION_SHARE = 3;
    protected const IDX_MR_COLLECTION_SHARE = 4;
    protected const IDX_SR_COLLECTION_SHARE = 5;
    protected const IDX_INCLUSION_EXCLUSION_INDICATOR = 6;
    protected const IDX_TIS_NUMERIC_CODE = 7;
    protected const IDX_SHARES_CHANGE = 8;

    /**
     * Fixed-length string format for SWT layout (including Version 2.1 Sequence #)
     *
     *  "%-19s" .   // Record Prefix (19 A)
     *  "%-9s"  .   // Interested Party # (9 A)
     *  "%05d"  .   // PR Collection Share (5 N: 00000–10000)
     *  "%05d"  .   // MR Collection Share (5 N: 00000–10000)
     *  "%05d"  .   // SR Collection Share (5 N: 00000–10000)
     *  "%-1s"  .   // Inclusion/Exclusion Indicator (1 L: "I" or "E")
     *  "%-4s"  .   // TIS Numeric Code (4 L, left-aligned)
     *  "%-1s"  .   // Shares Change (1 B: "Y" or blank)
     */
    protected string $stringFormat =
        "%-19s" .   // 1–19: Record Prefix
        "%-9s"  .   // 20–28: Interested Party #
        "%05d"  .   // 29–33: PR Collection Share
        "%05d"  .   // 34–38: MR Collection Share
        "%05d"  .   // 39–43: SR Collection Share
        "%-1s"  .   // 44: Inclusion/Exclusion Indicator
        "%-4s"  .   // 45–48: TIS Numeric Code (left‐aligned)
        "%-1s" ;   // 49: Shares Change


    public function __construct(
        string $interestedPartyNumber,
        string $inclusionExclusionIndicator,
        int|float $prCollectionShare,
        int|float $mrCollectionShare,
        int|float $srCollectionShare,
        int|TisCode $tisNumericCode,
        string $sharesChange = ''
    ) {
        parent::__construct();

        $this
            ->setInterestedPartyNumber($interestedPartyNumber)
            ->setPrCollectionShare($prCollectionShare)
            ->setMrCollectionShare($mrCollectionShare)
            ->setSrCollectionShare($srCollectionShare)
            ->setInclusionExclusionIndicator($inclusionExclusionIndicator)
            ->setTisNumericCode($tisNumericCode)
            ->setSharesChange($sharesChange);
    }

    /**
     * @param string $flag Must be 'I' or 'E'.
     */
    public function setInclusionExclusionIndicator(string $flag): static
    {
        $flag = trim($flag);
        if (!in_array($flag, ['I', 'E'], true)) {
            throw new \InvalidArgumentException("Inclusion/Exclusion Indicator must be 'I' or 'E'.");
        }
        $this->data[self::IDX_INCLUSION_EXCLUSION_INDICATOR] = $flag;
        return $this;
    }

    /**
     * @param string $flag Must be 'Y' or blank.
     */
    public function setSharesChange(string $flag): static
    {
        $flag = trim($flag);
        if ($flag !== '' && $flag !== 'Y') {
            throw new \InvalidArgumentException("Shares Change flag must be blank or 'Y'.");
        }
        $this->data[self::IDX_SHARES_CHANGE] = $flag;
        return $this;
    }

    protected function validateBeforeToString(): void
    {
        parent::validateBeforeToString();

        // Mandatory fields for SWT:
        if (empty($this->data[self::IDX_INTERESTED_PARTY_NUMBER]) && static::$recordType == 'SWT') {
            throw new \RuntimeException("Interested Party Number is required.");
        }
        if (!isset($this->data[self::IDX_INCLUSION_EXCLUSION_INDICATOR])) {
            throw new \RuntimeException("Inclusion/Exclusion Indicator is required.");
        }
        if (empty($this->data[self::IDX_TIS_NUMERIC_CODE])) {
            throw new \RuntimeException("TIS Numeric Code is required.");
        }
        // if (!isset($this->data[self::IDX_SEQUENCE_NUMBER])) {
        //     throw new \RuntimeException("SWT: Sequence Number is required (Version 2.1).");
        // }
    }
}
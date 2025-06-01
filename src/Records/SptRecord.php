<?php

namespace LabelTools\PhpCwrExporter\Records;

use InvalidArgumentException;
use LabelTools\PhpCwrExporter\Enums\TisCode;

//The SPT record defines the territory and the collection shares for the preceding SPU publisher.
class SptRecord extends Record
{
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

    protected const IDX_PREFIX = 1;
    protected const IDX_INTERESTED_PARTY = 2;
    protected const IDX_CONSTANT = 3;
    protected const IDX_PR_COLLECTION_SHARE = 4;
    protected const IDX_MR_COLLECTION_SHARE = 5;
    protected const IDX_SR_COLLECTION_SHARE = 6;
    protected const IDX_INCL_EXCL = 7;
    protected const IDX_TIS = 8;
    protected const IDX_SHARES_CHANGE = 9;

    public function __construct(
        protected string $interestedPartyNumber,
        protected int $prCollectionShare,
        protected int $mrCollectionShare,
        protected int $srCollectionShare,
        protected string $inclusionExclusionIndicator, // 'I' for inclusion, 'E' for exclusion
        protected string $tisNumericCode,
        protected ?string $sharesChange = null,
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

    private function setInterestedPartyNumber(string $partyNumber): self
    {
        if ($partyNumber === '' || strlen($partyNumber) > 9) {
            throw new InvalidArgumentException('Interested Party Number must be non-empty and at most 9 characters.');
        }
        $this->data[self::IDX_INTERESTED_PARTY] = $partyNumber;
        return $this;
    }

    private function setConstant(): self
    {
        $this->data[self::IDX_CONSTANT] = str_repeat(' ', 6);
        return $this;
    }

    private function setPrCollectionShare(int $share): self
    {
        if ($share < 0 || $share > 5000) {
            throw new InvalidArgumentException('PR Collection Share must be between 0 and 5000.');
        }
        $this->data[self::IDX_PR_COLLECTION_SHARE] = $share;
        return $this;
    }

    private function setMrCollectionShare(int $share): self
    {
        if ($share < 0 || $share > 10000) {
            throw new InvalidArgumentException('MR Collection Share must be between 0 and 10000.');
        }
        $this->data[self::IDX_MR_COLLECTION_SHARE] = $share;
        return $this;
    }

    private function setSrCollectionShare(int $share): self
    {
        if ($share < 0 || $share > 10000) {
            throw new InvalidArgumentException('SR Collection Share must be between 0 and 10000.');
        }
        $this->data[self::IDX_SR_COLLECTION_SHARE] = $share;
        return $this;
    }

    private function setInclusionExclusionIndicator(string $indicator): self
    {
        if ($indicator !== 'I' && $indicator !== 'E') {
            throw new InvalidArgumentException('Inclusion/Exclusion Indicator must be "I" or "E".');
        }
        $this->data[self::IDX_INCL_EXCL] = $indicator;
        return $this;
    }

    private function setTisNumericCode(string $code): self
    {
        if (TisCode::tryFrom($code) === null) {
            throw new InvalidArgumentException('Invalid TIS Numeric Code.');
        }
        $this->data[self::IDX_TIS] = $code;
        return $this;
    }

    public function setSharesChange(?string $flag): self
    {
        if (!empty($flag) && $flag !== 'Y') {
            throw new InvalidArgumentException('Shares Change flag must be empty or "Y".');
        }
        $this->data[self::IDX_SHARES_CHANGE] = $flag;
        return $this;
    }


    protected function validateBeforeToString(): void
    {

    }
}
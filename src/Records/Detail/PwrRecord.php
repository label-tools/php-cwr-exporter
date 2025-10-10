<?php

namespace LabelTools\PhpCwrExporter\Records\Detail;

use LabelTools\PhpCwrExporter\Records\Record;

class PwrRecord extends Record
{
    protected static string $recordType = 'PWR';

    protected const IDX_PUBLISHER_IP_NUMBER     = 2; // 9 chars (20-28)
    protected const IDX_PUBLISHER_NAME          = 3; // 45 chars (29-73)
    protected const IDX_SUBMITTER_AGREEMENT_NO  = 4; // 14 chars (74-87) - Version 2.0
    protected const IDX_SOCIETY_AGREEMENT_NO    = 5; // 14 chars (88-101) - Version 2.0

    protected string $stringFormat =
        "%-19s" .  // Record Prefix (19A)
        "%-9s"  .  // Publisher IP # (9A)
        "%-45s" .  // Publisher Name (45A)
        "%-14s" .  // Submitter Agreement Number (14A)
        "%-14s";   // Society-Assigned Agreement Number (14A)

    public function __construct(
        string $publisherIpNumber,
        string $publisherName,
        ?string $submitterAgreementNumber = '',
        ?string $societyAssignedAgreementNumber = ''
    ) {
        parent::__construct();

        $this
            ->setPublisherIpNumber($publisherIpNumber)
            ->setPublisherName($publisherName)
            ->setSubmitterAgreementNumber($submitterAgreementNumber)
            ->setSocietyAssignedAgreementNumber($societyAssignedAgreementNumber);
    }

    public function setPublisherIpNumber(string $ip): self
    {
        $ip = trim($ip);
        if ($ip === '' || strlen($ip) > 9) {
            throw new \InvalidArgumentException('Publisher IP # must be 1-9 characters.');
        }
        return $this->setAlphaNumeric(self::IDX_PUBLISHER_IP_NUMBER, $ip, 'Publisher IP #');
    }

    public function setPublisherName(string $name): self
    {
        $name = trim($name);
        if ($name === '' || strlen($name) > 45) {
            throw new \InvalidArgumentException("Publisher Name is required and must not exceed 45 characters.");
        }
        return $this->setAlphaNumeric(self::IDX_PUBLISHER_NAME, $name, 'Publisher Name');
    }

    public function setSubmitterAgreementNumber(?string $agr): self
    {
        $agr = trim((string)$agr);
        if ($agr !== '' && strlen($agr) > 14) {
            throw new \InvalidArgumentException("Submitter Agreement Number must not exceed 14 characters.");
        }
        return $this->setAlphaNumeric(self::IDX_SUBMITTER_AGREEMENT_NO, $agr, 'Submitter Agreement Number');
    }

    public function setSocietyAssignedAgreementNumber(?string $soc): self
    {
        $soc = trim((string)$soc);
        if ($soc !== '' && strlen($soc) > 14) {
            throw new \InvalidArgumentException("Society-Assigned Agreement Number must not exceed 14 characters.");
        }
        return $this->setAlphaNumeric(self::IDX_SOCIETY_AGREEMENT_NO, $soc, 'Society-Assigned Agreement Number');
    }

    protected function validateBeforeToString(): void
    {
        parent::validateBeforeToString();

        // Publisher IP # and Publisher Name are always required (already enforced in setters).
        if (empty($this->data[self::IDX_PUBLISHER_IP_NUMBER])) {
            throw new \RuntimeException("PWR: Publisher IP # is required.");
        }
        if (empty($this->data[self::IDX_PUBLISHER_NAME])) {
            throw new \RuntimeException("PWR: Publisher Name is required.");
        }
        // No further interdependent checks in v2.0
    }
}
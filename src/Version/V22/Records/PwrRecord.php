<?php

namespace LabelTools\PhpCwrExporter\Version\V22\Records;

use LabelTools\PhpCwrExporter\Contracts\RecordInterface;

/**
 * v2.2 Publisher For Writer (PWR) record — fixed-width 167 chars:
 *
 * Fields (fixed-width 167 chars):
 *  • Record Type                 1–3   = "PWR"
 *  • Publisher IP #             20–28   (9 chars)
 *  • Publisher Name            29–73   (45 chars, optional)
 *  • Submitter Agreement #     74–87   (14 chars, optional)
 *  • Society-Assigned Agr #    88–101  (14 chars, optional)
 *  • Writer IP #               102–110 (9 chars, optional)
 *  • Publisher Sequence #      111–112 (2 chars)
 *  • Padding                   113–167 spaces
 *
 * fileciteturn11file7
 */
class PwrRecord implements RecordInterface
{
    public function __construct(
        protected string $publisherIp,
        protected string $publisherName,
        protected ?string $submitterAgreementNumber,
        protected ?string $societyAgreementNumber,
        protected ?string $writerIpNumber,
        protected int    $publisherSequence
    ) {}

    public function toString(int $transactionSequence, int $recordSequence): string
    {
        $line  = str_pad('PWR', 3);                                    // Record Type
        $line .= str_pad($this->publisherIp, 9);                      // Publisher IP #
        $line .= str_pad($this->publisherName ?? '', 45);             // Publisher Name
        $line .= str_pad($this->submitterAgreementNumber ?? '', 14); // Submitter Agreement #
        $line .= str_pad($this->societyAgreementNumber  ?? '', 14);  // Society-Assigned Agr #
        $line .= str_pad($this->writerIpNumber ?? '', 9);            // Writer IP #
        $line .= str_pad((string)$this->publisherSequence, 2, '0', STR_PAD_LEFT); // Publisher Sequence #
        $line .= str_repeat(' ', 167 - mb_strlen($line));             // Padding

        return $line;
    }
}
<?php

namespace LabelTools\PhpCwrExporter\Version\V22\Records;

use LabelTools\PhpCwrExporter\Contracts\RecordInterface;

/**
 * v2.2 Transmission Header (HDR) record, fixed-width 167 characters:
 *  • Record Type        1–3   = "HDR"
 *  • Sender Type        4–5
 *  • Sender ID          6–14
 *  • Sender Name       15–59
 *  • EDI Version      60–64   = "01.10"
 *  • Creation Date    65–72   (YYYYMMDD)
 *  • Creation Time    73–78   (HHMMSS)
 *  • Transmission Date 79–86  (YYYYMMDD)
 *  • Character Set    87–101  (optional)
 *  • Version         102–104  (e.g. "2.2")
 *  • Revision        105–107  (e.g. "001")
 *  • Software Package 108–137 (optional)
 *  • Software Version 138–167 (optional)
 *
 *
 */
class HdrRecord implements RecordInterface
{
    public function __construct(
        protected string $senderType,
        protected string $senderId,
        protected string $senderName,
        protected ?string $characterSet = '',
        protected string $version       = '2.2',
        protected string $revision      = '1',
        protected ?string $softwarePkg  = '',
        protected ?string $softwareVer  = ''
    ) {}

    public function toString(int $transactionSequence, int $recordSequence): string
    {
        $nowDate = now()->format('Ymd');
        $nowTime = now()->format('His');

        $line  = str_pad('HDR', 3);                                  // Record Type
        $line .= str_pad($this->senderType, 2);                     // Sender Type
        $line .= str_pad($this->senderId, 9, '0', STR_PAD_LEFT);     // Sender ID
        $line .= str_pad($this->senderName, 45);                    // Sender Name
        $line .= str_pad('01.10', 5);                               // EDI Version
        $line .= $nowDate;                                          // Creation Date
        $line .= $nowTime;                                          // Creation Time
        $line .= $nowDate;                                          // Transmission Date
        $line .= str_pad($this->characterSet ?? '', 15);            // Character Set
        $line .= str_pad($this->version, 3);                        // CWR Version
        $line .= str_pad($this->revision, 3, '0', STR_PAD_LEFT);    // Revision
        $line .= str_pad($this->softwarePkg  ?? '', 30);            // Software Package
        $line .= str_pad($this->softwareVer  ?? '', 30);            // Software Version

        return $line;
    }
}
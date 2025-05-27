<?php

namespace LabelTools\PhpCwrExporter\Records;

use LabelTools\PhpCwrExporter\Contracts\RecordInterface;

/**
 * v2.2 Alternate Title (ALT) record — fixed-width 167 chars:
 *
 * Field layout (start–length):
 *  • Record Prefix    1–19  = "ALT" + 16-char prefix
 *  • Alternate Title 20–79  (60 chars)
 *  • Title Type      80–81   (2 chars, required)
 *  • Language Code   82–83   (2 chars, conditional)
 *  • Padding        84–167   spaces
 *
 * Spec: ALT record format and validation rules fileciteturn15file0
 */
class AltRecord implements RecordInterface
{
    public function __construct(
        protected string $alternateTitle,
        protected string $titleType,
        protected ?string $languageCode = ''
    ) {}

    public function toString(int $transactionSequence, int $recordSequence): string
    {
        // Build and pad each field
        $line  = str_pad('ALT', 3);                                     // Record Type
        // Prefixed transactional numbers (group & txn & rec seq) are handled upstream
        $line .= str_pad($this->alternateTitle, 60);                   // Alternate Title
        $line .= str_pad($this->titleType, 2);                         // Title Type
        $line .= str_pad($this->languageCode ?? '', 2);                // Language Code
        // Pad remainder to 167 chars
        $line .= str_repeat(' ', 167 - mb_strlen($line));

        return $line;
    }
}

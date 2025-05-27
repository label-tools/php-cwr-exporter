<?php

namespace LabelTools\PhpCwrExporter\Enums;

/**
 * CWR Sender Type codes (2-character values) per CWR spec:
 *  - PB: Publisher
 *  - SO: Society
 *  - AA: Administrative Agency
 *  - WR: Writer
 */
enum SenderType: string
{
    case PUBLISHER = 'PB';
    case SOCIETY = 'SO';
    case AGENCY = 'AA';
    case WRITER = 'WR';
}

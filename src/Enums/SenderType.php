<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum SenderType: string
{
    case PUBLISHER = 'PB';
    case SOCIETY = 'SO';
    case AGENCY = 'AA';
    case WRITER = 'WR';

    public static function regularTypes(): array
    {
        return [self::PUBLISHER, self::AGENCY, self::WRITER];
    }

    public function isRegular(): bool
    {
        return in_array($this, self::regularTypes(), true);
    }
}
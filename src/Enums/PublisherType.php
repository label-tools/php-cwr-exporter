<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum PublisherType: string
{
    case ACQUIRER = 'AQ';
    case ADMINISTRATOR = 'AM';
    case INCOME_PARTICIPANT = 'PA';
    case ORIGINAL_PUBLISHER = 'E';
    case SUBSTITUTED_PUBLISHER = 'ES';
    case SUB_PUBLISHER = 'SE';

    public function getName(): string
    {
        return match ($this) {
            // Override for non-specific category if desired
            default => ucwords(strtolower(str_replace('_', ' ', $this->name))),
        };
    }

    /**
     * Returns a detailed description of the publisher type.
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::ACQUIRER => 'A publisher that acquires some or all of the ownership from an Original Publisher, but yet the Original Publisher still controls the work.',
            self::ADMINISTRATOR => 'An interested party that collects royalty payments on behalf of a publisher that it represents.',
            self::INCOME_PARTICIPANT => 'A person or corporation that receives royalty payments for a work but is not a copyright owner.',
            self::ORIGINAL_PUBLISHER => 'The interested party which has acquired by agreement with a composer and/or author rights in one or more works for a stipulated territory and duration.',
            self::SUBSTITUTED_PUBLISHER => 'A publisher acting on behalf of publisher or sub-publisher.',
            self::SUB_PUBLISHER => 'The interested party which has acquired by agreement with a publisher rights in one or more works for a stipulated territory and duration.',
        };
    }
}
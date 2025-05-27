<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum TransactionType: string
{
    case ACKNOWLEDGMENT = 'ACK';
    case AGREEMENT = 'AGR';
    case NEW_WORK_REGISTRATION = 'NWR';
    case REVISED_REGISTRATION = 'REV';
    case NOTIFICATION_OF_ISWC = 'ISW';
    case EXISTING_CONFLICT = 'EXC';
    case NOTIFICATION_TO_COPUBLISHER = 'COP';
}

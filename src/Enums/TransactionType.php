<?php

namespace LabelTools\PhpCwrExporter\Enums;

enum TransactionType: string
{
    case ACKNOWLEDGMENT = 'ACK'; //Acknowledgment of Transaction
    case AGREEMENT = 'AGR'; //Agreement supporting Work Registration
    case NEW_WORK_REGISTRATION = 'NWR'; //New Works Registration
    case REVISED_REGISTRATION = 'REV'; //Revised Registration
    case NOTIFICATION_OF_ISWC = 'ISW'; //Notification of ISWC assigned to a work
    case EXISTING_CONFLICT = 'EXC'; //Existing work which is in conflict with a work registration
    case NOTIFICATION_TO_COPUBLISHER = 'COP';
}

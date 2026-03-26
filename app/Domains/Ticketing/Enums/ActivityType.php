<?php

namespace App\Domains\Ticketing\Enums;

enum ActivityType: string
{
    case STATUS_CHANGE = 'status_change';
    case ASSIGNMENT = 'assignment';
    case COMMENT = 'comment';
    case ATTACHMENT = 'attachment';
}

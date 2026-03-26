<?php

namespace App\Domains\Ticketing\Enums;

enum TicketStatus: string
{
    case OPEN = 'Open';
    case IN_PROGRESS = 'In Progress';
    case ON_HOLD = 'On Hold';
    case RESOLVED = 'Resolved';
    case CLOSED = 'Closed';
}

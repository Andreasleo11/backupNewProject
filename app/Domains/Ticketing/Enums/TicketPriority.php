<?php

namespace App\Domains\Ticketing\Enums;

enum TicketPriority: string
{
    case LOW = 'Low';
    case MEDIUM = 'Medium';
    case HIGH = 'High';
    case CRITICAL = 'Critical';
}

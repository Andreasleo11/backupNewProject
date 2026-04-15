<?php

namespace App\Domains\Ticketing\Actions;

use App\Domains\Ticketing\Entities\Ticket;
use App\Domains\Ticketing\Enums\TicketStatus;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Carbon\Carbon;

class CalculateKpiMetrics
{
    public function execute(User $pic, ?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        $startDate = $startDate ?? now()->startOfMonth();
        $endDate = $endDate ?? now()->endOfMonth();

        $picTickets = Ticket::with('category')
            ->where('assigned_to', $pic->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        // 1. Throughput: Tickets closed/resolved in period
        $throughput = $picTickets->filter(fn ($t) => in_array($t->status, [TicketStatus::RESOLVED, TicketStatus::CLOSED]))->count();

        // 2. SLA Compliance %
        $resolvedTickets = $picTickets->filter(fn ($t) => $t->resolved_at !== null);
        $slaMetCount = 0;

        foreach ($resolvedTickets as $t) {
            $ttrMinutes = $t->resolved_at->diffInMinutes($t->created_at) - $t->total_hold_time_minutes;
            $slaMinutes = $t->category->sla_hours * 60;

            if ($ttrMinutes <= $slaMinutes) {
                $slaMetCount++;
            }
        }

        $slaCompliance = $resolvedTickets->count() > 0
            ? round(($slaMetCount / $resolvedTickets->count()) * 100, 2)
            : 100.0; // Perfect score if no tickets

        // 3. Re-open Rate: Sum of reopen_count / Total Tickets Assigned (or Throughput)
        $totalReopens = $picTickets->sum('reopen_count');
        $reopenRate = $picTickets->count() > 0
            ? round(($totalReopens / $picTickets->count()) * 100, 2)
            : 0;

        // 4. Utilization: Concurrent IN_PROGRESS tickets (Current State Snapshot)
        $utilization = Ticket::where('assigned_to', $pic->id)
            ->where('status', TicketStatus::IN_PROGRESS)
            ->count();

        return [
            'pic_id' => $pic->id,
            'throughput' => $throughput,
            'sla_compliance' => $slaCompliance,
            'reopen_rate' => $reopenRate,
            'utilization' => $utilization,
            'period' => [
                'start' => $startDate->toDateTimeString(),
                'end' => $endDate->toDateTimeString(),
            ],
        ];
    }
}

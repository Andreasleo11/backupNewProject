<?php

namespace App\Livewire\Verification;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        // ponytail: single query, group by status — replaces legacy 4-query QaqcHomeController
        $counts = VerificationReport::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $draft    = (int) ($counts['DRAFT'] ?? 0);
        $inReview = (int) ($counts['IN_REVIEW'] ?? 0);
        $approved = (int) ($counts['APPROVED'] ?? 0);
        $rejected = (int) ($counts['REJECTED'] ?? 0);
        $total    = $draft + $inReview + $approved + $rejected;

        // Recent reports for quick overview
        $recent = VerificationReport::latest()->limit(5)->get();

        return view('livewire.verification.dashboard', compact(
            'draft', 'inReview', 'approved', 'rejected', 'total', 'recent'
        ));
    }
}

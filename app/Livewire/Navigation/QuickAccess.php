<?php

namespace App\Livewire\Navigation;

use App\Models\UserPageVisit;
use App\Models\UserPinnedRoute;
use App\Services\NavigationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class QuickAccess extends Component
{
    public bool $isMobile = false;

    /**
     * Pin a route. Called by Alpine star buttons via Livewire.dispatch.
     */
    #[On('nav-pin')]
    public function pin(string $routeName): void
    {
        $userId = Auth::id();

        // Validate route exists
        if (! \Route::has($routeName)) return;

        $count = UserPinnedRoute::where('user_id', $userId)->count();

        if ($count >= 3) {
            $this->dispatch('toast', type: 'warning', message: 'Maximum 3 pinned items allowed.');
            return;
        }

        UserPinnedRoute::firstOrCreate(
            ['user_id' => $userId, 'route_name' => $routeName],
            ['pinned_at' => now()]
        );

        // Notify all pin buttons about this route's new state
        $this->dispatch('pin-state-changed', routeName: $routeName, pinned: true);
    }

    /**
     * Unpin a route. Called by Alpine star buttons via Livewire.dispatch.
     */
    #[On('nav-unpin')]
    public function unpin(string $routeName): void
    {
        UserPinnedRoute::where('user_id', Auth::id())
            ->where('route_name', $routeName)
            ->delete();

        $this->dispatch('pin-state-changed', routeName: $routeName, pinned: false);
    }

    /**
     * Remove a tracked (non-pinned) item by deleting its visit record.
     */
    #[On('nav-remove-visit')]
    public function removeVisit(string $routeName): void
    {
        UserPageVisit::where('user_id', Auth::id())
            ->where('route_name', $routeName)
            ->delete();
    }

    public function render()
    {
        $items = NavigationService::getQuickAccessItems(Auth::user());

        return view('livewire.navigation.quick-access', [
            'items'    => $items,
            'isMobile' => $this->isMobile,
        ]);
    }
}

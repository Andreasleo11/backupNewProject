<?php

namespace App\Livewire\Admin;

use App\Domain\Admin\Services\PermissionAuditService;
use App\Models\PermissionSyncLog;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class PermissionSyncManager extends Component
{
    public $managedChanges = [];

    public $unmanagedRoles = [];

    public $logs = [];

    #[Url(history: true)]
    public $activeTab = 'compare';

    public function mount(PermissionAuditService $auditService)
    {
        $this->loadData($auditService);
    }

    public function loadData(PermissionAuditService $auditService)
    {
        $state = $auditService->getSyncState();
        $this->managedChanges = $state['managed'];
        $this->unmanagedRoles = $state['unmanaged'];
        $this->logs = PermissionSyncLog::with('user')->latest()->get();
    }

    public function syncPermissions(PermissionAuditService $auditService)
    {
        // Snapshot before
        $before = $auditService->getCurrentState();

        // Run the command
        Artisan::call('permissions:sync', ['--force' => true]);

        // Snapshot after
        $after = $auditService->getCurrentState();
        $changes = $auditService->calculateDiff($before, $after);

        if (! empty($changes)) {
            PermissionSyncLog::create([
                'user_id' => Auth::id(),
                'snapshot' => $before,
                'after_snapshot' => $after,
                'changes' => $changes,
                'description' => 'Synchronized via Web UI',
            ]);
        }

        $this->loadData($auditService);
        $this->dispatch('toast', message: 'Permissions synchronized successfully!', type: 'success');
    }

    public function revert(PermissionSyncLog $log, PermissionAuditService $auditService)
    {
        $auditService->revert($log);

        // Log the reversion itself as a NEW sync event
        PermissionSyncLog::create([
            'user_id' => Auth::id(),
            'snapshot' => $auditService->getCurrentState(), // state before reversion? No, auditService->revert needs to be careful.
            'after_snapshot' => $log->snapshot, // the target state
            'changes' => $auditService->calculateDiff($auditService->getCurrentState(), $log->snapshot),
            'description' => "Reverted to sync log #{$log->id}",
        ]);

        $this->loadData($auditService);
        $this->dispatch('toast', message: 'Permissions reverted successfully!', type: 'success');
    }

    public function deleteLog($logId)
    {
        PermissionSyncLog::destroy($logId);
        $this->logs = PermissionSyncLog::with('user')->latest()->get();
        $this->dispatch('toast', message: 'Log deleted successfully.', type: 'success');
    }

    public function render()
    {
        return view('livewire.admin.permission-sync-manager');
    }
}

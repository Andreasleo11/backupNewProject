<!-- resources/views/livewire/master-data-part/import-jobs-list.blade.php -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
            <div class="fw-semibold">Import History</div>
            <div class="d-flex gap-2">
                <input type="search" class="form-control form-control-sm" placeholder="Search id/type/error"
                    style="width: 220px" wire:model.live.debounce.400ms="search">
                <select class="form-select form-select-sm" style="width: 150px" wire:model.live="status">
                    <option value="all">All statuses</option>
                    <option value="running">Running</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Completed</option>
                    <option value="failed">Failed</option>
                </select>
                <select class="form-select form-select-sm" style="width: 90px" wire:model.live="perPage">
                    <option>5</option>
                    <option>25</option>
                    <option>50</option>
                </select>
            </div>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive" @if ($this->shouldPoll) wire:poll.1s.keep-alive @endif>
            <table class="table table-sm align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">#</th>
                        <th class="text-nowrap">Status</th>
                        <th class="text-nowrap">Progress</th>
                        <th class="text-nowrap">C/U/S</th>
                        <th class="text-nowrap">Last Updated</th>
                        <th class="text-nowrap">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jobs as $j)
                        @php
                            $total = max($j->total_rows ?? 0, 0);
                            $done = min($j->processed_rows ?? 0, $total);
                            $pct = $total > 0 ? (int) floor(($done / $total) * 100) : 0;
                        @endphp
                        <tr wire:key="job-{{ $j->id }}"
                            class="{{ $selectedJobId === $j->id ? 'table-active' : '' }}">
                            <td class="text-muted">{{ $j->id }}</td>
                            <td>
                                <span
                                    class="badge
                                        @if ($j->status === 'running') text-bg-primary
                                        @elseif($j->status === 'completed') text-bg-success
                                        @elseif($j->status === 'failed') text-bg-danger
                                        @else text-bg-secondary @endif">
                                    {{ ucfirst($j->status) }}
                                </span>
                            </td>
                            <td class="text-nowrap">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width:120px;height:10px;">
                                        <div class="progress-bar {{ $j->status === 'completed' ? 'bg-success' : '' }}"
                                            style="width: {{ $pct }}%"></div>
                                    </div>
                                    <small>{{ number_format($done) }}/{{ number_format($total) }}
                                        ({{ $pct }}%)
                                    </small>
                                </div>
                            </td>
                            <td class="text-nowrap">
                                <small>
                                    {{ number_format($j->created_rows ?? 0) }}/{{ number_format($j->updated_rows ?? 0) }}/{{ number_format($j->skipped_rows ?? 0) }}
                                </small>
                            </td>
                            <td class="text-nowrap">
                                <small title="{{ optional($j->updated_at)->toDateTimeString() }}">
                                    {{ optional($j->updated_at)->diffForHumans() ?? '—' }}
                                </small>
                            </td>
                            <td class="text-nowrap">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary"
                                        wire:click="selectJob({{ $j->id }})">
                                        Track
                                    </button>
                                    @if ($j->error_log_path)
                                        <a class="btn btn-outline-secondary" href="{{ route('import-jobs.log', $j) }}"
                                            target="_blank" rel="noopener">
                                            Log
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No jobs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer bg-white">
        {{ $jobs->links() }}
    </div>
</div>

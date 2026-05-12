@section('title', 'Overtime Requests')
@section('page-title', $isPrivileged ? 'Overtime Overview' : 'My Overtime Requests')
@section('page-subtitle', $isPrivileged ? 'Monitor and manage overtime submissions across departments' : 'Track the status of your submitted overtime requests')

<div class="space-y-4" x-data="{
    deleteOpen: false,
    filtersOpen: false,
    selectedIds: @entangle('selectedIds'),
    get isAllSelected() {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        if (checkboxes.length === 0) return false;
        return checkboxes.length === document.querySelectorAll('.row-checkbox:checked').length;
    },
    toggleAll(e) {
        if (e.target.checked) {
            this.selectedIds = Array.from(document.querySelectorAll('.row-checkbox')).map(cb => cb.value);
        } else {
            this.selectedIds = [];
        }
    }
}" x-on:show-delete-modal.window="deleteOpen = true"
   x-on:hide-delete-modal.window="deleteOpen = false">

    {{-- Zone 1: Slim Header --}}
    @include('livewire.overtime.partials._header')

    {{-- Zone 2: Toolbar — search + desktop range pills + filter panel trigger --}}
    <div class="rounded-2xl bg-white border border-slate-200/60 px-3 py-2.5 shadow-sm">
        @include('livewire.overtime.partials._toolbar')
    </div>

    {{-- Zone 3: Status Tabs — replaces metrics grid + urgent banners --}}
    @include('livewire.overtime.partials._status_tabs')

    {{-- Zone 4: Data Table + empty states + skeleton --}}
    @include('livewire.overtime.partials._table')

    {{-- Pagination --}}
    @if ($dataheader->hasPages())
        <div class="pb-6">{{ $dataheader->links() }}</div>
    @endif

    {{-- Spacer so floating bar doesn't cover last row --}}
    <div class="h-16"></div>

    {{-- Overlays (all teleported to body) --}}
    @include('livewire.overtime.partials._filter_panel')
    @include('livewire.overtime.partials._bulk_bar')
    @include('livewire.overtime.partials._modals')

</div>

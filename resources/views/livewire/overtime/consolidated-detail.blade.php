@section('title', 'Consolidated Overtime - ' . date('d M Y', strtotime($date)))
@section('page-title', 'Consolidated Overtime View')
@section('page-subtitle', 'Review all overtime forms for ' . date('l, d F Y', strtotime($date)) . ($branch ? ' - ' . $branch : '') . ($dept ? ' (Dept: ' . $dept . ')' : ''))

@section('title', 'Consolidated Overtime - ' . date('d M Y', strtotime($date)))
@section('page-title', 'Consolidated Overtime View')
@section('page-subtitle', 'Review all overtime forms for ' . date('l, d F Y', strtotime($date)) . ($branch ? ' - ' . $branch : '') . ($dept ? ' (Dept: ' . $dept . ')' : ''))

<div class="space-y-4" x-data="{
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
}" x-init="$wire.viewMode === 'grouped' ? null : (selectedIds = [])">

    {{-- Zone 1: Header + Global Stats --}}
    @include('livewire.overtime.partials._consolidated_header')

    {{-- Zone 2: Flattened Unified Table --}}
    @include('livewire.overtime.partials._consolidated_table')

    {{-- Spacer for floating bar --}}
    <div class="h-20"></div>

    {{-- Overlays --}}
    @include('livewire.overtime.partials._consolidated_bulk_bar')
    @include('livewire.overtime.partials._consolidated_modals')
    @include('livewire.overtime.partials._push_all_confirmation_modal')
    @include('livewire.overtime.partials._push_all_progress_modal')

</div>
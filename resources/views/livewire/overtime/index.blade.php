@section('title', 'Overtime Requests')
@section('page-title', 'Overtime Overview')
@section('page-subtitle', 'Monitor and manage overtime submissions across departments')

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



    {{-- Zone 3: Status Tabs — replaces metrics grid + urgent banners --}}
    @include('livewire.overtime.partials._status_tabs')

    {{-- Zone 4: Data Table + empty states + skeleton --}}
    @include('livewire.overtime.partials._table')

    {{-- Spacer so floating bar doesn't cover last row --}}
    <div class="h-20"></div>

    {{-- Overlays (all teleported to body) --}}
    @include('livewire.overtime.partials._filter_panel')
    @include('livewire.overtime.partials._bulk_bar')
    @include('livewire.overtime.partials._modals')

</div>

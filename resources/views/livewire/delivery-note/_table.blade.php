<table class="table table-bordered align-middle table-hover">
  <thead class="table-light">
    <tr>
      <th wire:click="sortBy('id')" style="cursor:pointer;">#
        @if ($sortField === 'id')
          @if ($sortDirection === 'asc')
            ↑
          @else
            ↓
          @endif
        @endif
      </th>
      <th wire:click="sortBy('branch')" style="cursor: pointer;">Branch
        @if ($sortField === 'branch')
          @if ($sortDirection === 'asc')
            ↑
          @else
            ↓
          @endif
        @endif
      </th>
      <th wire:click="sortBy('ritasi')" style="cursor: pointer;">
        Ritasi
        @if ($sortField === 'ritasi')
          @if ($sortDirection === 'asc')
            ↑
          @else
            ↓
          @endif
        @endif
      </th>
      <th wire:click="sortBy('delivery_note_date')" style="cursor: pointer;">
        Date
        @if ($sortField === 'delivery_note_date')
          @if ($sortDirection === 'asc')
            ↑
          @else
            ↓
          @endif
        @endif
      </th>
      <th wire:click="sortBy('plate_number')" style="cursor: pointer;">
        Vehicle
        @if ($sortField === 'plate_number')
          @if ($sortDirection === 'asc')
            ↑
          @else
            ↓
          @endif
        @endif
      </th>
      <th wire:click="sortBy('driver_name')" style="cursor: pointer;">
        Driver
        @if ($sortField === 'driver_name')
          @if ($sortDirection === 'asc')
            ↑
          @else
            ↓
          @endif
        @endif
      </th>
      <th>Status</th>
      <th class="text-center" style="width: 160px;">Actions</th>
    </tr>
  </thead>
  <tbody>
    @forelse($deliveryNotes as $note)
      <tr>
        <td>{{ $note->id }}</td>
        <td>{{ $note->branch }}</td>
        <td>{{ $note->ritasi_label }}</td>
        <td>{{ $note->formatted_delivery_note_date }}</td>
        <td>{{ $note->vehicle->plate_number ?? '-' }}</td>
        <td>{{ $note->vehicle->driver_name ?? '-' }}</td>
        <td>
          <span
            class="badge 
                                    @if ($note->status === 'draft') bg-warning text-dark 
                                    @else bg-success @endif">
            {{ ucfirst($note->status) }}
          </span>
        </td>
        <td class="text-center">
          <div class="btn-group" role="group">
            @php
              $isGuest = !auth()->check(); // true if no user is logged in
              $isLatest = $note->latest ?? false; // assumes `latest` is set in Livewire
            @endphp

            <a href="{{ route('delivery-notes.show', $note->id) }}"
              class="btn btn-sm btn-outline-info" title="View">
              🔍
            </a>

            @if (!$isGuest && auth()->check())
              {{-- Authenticated user: can edit and delete --}}
              <a href="{{ route('delivery-notes.edit', $note->id) }}"
                class="btn btn-sm btn-outline-warning" title="Edit">
                ✏️
              </a>
              <button x-data
                @click.prevent="if (confirm('Are you sure you want to delete this delivery note?')) { $wire.delete({{ $note->id }}) }"
                class="btn btn-sm btn-outline-danger" title="Delete">
                🗑
              </button>
            @elseif ($isGuest && $isLatest)
              {{-- Guest and latest note: only allow edit --}}
              <a href="{{ route('delivery-notes.edit', $note->id) }}"
                class="btn btn-sm btn-outline-warning" title="Edit">
                ✏️
              </a>
            @endif
          </div>
        </td>
      </tr>
    @empty
      <tr>
        <td colspan="9" class="text-center py-4">
          <div class="text-muted">No delivery notes found.</div>
        </td>
      </tr>
    @endforelse
  </tbody>
</table>

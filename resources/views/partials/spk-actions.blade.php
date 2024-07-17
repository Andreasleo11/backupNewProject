<a href="{{ route('spk.detail', $report->id) }}" class="my-1 btn btn-secondary">
    <i class='bx bx-info-circle'></i> Detail
</a>
@include('partials.delete-confirmation-modal', [
    'id' => $report->id,
    'title' => 'Delete ' . $report->no_dokumen,
    'body' => 'Are you sure want to delete ' . $report->no_dokumen,
    'route' => 'spk.delete',
])
<button data-bs-toggle="modal" data-bs-target="#delete-confirmation-modal-{{ $report->id }}"
    class="btn btn-danger">Delete</button>

<div class="inline-flex flex-wrap items-center justify-end gap-2">
    <a href="{{ route('spk.detail', $report->id) }}"
       class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2.5 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
        Detail
    </a>

    @if (! $report->status_laporan)
        <form action="{{ route('spk.delete', $report->id) }}"
              method="POST"
              onsubmit="return confirm('Are you sure you want to delete {{ $report->no_dokumen }}?');"
              class="inline">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center rounded-md border border-red-200 bg-red-50 px-2.5 py-1.5 text-xs font-medium text-red-700 shadow-sm hover:bg-red-100">
                Delete
            </button>
        </form>
    @endif
</div>

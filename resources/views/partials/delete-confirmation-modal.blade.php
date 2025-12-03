<div x-data="{ open: false }" class="px-4 py-6">
    <button @click="open = true"
        class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow hover:bg-red-700">
        Hapus Data
    </button>

    {{-- Backdrop --}}
    <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 bg-black/40" @click="open = false"
        @keydown.escape.window="open = false"></div>

    {{-- Modal --}}
    <div x-show="open" x-transition class="fixed inset-0 z-50 flex items-center justify-center px-4" aria-modal="true"
        role="dialog">
        <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-gray-200">
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-gray-900">
                    Konfirmasi Hapus
                </h2>
                <button @click="open = false"
                    class="rounded-full p-1.5 text-gray-400 hover:text-gray-600 hover:bg-gray-100">
                    &times;
                </button>
            </div>

            <div class="px-4 py-3 text-sm text-gray-600">
                Yakin ingin menghapus data ini? Aksi ini tidak bisa dibatalkan.
            </div>

            <div class="px-4 py-3 border-t border-gray-100 flex justify-end gap-2">
                <button type="button" @click="open = false"
                    class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-50">
                    Batal
                </button>
                <form method="POST" action="{{ route('something.destroy', 1) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

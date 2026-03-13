<div
    x-data="{ isOpen: @entangle('isOpen') }"
    x-show="isOpen"
    @keydown.escape.window="isOpen = false"
    class="relative z-[100]"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true"
    x-cloak
>
    <!-- Background backdrop -->
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
    ></div>

    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal panel -->
            <div
                x-show="isOpen"
                @click.away="isOpen = false"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-3xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-4xl"
            >
                <!-- Close button -->
                <div class="absolute right-0 top-0 hidden pr-4 pt-4 sm:block">
                    <button @click="isOpen = false" type="button" class="rounded-xl bg-white text-slate-400 hover:text-slate-500 transition-colors">
                        <span class="sr-only">Close</span>
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-left w-full">
                            <h3 class="text-2xl font-black text-slate-900 mb-1" id="modal-title">
                                @if($step === 'upload') Upload Laporan Baru @elseif($step === 'preview') Konfirmasi Data @else Hasil Pemrosesan @endif
                            </h3>
                            <p class="text-sm font-medium text-slate-500 mb-6 border-b border-slate-100 pb-4">
                                @if($step === 'upload') Unggah file Excel atau CSV untuk pratinjau. @elseif($step === 'preview') Periksa kembali data sebelum disimpan ke database. @else Ringkasan hasil pemrosesan file. @endif
                            </p>

                            {{-- Step 1: Upload --}}
                            @if($step === 'upload')
                                <div x-data="{ dragging: false, fileName: '' }" class="space-y-6">
                                    <div 
                                        class="relative flex justify-center rounded-3xl border-2 border-dashed px-6 py-16 transition-all cursor-pointer"
                                        :class="dragging ? 'border-indigo-500 bg-indigo-50/50 scale-[0.98]' : 'border-slate-200 hover:border-indigo-400 hover:bg-slate-50'"
                                        @dragover.prevent="dragging = true"
                                        @dragleave.prevent="dragging = false"
                                        @drop.prevent="
                                            dragging = false;
                                            if($event.dataTransfer.files.length > 0) {
                                                @this.upload('report_file', $event.dataTransfer.files[0])
                                                fileName = $event.dataTransfer.files[0].name;
                                            }
                                        "
                                        @click="$refs.fileInput.click()"
                                    >
                                        <div class="text-center">
                                            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-100 text-slate-400 mb-4" x-show="!fileName">
                                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" /></svg>
                                            </div>
                                            
                                            <div x-show="fileName" class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-emerald-100 text-emerald-600 mb-4" style="display: none;">
                                                <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            </div>

                                            <div class="flex text-sm text-slate-600 font-bold mb-1">
                                                <span x-show="!fileName">Klik untuk mengunggah</span>
                                                <span x-show="fileName" x-text="fileName" class="text-emerald-700"></span>
                                                <span x-show="!fileName" class="ml-1 font-medium">atau tarik file ke sini</span>
                                            </div>
                                            <p class="text-xs text-slate-400 font-medium">XLSX, CSV (Maks 10MB)</p>
                                        </div>
                                        <input type="file" wire:model="report_file" x-ref="fileInput" class="hidden" @change="fileName = $event.target.files[0]?.name || ''">
                                    </div>

                                    @error('report_file')
                                        <span class="text-xs font-bold text-rose-500 bg-rose-50 px-3 py-2 rounded-xl flex items-center gap-2">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                            {{ $message }}
                                        </span>
                                    @enderror

                                    <div class="flex items-center justify-end border-t border-slate-50 pt-6">
                                        <button 
                                            wire:click="processUpload" 
                                            wire:loading.attr="disabled"
                                            class="inline-flex items-center gap-2 rounded-2xl bg-indigo-600 px-8 py-3.5 text-sm font-black text-white shadow-xl shadow-indigo-200 transition-all hover:bg-indigo-700 hover:-translate-y-0.5 disabled:opacity-50"
                                        >
                                            <span wire:loading.remove>Lanjutkan & Pratinjau</span>
                                            <span wire:loading>Memproses...</span>
                                            <svg wire:loading.remove class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                                        </button>
                                    </div>
                                </div>

                            {{-- Step 2: Preview --}}
                            @elseif($step === 'preview')
                                <div class="space-y-6">
                                    <div class="overflow-x-auto rounded-2xl border border-slate-100 bg-slate-50/50">
                                        <table class="min-w-full text-left text-sm">
                                            <thead class="bg-slate-50 text-[10px] uppercase tracking-widest font-black text-slate-400">
                                                <tr>
                                                    <th class="px-4 py-3">Tanggal</th>
                                                    <th class="px-4 py-3 text-center">Jam</th>
                                                    <th class="px-4 py-3">Karyawan</th>
                                                    <th class="px-4 py-3">Deskripsi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100">
                                                @foreach(array_slice($previewData, 0, 10) as $row)
                                                    <tr>
                                                        <td class="px-4 py-3 font-bold text-slate-700 whitespace-nowrap">{{ $row['work_date'] }}</td>
                                                        <td class="px-4 py-3 text-center font-bold text-slate-500 whitespace-nowrap">{{ $row['work_time'] }}</td>
                                                        <td class="px-4 py-3">
                                                            <div class="font-black text-indigo-600">{{ $row['employee_name'] }}</div>
                                                            <div class="text-[10px] font-bold text-slate-400">{{ $row['employee_id'] }}</div>
                                                        </td>
                                                        <td class="px-4 py-3 text-slate-600 line-clamp-2">{{ $row['work_description'] }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @if(count($previewData) > 10)
                                            <div class="px-4 py-3 text-center text-xs font-bold text-slate-400 bg-white">
                                                + {{ count($previewData) - 10 }} baris lainnya...
                                            </div>
                                        @endif
                                    </div>

                                    <div class="flex items-center justify-between border-t border-slate-50 pt-6">
                                        <button wire:click="$set('step', 'upload')" class="text-sm font-bold text-slate-400 hover:text-slate-600">Kembali</button>
                                        <button 
                                            wire:click="confirm" 
                                            class="inline-flex items-center gap-2 rounded-2xl bg-emerald-600 px-8 py-3.5 text-sm font-black text-white shadow-xl shadow-emerald-200 transition-all hover:bg-emerald-700 hover:-translate-y-0.5"
                                        >
                                            <span>Konfirmasi & Simpan</span>
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                        </button>
                                    </div>
                                </div>

                            {{-- Step 3: Results --}}
                            @else
                                <div class="space-y-6">
                                    <div class="grid grid-cols-3 gap-4 mb-4">
                                        <div class="bg-emerald-50 rounded-2xl p-4 text-center">
                                            <div class="text-2xl font-black text-emerald-600">{{ collect($logs)->where('status', 'Berhasil')->count() }}</div>
                                            <div class="text-[10px] font-black text-emerald-700 uppercase tracking-widest">Berhasil</div>
                                        </div>
                                        <div class="bg-amber-50 rounded-2xl p-4 text-center">
                                            <div class="text-2xl font-black text-amber-600">{{ collect($logs)->filter(fn($l) => str_contains($l['status'], 'Duplikat'))->count() }}</div>
                                            <div class="text-[10px] font-black text-amber-700 uppercase tracking-widest">Duplikat</div>
                                        </div>
                                        <div class="bg-rose-50 rounded-2xl p-4 text-center">
                                            <div class="text-2xl font-black text-rose-600">{{ collect($logs)->where('status', 'Gagal')->count() }}</div>
                                            <div class="text-[10px] font-black text-rose-700 uppercase tracking-widest">Gagal</div>
                                        </div>
                                    </div>

                                    <div class="max-h-64 overflow-y-auto rounded-2xl border border-slate-100 divide-y divide-slate-100">
                                        @foreach($logs as $log)
                                            <div class="px-4 py-3 flex items-center justify-between text-xs">
                                                <div>
                                                    <span class="font-black text-slate-700">{{ $log['employee_name'] }}</span>
                                                    <span class="text-slate-400 font-medium ml-1">({{ $log['work_date'] }})</span>
                                                </div>
                                                <span class="font-black @if($log['status'] === 'Berhasil') text-emerald-600 @else text-amber-600 @endif">{{ $log['status'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="flex justify-end pt-4">
                                        <button @click="isOpen = false" class="rounded-2xl bg-slate-900 px-8 py-3.5 text-sm font-black text-white shadow-xl transition-all hover:bg-slate-800">Tutup</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div x-data="focusMode()"
     x-show="isOpen"
     @open-focus-mode.window="openMode($event.detail)"
     @focus-mode-finished.window="closeMode()"
     style="display: none;"
     class="fixed inset-0 z-[1100] bg-slate-100 flex flex-col items-center justify-start overflow-hidden">
     
    {{-- Top Navigation Bar --}}
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="-translate-y-full"
         x-transition:enter-end="translate-y-0"
         class="w-full bg-white shadow-sm border-b border-slate-200 sticky top-0 z-20">
        <div class="max-w-6xl mx-auto px-4 lg:px-8 py-3 flex items-center justify-between">
            <button @click="closeMode()" class="h-10 w-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-500 hover:bg-rose-50 hover:text-rose-600 transition-colors">
                <i class="bx bx-x text-2xl"></i>
            </button>
            
            <div class="flex-1 text-center px-4">
                <h2 class="text-sm font-black text-slate-800 tracking-tight">Mode Fokus</h2>
                <div class="mt-1 w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="bg-indigo-600 h-1.5 rounded-full transition-all duration-500 ease-out" 
                         style="width: {{ $totalRecords > 0 ? ($currentStep / $totalRecords) * 100 : 0 }}%"></div>
                </div>
                <p class="text-[10px] font-bold text-slate-400 mt-1 uppercase tracking-widest">
                    {{ $currentStep }} / {{ $totalRecords }} Pegawai
                </p>
            </div>

            <button wire:click="skip" class="h-10 w-10 flex items-center justify-center rounded-xl bg-slate-50 text-slate-500 hover:bg-slate-200 transition-colors">
                <i class="bx bx-skip-next text-2xl"></i>
            </button>
        </div>
    </div>

    {{-- Main Grading Area --}}
    <div class="w-full flex-1 overflow-y-auto custom-scrollbar overflow-x-hidden relative" id="focusModeScroll">
        @if($currentRecord)
            <div class="max-w-6xl mx-auto w-full p-4 lg:p-8 pb-32">
                <div class="flex flex-col lg:flex-row gap-6 lg:gap-10 items-start">
                    
                    {{-- Left Side: Employee Profile Card (Sticky on Desktop) --}}
                    <div class="w-full lg:w-1/3 lg:sticky lg:top-8 shrink-0">
                        <div class="bg-white rounded-3xl p-6 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100 flex flex-col items-center text-center relative overflow-hidden">
                            <div class="absolute inset-x-0 top-0 h-2 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
                    
                    <div class="h-20 w-20 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-500 shadow-inner mb-4 border border-indigo-100/50">
                        <i class="bx bx-user text-4xl"></i>
                    </div>
                    
                    <h3 class="text-xl font-black text-slate-800 tracking-tight">{{ $currentRecord->karyawan->name }}</h3>
                    <p class="text-sm font-semibold text-slate-500 mt-1">{{ $currentRecord->NIK }} &bull; {{ $currentRecord->karyawan->dept_code ?? 'N/A' }}</p>
                    
                    <div class="flex items-center gap-2 mt-3">
                        <span class="px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-bold border border-indigo-100/50 uppercase tracking-wider">
                            {{ $currentRecord->karyawan->employment_scheme }}
                        </span>
                    </div>

                    {{-- Attendance Badges --}}
                    <div class="flex flex-wrap items-center justify-center gap-2 mt-5 w-full pt-5 border-t border-slate-100">
                        <div class="flex-1 min-w-[60px] p-2 rounded-xl bg-rose-50 border border-rose-100/50">
                            <p class="text-[10px] font-bold text-rose-400 uppercase tracking-widest mb-0.5">Alpha</p>
                            <p class="text-lg font-black text-rose-700">{{ $currentRecord->Alpha }}</p>
                        </div>
                        <div class="flex-1 min-w-[60px] p-2 rounded-xl bg-amber-50 border border-amber-100/50">
                            <p class="text-[10px] font-bold text-amber-500 uppercase tracking-widest mb-0.5">Telat</p>
                            <p class="text-lg font-black text-amber-700">{{ $currentRecord->Telat }}</p>
                        </div>
                        <div class="flex-1 min-w-[60px] p-2 rounded-xl bg-sky-50 border border-sky-100/50">
                            <p class="text-[10px] font-bold text-sky-500 uppercase tracking-widest mb-0.5">Izin</p>
                            <p class="text-lg font-black text-sky-700">{{ $currentRecord->Izin }}</p>
                        </div>
                        <div class="flex-1 min-w-[60px] p-2 rounded-xl bg-indigo-50 border border-indigo-100/50">
                            <p class="text-[10px] font-bold text-indigo-400 uppercase tracking-widest mb-0.5">Sakit</p>
                            <p class="text-lg font-black text-indigo-700">{{ $currentRecord->Sakit }}</p>
                        </div>
                    </div>
                        </div>
                    </div>

                    {{-- Right Side: Interactive Grading Grid --}}
                    <div class="w-full lg:w-2/3 space-y-4">
                        @php
                            $fields = $isNewSystem ? $newFieldsConfig : $oldFieldsConfig;
                        @endphp

                    @foreach($fields as $field => $label)
                        <div class="bg-white rounded-3xl p-5 shadow-[0_8px_30px_rgb(0,0,0,0.04)] border border-slate-100">
                            <h4 class="text-sm font-bold text-slate-700 mb-3 ml-1">{{ $label }}</h4>
                            
                            {{-- Grade Buttons --}}
                            <div class="flex items-center justify-between gap-2">
                                @foreach(['A', 'B', 'C', 'D', 'E'] as $grade)
                                    <button wire:click="setGrade('{{ $field }}', '{{ $grade }}')" 
                                            class="flex-1 aspect-square rounded-2xl flex items-center justify-center text-xl font-black transition-all duration-300 {{ ($form[$field] ?? '') === $grade ? 'scale-110 shadow-lg ring-2 ring-indigo-500/50 ring-offset-2' : 'hover:scale-105 hover:bg-slate-100' }}"
                                            style="{{ ($form[$field] ?? '') === $grade ? 'background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white; border: none;' : 'background-color: #f8fafc; color: #64748b; border: 1px solid #f1f5f9;' }}">
                                        {{ $grade }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center h-full p-6 text-center">
                <div class="h-24 w-24 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-500 mb-6">
                    <i class="bx bx-check-double text-5xl"></i>
                </div>
                <h3 class="text-xl font-black text-slate-800 mb-2">Semua Selesai!</h3>
                <p class="text-slate-500 font-medium">Tidak ada lagi pegawai yang perlu dinilai.</p>
                <button @click="closeMode()" class="mt-8 px-8 py-3 bg-slate-900 text-white font-bold rounded-2xl shadow-lg hover:shadow-xl hover:-translate-y-1 transition-all">
                    Kembali ke Dashboard
                </button>
            </div>
        @endif
    </div>

    {{-- Bottom Action Bar --}}
    @if($currentRecord)
    <div x-show="isOpen" 
         x-transition:enter="transition ease-out duration-300 transform"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="fixed bottom-0 inset-x-0 w-full bg-white/80 backdrop-blur-xl border-t border-slate-200/60 pb-safe z-20">
        <div class="max-w-6xl mx-auto p-4 lg:px-8 flex gap-3 lg:justify-end">
            <button wire:click="previous" class="w-14 lg:w-32 h-14 shrink-0 flex items-center justify-center gap-2 rounded-2xl bg-white border border-slate-200 shadow-sm text-slate-600 hover:bg-slate-50 hover:text-indigo-600 font-bold transition-all disabled:opacity-50" {{ $currentStep <= 1 ? 'disabled' : '' }}>
                <i class="bx bx-chevron-left text-3xl"></i>
                <span class="hidden lg:inline">Seblmnya</span>
            </button>
            <button wire:click="saveGrade" class="flex-1 lg:flex-none lg:w-64 h-14 rounded-2xl bg-indigo-600 text-white font-black text-lg shadow-lg shadow-indigo-600/30 hover:bg-indigo-700 hover:shadow-indigo-600/40 hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2 relative overflow-hidden group">
                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
                <span wire:loading.remove wire:target="saveGrade">Simpan & Lanjut</span>
                <span wire:loading wire:target="saveGrade" class="animate-pulse">Menyimpan...</span>
                <i class="bx bx-right-arrow-alt text-2xl group-hover:translate-x-1 transition-transform" wire:loading.remove wire:target="saveGrade"></i>
            </button>
        </div>
    </div>
    @endif

    <style>
        .pb-safe { padding-bottom: env(safe-area-inset-bottom); }
    </style>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('focusMode', () => ({
                isOpen: false,
                
                openMode(detail) {
                    this.isOpen = true;
                    document.body.classList.add('overflow-hidden');
                    
                    // Tell Livewire to boot up and fetch data
                    Livewire.dispatch('focusModeOpened', {
                        type: detail.type,
                        month: detail.month,
                        year: detail.year
                    });
                },
                
                closeMode() {
                    this.isOpen = false;
                    document.body.classList.remove('overflow-hidden');
                    
                    // Reload DataTables in the background
                    if (typeof window.reloadEvaluationTables === 'function') {
                        window.reloadEvaluationTables();
                    } else {
                        document.querySelectorAll('table.dataTable').forEach(t => {
                            const dtInstance = typeof $.fn?.dataTable?.Api === 'function' ? new $.fn.dataTable.Api(t) : null;
                            if (dtInstance) dtInstance.ajax.reload(null, false);
                        });
                    }
                }
            }));
        });
    </script>
</div>

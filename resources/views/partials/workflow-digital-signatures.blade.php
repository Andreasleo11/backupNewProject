<div class="mt-8 border-t border-slate-100 px-6 py-6">
    <div class="mb-4 flex items-center justify-between">
        <h3 class="flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-slate-500">
            <i class="bi bi-file-earmark-check text-indigo-500"></i>
            Digital Certifications
        </h3>
        <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-bold text-slate-500">
            Securely Signed
        </span>
    </div>

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
        @foreach ($record->workflow_signatures as $signature)
            @php
                $status = $signature['status'] ?? 'pending'; // signed, pending, rejected
                $signerName = $signature['name'] ?? 'Waiting...';
                $roleLabel = ucwords(str_replace(['pr-', '-'], [' ', ' '], $signature['step_code']));
                
                // Styling based on status
                $cardClasses = match($status) {
                    'signed' => 'border-slate-200 bg-white hover:border-indigo-200 hover:shadow-lg hover:shadow-indigo-50',
                    'rejected' => 'border-red-200 bg-red-50 hover:border-red-300',
                    'pending' => 'border-slate-200 bg-slate-50 border-dashed opacity-70',
                    default => 'border-slate-200 bg-white'
                };
                
                $textClasses = match($status) {
                    'signed' => 'text-slate-800',
                    'rejected' => 'text-red-800',
                    'pending' => 'text-slate-400',
                    default => 'text-slate-800'
                };
            @endphp

            <div class="group relative flex flex-col items-center justify-center rounded-xl border p-4 transition-all {{ $cardClasses }}">
                
                {{-- Status Badge --}}
                @if ($status === 'rejected')
                    <span class="absolute right-2 top-2 rounded bg-red-100 px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-wide text-red-600">
                        Rejected
                    </span>
                @endif
                
                @if ($signature['is_current'] ?? false)
                    <span class="absolute right-2 top-2 rounded bg-amber-100 px-1.5 py-0.5 text-[8px] font-bold uppercase tracking-wide text-amber-600 animate-pulse">
                        Current
                    </span>
                @endif

                {{-- Signature Image or Placeholder --}}
                <div class="mb-3 flex h-24 w-full items-center justify-center overflow-hidden rounded-lg transition-opacity {{ $status === 'signed' ? 'bg-slate-50 opacity-90 mix-blend-multiply group-hover:opacity-100' : '' }}">
                    @if ($status === 'signed' && $signature['image'])
                        <img src="{{ $signature['image'] }}" alt="Signature" class="max-w-full h-auto max-h-20 object-contain mix-blend-darken">
                    @elseif ($status === 'rejected')
                        <i class="bi bi-x-circle text-3xl text-red-300"></i>
                    @else
                        <i class="bi bi-person-badge text-3xl text-slate-300"></i>
                    @endif
                </div>

                {{-- Signer Info --}}
                <div class="text-center">
                    <p class="text-[10px] font-bold uppercase tracking-wider mb-0.5 {{ $status === 'signed' ? 'text-indigo-600' : 'text-slate-500' }}">
                        {{ $roleLabel }}
                    </p>
                    <p class="text-xs font-black line-clamp-1 {{ $textClasses }}" title="{{ $signerName }}">
                        {{ $signerName }}
                    </p>
                    @if ($status === 'signed' && $signature['at'])
                    <p class="mt-1 text-[9px] font-medium text-slate-400">
                        {{ \Carbon\Carbon::parse($signature['at'])->format('d M Y H:i') }}
                    </p>
                    @endif
                </div>

                {{-- Verified Badge --}}
                @if ($status === 'signed')
                <div class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500 text-white shadow-sm opacity-0 transition-all group-hover:opacity-100 group-hover:scale-110">
                    <i class="bi bi-check-lg text-[10px]"></i>
                </div>
                @endif
            </div>
        @endforeach
    </div>
</div>

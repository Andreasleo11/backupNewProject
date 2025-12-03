@extends('new.layouts.app')

@section('content')
    <div class="min-h-[60vh] flex items-center justify-center bg-slate-50/60">
        <div class="w-full max-w-xl px-4">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200">
                {{-- Header --}}
                <div class="px-6 py-4 border-b border-slate-100">
                    <h1 class="text-base font-semibold text-slate-900">
                        Silahkan Pilih Scenario
                    </h1>
                    <p class="mt-1 text-xs text-slate-500">
                        Pilih scenario produksi yang ingin digunakan untuk melanjutkan ke portal.
                    </p>
                </div>

                {{-- Form --}}
                <form action="{{ route('portal') }}" method="POST" class="px-6 py-5 space-y-4">
                    @csrf

                    <div>
                        <label for="scenario" class="block text-sm font-medium text-slate-700 mb-1">
                            Scenario
                        </label>
                        <select id="scenario" name="scenario"
                            class="block w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm
                                       text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                            <option value="injection">Plastic Injection</option>
                            <option value="second">Second Process</option>
                            <option value="assembly">Assembly</option>
                            <option value="karawang">Karawang</option>
                        </select>
                    </div>

                    <div class="pt-2 flex justify-end">
                        <button type="submit"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold
                                       text-white shadow-sm hover:bg-indigo-700 focus:outline-none
                                       focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Pilih
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

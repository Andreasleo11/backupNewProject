<div x-data="{ openSendMail: false, openSendMailConfirm: false }" @open-mail-modal.window="if ({{ isset($report->has_been_emailed) && $report->has_been_emailed ? 'true' : 'false' }}) openSendMailConfirm = true; else openSendMail = true;">
        {{-- ========= MODAL: SEND MAIL ========= --}}
        <div x-show="openSendMail" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
            @keydown.escape.window="openSendMail = false">
            <div class="absolute inset-0" @click="openSendMail = false"></div>

            <div class="relative z-10 w-full max-w-3xl mx-4 rounded-2xl bg-white shadow-xl border border-slate-100">
                <form action="{{ route('verification.sendEmail', $report->id) }}" method="post"
                    enctype="multipart/form-data">
                    @csrf
                    @method('POST')

                    {{-- Header --}}
                    <div class="flex items-start justify-between px-5 pt-5 pb-2">
                        <h5 class="text-sm font-semibold text-slate-900">
                            Send mail
                        </h5>
                        <button type="button"
                            class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400
                                       hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2
                                       focus:ring-indigo-500 focus:ring-offset-1"
                            @click="openSendMail = false">
                            <span class="sr-only">Close</span>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                    d="M6 6l12 12M18 6L6 18" />
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="px-5 pb-4 pt-1 space-y-4 text-sm text-slate-700">
                        {{-- From --}}
                        <div class="space-y-1">
                            <label for="fromInput" class="block text-xs font-medium text-slate-600">
                                From
                            </label>
                            <input id="fromInput" type="text"
                                class="block w-full rounded-md border-slate-300 bg-slate-50 px-3 py-2 text-sm
                                          text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                value="{{ Auth::user()->email }}" readonly disabled>
                        </div>

                        {{-- To --}}
                        <div class="space-y-1">
                            <label for="toInput" class="block text-xs font-medium text-slate-600">
                                To
                            </label>
                            <textarea name="to" id="toInput" rows="4"
                                class="semicolon-input block w-full rounded-md border-slate-300 px-3 py-2 text-xs
                                             text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                spellcheck="false">andriani@daijo.co.id; sriyati@daijo.co.id; anik@daijo.co.id; albert@daijo.co.id; nurul@daijo.co.id; riki@daijo.co.id; rony@daijo.co.id; sukiyono@daijo.co.id; budiman@daijo.co.id; heri@daijo.co.id; leny@daijo.co.id; popon@daijo.co.id; sukur@daijo.co.id; supri@daijo.co.id; wiji@daijo.co.id; agus_s@daijo.co.id; catur@daijo.co.id; yeyen@daijo.co.id; </textarea>
                            <p class="mt-1 text-[11px] text-slate-400">
                                Pisahkan email dengan titik koma <code>;</code>. Spasi akan ditambahkan otomatis.
                            </p>
                        </div>

                        {{-- CC --}}
                        <div class="space-y-1">
                            <label for="ccInput" class="block text-xs font-medium text-slate-600">
                                CC
                            </label>
                            <textarea name="cc" id="ccInput" rows="4"
                                class="semicolon-input block w-full rounded-md border-slate-300 px-3 py-2 text-xs
                                             text-slate-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                spellcheck="false">deni_qc@daijo.co.id; beata.qc@daijo.co.id; erizal@daijo.co.id; nurul_hidayati@daijo.co.id; herlina@daijo.co.id; srie@daijo.co.id; bayu@daijo.co.id; ekoqc@daijo.co.id; QA01_daijo@daijo.co.id; qa02_daijo@daijo.co.id; umi@daijo.co.id; yuli@daijo.co.id; emma@daijo.co.id; abdulrahim@daijo.co.id; raditya_qc@daijo.co.id; naya@daijo.co.id; adi@daijo.co.id; dian@daijo.co.id; dedi.agung@daijo.co.id; </textarea>
                        </div>

                        {{-- Subject --}}
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-600">
                                Subject
                            </label>
                            <input type="text" name="subject" placeholder="Subject for the email"
                                class="block w-full rounded-md border-slate-300 px-3 py-2 text-sm text-slate-700
                                          shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>

                        {{-- Body --}}
                        <div class="space-y-1">
                            <label class="block text-xs font-medium text-slate-600">
                                Body
                            </label>
                            <textarea name="body" rows="4" placeholder="Body for the email"
                                class="block w-full rounded-md border-slate-300 px-3 py-2 text-sm text-slate-700
                                             shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                        </div>

                        {{-- Attachments --}}
                        <div class="space-y-2">
                            <div class="text-xs font-medium text-slate-600">
                                Attachments
                            </div>

                            @php
                                $fileName = 'verification-report-' . $report->id . '.pdf';
                                $filePath = Storage::url('pdfs/' . $fileName);
                                $fileExists = file_exists(public_path('storage/pdfs/' . $fileName));
                            @endphp

                            @if ($fileExists)
                                <a href="{{ asset($filePath) }}" download="{{ $fileName }}" class="block">
                                    <div
                                        class="flex items-center justify-between rounded-lg border border-slate-200
                                                bg-slate-50 px-3 py-2 text-xs text-slate-600 hover:bg-slate-100">
                                        <span>{{ $fileName }}</span>
                                        <span class="text-[11px] text-slate-400">Click to download</span>
                                    </div>
                                </a>
                            @else
                                <div class="mt-1 text-xs font-semibold text-slate-500">
                                    You need to export the document first
                                </div>
                            <a href="{{ route('verification.download', $report->id) }}"
                                    class="inline-flex items-center rounded-md border border-indigo-600 bg-white
                                          px-3 py-1.5 text-xs font-semibold text-indigo-700 shadow-sm hover:bg-indigo-50
                                          focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                                    Export PDF
                                </a>
                            @endif

                            @if(isset($report->files))
                                @foreach ($report->files as $file)
                                    @php
                                        $filename = basename($file->name);
                                        $filepath = Storage::url('files/' . $filename);
                                    @endphp
                                    <a href="{{ $filepath }}" download="{{ $filename }}" class="block">
                                        <div
                                            class="flex items-center justify-between rounded-lg border border-slate-200
                                                    bg-slate-50 px-3 py-2 text-xs text-slate-600 hover:bg-slate-100">
                                            <span>{{ $filename }}</span>
                                            <span class="text-[11px] text-slate-400">Click to download</span>
                                        </div>
                                    </a>
                                @endforeach
                            @endif
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-2 border-t border-slate-100 px-5 py-3">
                        <button type="button"
                            class="inline-flex items-center rounded-md border border-slate-300 bg-white
                                       px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50
                                       focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1"
                            @click="openSendMail = false">
                            Close
                        </button>
                        <button type="submit"
                            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs
                                       font-semibold text-white shadow-sm hover:bg-indigo-700
                                       focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                            Send
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========= MODAL: SEND MAIL CONFIRMATION ========= --}}
        <div x-show="openSendMailConfirm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
            @keydown.escape.window="openSendMailConfirm = false">
            <div class="absolute inset-0" @click="openSendMailConfirm = false"></div>

            <div class="relative z-10 w-full max-w-md mx-4 rounded-2xl bg-white shadow-xl border border-slate-100">
                <div class="px-5 pt-5 pb-2 flex items-start justify-between gap-3">
                    <h5 class="text-sm font-semibold text-slate-900">
                        Send Email Confirmation
                    </h5>
                    <button type="button"
                        class="inline-flex h-7 w-7 items-center justify-center rounded-full text-slate-400
                                   hover:bg-slate-100 hover:text-slate-600 focus:outline-none focus:ring-2
                                   focus:ring-indigo-500 focus:ring-offset-1"
                        @click="openSendMailConfirm = false">
                        <span class="sr-only">Close</span>
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="h-4 w-4" fill="none"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.6"
                                d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>

                <div class="px-5 pt-1 pb-4 text-sm text-slate-600 space-y-2">
                    <p>You have already sent this report before.</p>
                    <p>Are you sure you want to send it again?</p>
                </div>

                <div class="px-5 pb-5 pt-2 flex items-center justify-end gap-2 border-t border-slate-100">
                    <button type="button"
                        class="inline-flex items-center rounded-md border border-slate-300 bg-white
                                   px-3 py-1.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50
                                   focus:outline-none focus:ring-2 focus:ring-slate-300 focus:ring-offset-1"
                        @click="openSendMailConfirm = false">
                        Close
                    </button>

                    <button type="button"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs
                                   font-semibold text-white shadow-sm hover:bg-indigo-700
                                   focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1"
                        @click="openSendMailConfirm = false; openSendMail = true">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
</div>

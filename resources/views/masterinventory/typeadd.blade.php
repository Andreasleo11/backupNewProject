@extends('new.layouts.app')

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        {{-- Page header --}}
        <div class="mb-6">
            <h1 class="text-xl sm:text-2xl font-semibold text-gray-900">Type List</h1>
            <p class="mt-1 text-sm text-gray-500">
                Kelola master jenis <span class="font-medium">hardware</span> dan <span class="font-medium">software</span>
                yang digunakan pada Master Inventory.
            </p>
        </div>

        {{-- Hardware Types --}}
        <div class="mb-6 bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
            <div class="px-4 py-3 sm:px-6 sm:py-4 flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Hardware Types</h2>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Kategori hardware (PC, monitor, printer, dll).
                    </p>
                </div>
                <button id="openAddHardwareTypeModal" type="button"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    Add Hardware Type
                </button>
            </div>

            <div class="px-4 pb-4 sm:px-6 sm:pb-5">
                @if ($hardwareTypes->isEmpty())
                    <div
                        class="rounded-md border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center text-sm text-gray-600">
                        Tidak ada hardware type. Tambahkan minimal satu jenis untuk memulai.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Type Name
                                    </th>
                                    <th
                                        class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($hardwareTypes as $type)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 text-gray-900">
                                            {{ $type->name }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button type="button"
                                                class="delete-btn inline-flex items-center rounded-md bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-red-500"
                                                data-id="{{ $type->id }}" data-type="hardware">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- Software Types --}}
        <div class="mb-6 bg-white rounded-lg shadow-sm ring-1 ring-gray-200">
            <div class="px-4 py-3 sm:px-6 sm:py-4 flex items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-semibold text-gray-900">Software Types</h2>
                    <p class="mt-0.5 text-xs text-gray-500">
                        Kategori software (OS, Office, antivirus, dll).
                    </p>
                </div>
                <button id="openAddSoftwareTypeModal" type="button"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs sm:text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1">
                    Add Software Type
                </button>
            </div>

            <div class="px-4 pb-4 sm:px-6 sm:pb-5">
                @if ($softwareTypes->isEmpty())
                    <div
                        class="rounded-md border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-center text-sm text-gray-600">
                        Tidak ada software type. Tambahkan kategori untuk lisensi yang digunakan.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-3 py-2 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Type Name
                                    </th>
                                    <th
                                        class="px-3 py-2 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($softwareTypes as $type)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-3 py-2 text-gray-900">
                                            {{ $type->name }}
                                        </td>
                                        <td class="px-3 py-2 text-right">
                                            <button type="button"
                                                class="delete-btn inline-flex items-center rounded-md bg-red-600 px-2.5 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-red-500"
                                                data-id="{{ $type->id }}" data-type="software">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Add Hardware Type Modal --}}
    <div id="addHardwareTypeModal" class="fixed inset-0 z-40 hidden" aria-labelledby="addHardwareTypeModalLabel"
        aria-hidden="true">
        {{-- overlay --}}
        <div class="absolute inset-0 bg-gray-900/40"></div>

        {{-- modal content wrapper (di atas overlay) --}}
        <div class="relative z-10 flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-md rounded-lg bg-white shadow-lg ring-1 ring-gray-200">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                    <h5 class="text-sm font-semibold text-gray-900" id="addHardwareTypeModalLabel">
                        Add New Hardware Type
                    </h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600"
                        data-close-modal="addHardwareTypeModal">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 8.586L4.293 2.879 2.879 4.293 8.586 10l-5.707 5.707 1.414 1.414L10 11.414l5.707 5.707 1.414-1.414L11.414 10l5.707-5.707-1.414-1.414L10 8.586z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <div class="px-4 py-4">
                    <form id="addHardwareTypeForm" class="space-y-3">
                        <div>
                            <label for="hardwareTypeName" class="block text-sm font-medium text-gray-700">
                                Type Name
                            </label>
                            <input type="text" id="hardwareTypeName" name="name" required
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 px-3 py-2 text-sm shadow-sm
                                      focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-gray-300"
                                data-close-modal="addHardwareTypeModal">
                                Cancel
                            </button>
                            <button type="submit"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                Add Type
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div id="addSoftwareTypeModal" class="fixed inset-0 z-40 hidden" aria-labelledby="addSoftwareTypeModalLabel"
        aria-hidden="true">
        <div class="absolute inset-0 bg-gray-900/40"></div>

        <div class="relative z-10 flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-md rounded-lg bg-white shadow-lg ring-1 ring-gray-200">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                    <h5 class="text-sm font-semibold text-gray-900" id="addSoftwareTypeModalLabel">
                        Add New Software Type
                    </h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600"
                        data-close-modal="addSoftwareTypeModal">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 8.586L4.293 2.879 2.879 4.293 8.586 10l-5.707 5.707 1.414 1.414L10 11.414l5.707 5.707 1.414-1.414L11.414 10l5.707-5.707-1.414-1.414L10 8.586z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <div class="px-4 py-4">
                    <form id="addSoftwareTypeForm" class="space-y-3">
                        <div>
                            <label for="softwareTypeName" class="block text-sm font-medium text-gray-700">
                                Type Name
                            </label>
                            <input type="text" id="softwareTypeName" name="name" required
                                class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 px-3 py-2 text-sm shadow-sm
                                      focus:bg-white focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        <div class="flex justify-end gap-2 pt-2">
                            <button type="button"
                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-gray-300"
                                data-close-modal="addSoftwareTypeModal">
                                Cancel
                            </button>
                            <button type="submit"
                                class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                                Add Type
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <div id="deleteModal" class="fixed inset-0 z-40 hidden" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="absolute inset-0 bg-gray-900/40"></div>

        <div class="relative z-10 flex min-h-full items-center justify-center p-4">
            <div class="w-full max-w-md rounded-lg bg-white shadow-lg ring-1 ring-gray-200">
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3">
                    <h5 class="text-sm font-semibold text-gray-900" id="deleteModalLabel">
                        Confirm Deletion
                    </h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600" data-close-modal="deleteModal">
                        <span class="sr-only">Close</span>
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M10 8.586L4.293 2.879 2.879 4.293 8.586 10l-5.707 5.707 1.414 1.414L10 11.414l5.707 5.707 1.414-1.414L11.414 10l5.707-5.707-1.414-1.414L10 8.586z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
                <div class="px-4 py-4 text-sm text-gray-700">
                    Are you sure you want to delete this type?
                </div>
                <div class="flex justify-end gap-2 px-4 py-3 border-t border-gray-100">
                    <button type="button"
                        class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-1.5 text-xs font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-1 focus:ring-gray-300"
                        data-close-modal="deleteModal">
                        Cancel
                    </button>
                    <button type="button" id="confirmDelete"
                        class="inline-flex items-center rounded-md bg-red-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-1 focus:ring-red-500">
                        Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const csrfToken = '{{ csrf_token() }}';

            // Helpers to open/close modal
            function openModal(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
            }

            function closeModal(id) {
                const el = document.getElementById(id);
                if (!el) return;
                el.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            // Close modal buttons (data-close-modal="id")
            document.querySelectorAll('[data-close-modal]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const target = btn.getAttribute('data-close-modal');
                    closeModal(target);
                });
            });

            // Open add modals
            const openHardwareBtn = document.getElementById('openAddHardwareTypeModal');
            const openSoftwareBtn = document.getElementById('openAddSoftwareTypeModal');

            if (openHardwareBtn) {
                openHardwareBtn.addEventListener('click', () => openModal('addHardwareTypeModal'));
            }
            if (openSoftwareBtn) {
                openSoftwareBtn.addEventListener('click', () => openModal('addSoftwareTypeModal'));
            }

            // Add Hardware Type (AJAX)
            const addHardwareForm = document.getElementById('addHardwareTypeForm');
            if (addHardwareForm) {
                addHardwareForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const name = document.getElementById('hardwareTypeName').value.trim();
                    if (!name) return;

                    try {
                        const res = await fetch('{{ route('add.hardware.type') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                name
                            }),
                        });

                        const data = await res.json();
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert('Error adding type');
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Error adding type');
                    }
                });
            }

            // Add Software Type (AJAX)
            const addSoftwareForm = document.getElementById('addSoftwareTypeForm');
            if (addSoftwareForm) {
                addSoftwareForm.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const name = document.getElementById('softwareTypeName').value.trim();
                    if (!name) return;

                    try {
                        const res = await fetch('{{ route('add.software.type') }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                name
                            }),
                        });

                        const data = await res.json();
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert('Error adding type');
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Error adding type');
                    }
                });
            }

            // Delete logic
            let deleteId = null;
            let deleteType = null;

            document.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    deleteId = btn.getAttribute('data-id');
                    deleteType = btn.getAttribute('data-type');
                    openModal('deleteModal');
                });
            });

            const confirmDeleteBtn = document.getElementById('confirmDelete');
            if (confirmDeleteBtn) {
                confirmDeleteBtn.addEventListener('click', async () => {
                    if (!deleteId || !deleteType) return;

                    try {
                        const res = await fetch('{{ route('delete.type') }}', {
                            method: 'DELETE',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({
                                id: deleteId,
                                type: deleteType,
                            }),
                        });

                        const data = await res.json();
                        if (data.success) {
                            window.location.reload();
                        } else {
                            alert('Error deleting type');
                        }
                    } catch (error) {
                        console.error(error);
                        alert('Error deleting type');
                    }
                });
            }
        });
    </script>
@endpush

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8" x-data="{ 
    modals: {
        'add-category': false,
        'edit-category': false
    },
    openModal(name) {
        this.modals[name] = true;
        document.body.classList.add('overflow-hidden');
    },
    closeModal(name) {
        this.modals[name] = false;
        document.body.classList.remove('overflow-hidden');
    }
}" 
@open-modal.window="openModal($event.detail)"
@close-modal.window="closeModal($event.detail)">

    {{-- Header Section --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-black text-slate-800 tracking-tight">Defect Categories</h1>
            <p class="text-sm text-slate-500 mt-1 font-medium">Manage defect categories for Verification and QAQC reports.</p>
        </div>
        
        <button @click="openModal('add-category')" 
            class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 text-white text-sm font-bold shadow-lg shadow-indigo-200 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all">
            <i class="bi bi-plus-lg"></i>
            Add New Category
        </button>
    </div>

    {{-- Alerts --}}
    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
            x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="mb-6 flex items-center justify-between p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-800 shadow-sm">
            <div class="flex items-center gap-3">
                <i class="bi bi-check-circle-fill text-emerald-500 text-lg"></i>
                <span class="text-sm font-bold">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-400 hover:text-emerald-600 transition-colors">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    @endif

    {{-- Table Card --}}
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100 bg-slate-50/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="relative max-w-md w-full">
                <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" wire:model.live.debounce.300ms="search" 
                    placeholder="Search categories..." 
                    class="w-full pl-11 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all shadow-sm">
            </div>
            
            <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">
                Showing {{ $defectCategories->firstItem() ?? 0 }} - {{ $defectCategories->lastItem() ?? 0 }} of {{ $defectCategories->total() }}
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead>
                    <tr class="text-[10px] uppercase tracking-widest text-slate-400 font-bold border-b border-slate-100 bg-slate-50/50">
                        <th class="px-6 py-4 w-20 text-center">#</th>
                        <th class="px-6 py-4">Category Name</th>
                        <th class="px-6 py-4">Created At</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($defectCategories as $category)
                        <tr class="group hover:bg-slate-50/50 transition-all">
                            <td class="px-6 py-4 text-center">
                                <span class="text-xs font-bold text-slate-400">{{ ($defectCategories->currentPage() - 1) * $defectCategories->perPage() + $loop->iteration }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-slate-700">{{ $category->name }}</span>
                            </td>
                            <td class="px-6 py-4 text-xs text-slate-400 font-medium">
                                {{ $category->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2 transition-all">
                                    <button wire:click="edit({{ $category->id }})" 
                                        class="h-9 w-9 flex items-center justify-center rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm"
                                        title="Edit Category">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    <button onclick="confirm('Are you sure you want to delete this category?') || event.stopImmediatePropagation()" 
                                        wire:click="delete({{ $category->id }})" 
                                        class="h-9 w-9 flex items-center justify-center rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all shadow-sm"
                                        title="Delete Category">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="h-16 w-16 bg-slate-100 rounded-full flex items-center justify-center text-slate-400 mb-4">
                                        <i class="bi bi-folder2-open text-3xl"></i>
                                    </div>
                                    <h3 class="text-slate-800 font-bold">No categories found</h3>
                                    <p class="text-slate-500 text-sm mt-1">Try adjusting your search or add a new category.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($defectCategories->hasPages())
            <div class="px-6 py-4 bg-slate-50/50 border-t border-slate-100">
                {{ $defectCategories->links() }}
            </div>
        @endif
    </div>

    {{-- Add Category Modal --}}
    <div x-show="modals['add-category']" x-cloak 
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
        @keydown.escape.window="closeModal('add-category')">
        
        <div class="absolute inset-0" @click="closeModal('add-category')"></div>
        
        <div class="relative w-full max-w-md transform transition-all" 
            x-transition:enter="ease-out duration-300" 
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
            
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Add New Category</h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Create a new defect classification</p>
                    </div>
                    <button @click="closeModal('add-category')" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="bi bi-x-lg text-lg"></i>
                    </button>
                </div>

                <form wire:submit.prevent="store" class="p-8 space-y-6">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Category Name</label>
                        <input type="text" wire:model="name" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('name') border-rose-500 @enderror"
                            placeholder="e.g. Scratches, Burrs, Missing Parts">
                        @error('name') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" @click="closeModal('add-category')" 
                            class="flex-1 px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-all">
                            Cancel
                        </button>
                        <button type="submit" 
                            class="flex-1 px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all">
                            Save Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Category Modal --}}
    <div x-show="modals['edit-category']" x-cloak 
        class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4"
        @keydown.escape.window="closeModal('edit-category')">
        
        <div class="absolute inset-0" @click="closeModal('edit-category')"></div>
        
        <div class="relative w-full max-w-md transform transition-all" 
            x-transition:enter="ease-out duration-300" 
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
            
            <div class="bg-white rounded-3xl shadow-2xl border border-slate-100 overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h3 class="text-xl font-bold text-slate-800">Edit Category</h3>
                        <p class="text-xs text-slate-500 font-medium mt-0.5">Modify category details</p>
                    </div>
                    <button @click="closeModal('edit-category')" class="text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="bi bi-x-lg text-lg"></i>
                    </button>
                </div>

                <form wire:submit.prevent="update" class="p-8 space-y-6">
                    <div>
                        <label class="block text-xs font-black uppercase tracking-widest text-slate-400 mb-2">Category Name</label>
                        <input type="text" wire:model="editingName" 
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 text-slate-700 font-medium focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all @error('editingName') border-rose-500 @enderror"
                            placeholder="Category name">
                        @error('editingName') <p class="mt-1 text-xs font-bold text-rose-500">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" @click="closeModal('edit-category')" 
                            class="flex-1 px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-all">
                            Cancel
                        </button>
                        <button type="submit" 
                            class="flex-1 px-6 py-3 rounded-xl bg-indigo-600 text-white font-bold shadow-lg shadow-indigo-100 hover:bg-indigo-700 hover:-translate-y-0.5 transition-all">
                            Update Category
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

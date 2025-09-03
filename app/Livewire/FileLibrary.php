<?php

namespace App\Livewire;

use App\Models\Tag;
use App\Models\Upload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Illuminate\Support\Str;

class FileLibrary extends Component
{
    use WithPagination, WithFileUploads;

    protected $paginationTheme = "bootstrap";

    #[Url(as: "q")]
    public string $search = "";

    #[Url(as: "type")]
    public string $type = "all"; // all,image,pdf,doc,sheet,video,audio,archive,other

    #[Url(as: "sort")]
    public string $sortField = "created_at";

    #[Url(as: "dir")]
    public string $sortDirection = "desc";

    public int $perPage = 10;

    /** @var array<int> */
    public array $checked = [];

    // Uploading
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile[] */
    public array $newFiles = [];

    // Rename
    public ?Upload $selected = null;
    public string $newName = "";
    public bool $showRename = false;

    // Replace file
    public bool $showReplace = false;
    public $replacement; // TemporaryUploadedFile

    public string $viewMode = "grid"; // or 'table'
    public bool $selectPage = false; // header checkbox (you already wired this)

    public bool $selectAllResults = false;

    public array $tagsFilter = [];
    public string $tagSearch = "";
    public int $tagLimit = 5;

    public string $newTags = "";
    public bool $showTagModal = false;

    public bool $showRemoveTagModal = false;
    public array $removeTagIds = [];

    public bool $tagDropdownOpen = false;

    protected $queryString = [
        "search" => ["except" => ""],
        "type" => ["except" => "all"],
        "sortField" => ["except" => "created_at"],
        "sortDirection" => ["except" => "desc"],
    ];

    protected $rules = [
        "newFiles.*" =>
            "file|max:20480|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,csv,mp4,mp3,zip,rar",
        "newName" => "required|string|min:1|max:200",
        "replacement" =>
            "nullable|file|max:20480|mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,csv,mp4,mp3,zip,rar",
    ];

    protected function filteredQuery()
    {
        $q = Upload::query();

        if ($this->search !== "") {
            $q->where("original_name", "like", "%" . $this->search . "%");
        }

        if ($this->type !== "all") {
            $q->where(function ($w) {
                $t = $this->type;
                $w->when($t === "image", fn($x) => $x->where("mime_type", "like", "image/%"))
                    ->when($t === "pdf", fn($x) => $x->where("mime_type", "application/pdf"))
                    ->when(
                        $t === "doc",
                        fn($x) => $x->where(function ($y) {
                            $y->where("mime_type", "like", "%word%")
                                ->orWhere("mime_type", "like", "%rtf%")
                                ->orWhere("mime_type", "like", "text/%");
                        }),
                    )
                    ->when(
                        $t === "sheet",
                        fn($x) => $x->where(function ($y) {
                            $y->where("mime_type", "like", "%spreadsheet%")
                                ->orWhere("mime_type", "like", "%excel%")
                                ->orWhere("mime_type", "like", "%csv%");
                        }),
                    )
                    ->when($t === "video", fn($x) => $x->where("mime_type", "like", "video/%"))
                    ->when($t === "audio", fn($x) => $x->where("mime_type", "like", "audio/%"))
                    ->when(
                        $t === "archive",
                        fn($x) => $x->where(function ($y) {
                            $y->where("mime_type", "like", "%zip%")
                                ->orWhere("mime_type", "like", "%rar%")
                                ->orWhere("mime_type", "like", "%7z%");
                        }),
                    )
                    ->when(
                        $t === "other",
                        fn($x) => $x
                            ->whereNot("mime_type", "like", "image/%")
                            ->whereNot("mime_type", "application/pdf")
                            ->whereNot("mime_type", "like", "%word%")
                            ->whereNot("mime_type", "like", "%rtf%")
                            ->whereNot("mime_type", "like", "text/%")
                            ->whereNot("mime_type", "like", "%spreadsheet%")
                            ->whereNot("mime_type", "like", "%excel%")
                            ->whereNot("mime_type", "like", "%csv%")
                            ->whereNot("mime_type", "like", "video/%")
                            ->whereNot("mime_type", "like", "audio/%"),
                    );
            });
        }

        if (!empty($this->tagsFilter)) {
            // match ANY of the selected tags; switch to ->whereHas(...) multiple times if you want ALL
            $q->whereHas("tags", fn($r) => $r->whereIn("slug", $this->tagsFilter));
        }

        return $q->orderBy($this->sortField, $this->sortDirection);
    }

    protected function currentPageIds(): array
    {
        $page = $this->getPage();
        return $this->filteredQuery()
            ->forPage($page, $this->perPage)
            ->pluck("id")
            ->map(fn($id) => (string) $id)
            ->values()
            ->all();
    }

    protected function slugify(string $name): string
    {
        $slug = Str::slug(mb_strtolower(trim($name)));
        return Str::limit($slug, 60, "");
    }

    public function toggleTagFilter(string $slug): void
    {
        $selected = collect($this->tagsFilter);

        $this->tagsFilter = $selected->contains($slug)
            ? $selected->reject(fn($s) => $s === $slug)->values()->all()
            : $selected->push($slug)->values()->all();

        // keep UI consistent
        $this->resetPage();
        $this->selectAllResults = false;
        $this->checked = [];
    }

    public function getTopTagsProperty()
    {
        return Tag::withCount(["uploads as uses"])
            ->orderByDesc("uses")
            ->take($this->tagLimit)
            ->get();
    }

    public function getAllTagsProperty()
    {
        return Tag::query()
            ->when(
                $this->tagSearch !== "",
                fn($q) => $q->where("name", "like", "%{$this->tagSearch}%"),
            )
            ->orderBy("name")
            ->limit(300)
            ->get();
    }

    public function getAllTagsCountProperty()
    {
        return Tag::count();
    }

    public function updatedTagsFilter(): void
    {
        $this->resetPage();
        $this->selectAllResults = false;
        $this->checked = [];
    }

    public function updatedSelectPage($value): void
    {
        if ($value) {
            $this->checked = $this->currentPageIds();
        } else {
            // user unticked header => cancel all selection modes
            $this->selectAllResults = false;
            $this->checked = [];
        }
    }

    public function selectAllResultsAction(): void
    {
        // Turn on all-results mode and visually check the current page
        $this->selectAllResults = true;
        $this->selectPage = true;
        $this->checked = $this->getAllResultsIds();
    }

    protected function getAllResultsIds(): array
    {
        return $this->filteredQuery()->pluck("id")->map(fn($id) => (string) $id)->all();
    }

    public function updatedChecked(): void
    {
        if ($this->selectAllResults) {
            // While in "all results" mode, keep header checked no matter what
            $this->selectPage = true;
            return;
        }

        // Normal page mode: header checked if entire page is selected
        $this->selectPage = count($this->checked) === count($this->currentPageIds());
    }

    protected function applySelectionToCurrentPage(): void
    {
        if ($this->selectAllResults) {
            $this->selectPage = true;
            $this->checked = $this->getAllResultsIds();
        } else {
            // keep whatever the user had on this page (or recompute)
            $this->selectPage = count($this->checked) === count($this->currentPageIds());
        }
    }

    // If you allow switching between grid/table:
    public function updatedViewMode()
    {
        $this->applySelectionToCurrentPage();
    }

    // Optional: clear selection when filters/pagination change
    protected function resetSelection(): void
    {
        $this->checked = [];
        $this->selectPage = false;
    }

    public function updatingSearch()
    {
        $this->resetPage();
        $this->selectAllResults = false;
        $this->resetSelection();
    }
    public function updatingType()
    {
        $this->resetPage();
        $this->selectAllResults = false;
        $this->resetSelection();
    }
    public function updatingPerPage()
    {
        $this->resetPage();
        $this->selectAllResults = false;
        $this->resetSelection();
    }

    public function updatedPage()
    {
        $this->applySelectionToCurrentPage();
    }

    public function updated($name, $value): void
    {
        if ($name == "page" && $this->selectAllResults) {
            $this->selectPage = true;
            $this->checked = $this->getAllResultsIds();
        }
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === "asc" ? "desc" : "asc";
        } else {
            $this->sortField = $field;
            $this->sortDirection = "asc";
        }

        $this->resetPage();
        if ($this->selectAllResults) {
            $this->selectPage = true;
            $this->checked = $this->getAllResultsIds();
        } else {
            $this->resetSelection();
        }
    }

    public function store(): void
    {
        $this->validateOnly("newFiles.*");

        foreach ($this->newFiles as $file) {
            $path = $file->store("uploads", "public");

            Upload::create([
                "original_name" => $file->getClientOriginalName(),
                "path" => $path,
                "mime_type" => $file->getMimeType(),
                "size" => $file->getSize(),
                "disk" => "public",
                "uploaded_by" => Auth::id(),
            ]);
        }

        $this->reset("newFiles");
        $this->dispatch("toast", message: "Upload selesai.");
        $this->resetPage();
    }

    public function confirmRename(int $id): void
    {
        $this->selected = Upload::findOrFail($id);
        $this->newName = pathinfo($this->selected->original_name, PATHINFO_FILENAME);
        $this->showRename = true;
    }

    public function rename(): void
    {
        $this->validateOnly("newName");
        $upload = $this->selected;
        if (!$upload) {
            return;
        }

        $ext = pathinfo($upload->original_name, PATHINFO_EXTENSION);
        $dir = pathinfo($upload->path, PATHINFO_DIRNAME);
        // Keep spaces, dots and dashes; replace illegal characters with a dash
        $newBase = preg_replace('/[\/\\\\\?\%\*\:\|"<>]+/', "-", trim($this->newName));
        // Optional: collapse multiple spaces
        $newBase = preg_replace("/\s+/", " ", $newBase);
        $newPath = $dir . "/" . $newBase . ($ext ? ".{$ext}" : "");

        if ($newPath !== $upload->path) {
            if (Storage::disk($upload->disk)->exists($newPath)) {
                $this->addError("newName", "Nama file sudah ada.");
                return;
            }
            Storage::disk($upload->disk)->move($upload->path, $newPath);
        }

        $upload->update([
            "original_name" => $newBase . ($ext ? ".{$ext}" : ""),
            "path" => $newPath,
        ]);

        $this->showRename = false;
        $this->selected = null;
        $this->dispatch("toast", message: "Nama file diperbarui.");
    }

    public function confirmReplace(int $id): void
    {
        $this->selected = Upload::findOrFail($id);
        $this->replacement = null;
        $this->showReplace = true;
    }

    public function replace(): void
    {
        $this->validateOnly("replacement");

        $upload = $this->selected;
        if (!$upload || !$this->replacement) {
            return;
        }

        // Remove old file if exists
        if (Storage::disk($upload->disk)->exists($upload->path)) {
            Storage::disk($upload->disk)->delete($upload->path);
        }

        // Store new file under same folder with hashed name
        $dir = pathinfo($upload->path, PATHINFO_DIRNAME);
        $newPath = $this->replacement->store($dir, $upload->disk);

        $upload->update([
            "original_name" => $this->replacement->getClientOriginalName(),
            "path" => $newPath,
            "mime_type" => $this->replacement->getMimeType(),
            "size" => $this->replacement->getSize(),
        ]);

        $this->showReplace = false;
        $this->selected = null;
        $this->dispatch("toast", message: "File berhasil diganti.");
    }

    public function deleteOne(int $id): void
    {
        $u = Upload::findOrFail($id);
        if (Storage::disk($u->disk)->exists($u->path)) {
            Storage::disk($u->disk)->delete($u->path);
        }
        $u->delete();
        $this->dispatch("toast", message: "File berhasil dihapus.");
        $this->resetPage();
    }

    public function deleteSelected(): void
    {
        $idsQuery = $this->selectAllResults
            ? $this->filteredQuery()->select("id")
            : Upload::query()
                ->whereIn("id", collect($this->checked)->map(fn($id) => (int) $id)->all())
                ->select("id");

        $this->checked = [];
        $this->selectAllResults = false;
        $this->selectPage = false;

        $idsQuery->chunkById(500, function ($chunk) {
            foreach ($chunk as $row) {
                $u = Upload::find($row->id);
                if (!$u) {
                    continue;
                }
                if (Storage::disk($u->disk)->exists($u->path)) {
                    Storage::disk($u->disk)->delete($u->path);
                }
                $u->delete();
            }
        });

        $this->dispatch("toast", message: "File terpilih dihapus.");
        $this->resetPage();
    }

    public function removeTagFromItem(int $uploadId, int $tagId): void
    {
        if ($u = Upload::with("tags")->find($uploadId)) {
            $u->tags()->detach($tagId);
            // Optional: delete orphan tags
            Tag::whereDoesntHave("uploads")->whereKey($tagId)->delete();

            $this->dispatch("toast", message: "Tag berhasil dihapus dari file.");
        }
    }

    public function addTagsToSelection(): void
    {
        $names = collect(preg_split('/[,\n]/', $this->newTags ?? ""))
            ->map(fn($name) => trim($name))
            ->filter()
            ->unique()
            ->take(25); // guardtail

        if ($names->isEmpty()) {
            $this->dispatch("toast", message: "Tidak ada tag yang ditambahkan.");
            return;
        }

        // upsert/find tags
        $tags = $names->map(function ($name) {
            $slug = $this->slugify($name);
            return Tag::firstOrCreate(
                ["slug" => $slug],
                ["name" => $name, "created_by" => Auth::id()],
            );
        });

        $query = $this->selectAllResults
            ? $this->filteredQuery()->select("id")
            : Upload::query()
                ->whereKey(array_map("intval", $this->checked))
                ->select("id");

        // attach in chunks
        $tagIds = $tags->pluck("id")->all();
        $query->chunkById(500, function ($chunk) use ($tagIds) {
            $ids = $chunk->pluck("id")->all();
            Upload::whereKey($ids)
                ->get()
                ->each(function (Upload $u) use ($tagIds) {
                    $u->tags()->syncWithoutDetaching($tagIds);
                });
        });

        // clean ui
        $this->showTagModal = false;
        $this->newTags = "";
        $this->dispatch("toast", message: "Tag berhasil ditambahkan.");
    }

    public function removeCheckedTagsFromSelection(): void
    {
        $tagIds = collect($this->removeTagIds)
            ->map(fn($id) => (int) $id)
            ->filter()
            ->values()
            ->all();
        if (empty($tagIds)) {
            $this->dispatch("toast", message: "Choose at least one tag.");
            return;
        }

        $this->bulkDetachTags($tagIds);

        $this->removeTagIds = [];
        $this->showRemoveTagModal = false;
        $this->dispatch("toast", message: "Tags removed.");
    }

    protected function bulkDetachTags(array $tagIds): void
    {
        if (empty($tagIds)) {
            return;
        }

        $idsQuery = $this->selectAllResults
            ? $this->filteredQuery()->select("id")
            : Upload::query()->whereIn("id", $this->checked)->select("id");

        $idsQuery->chunkById(500, function ($chunk) use ($tagIds) {
            $uploads = Upload::whereIn("id", $chunk->pluck("id"))->get();
            foreach ($uploads as $u) {
                $u->tags()->detach($tagIds);
            }
        });

        // Optional cleanup of orphan tags (only those we touched)
        Tag::whereIn("id", $tagIds)->whereDoesntHave("uploads")->delete();
    }

    public function getAvailableTagsProperty()
    {
        // Find upload ids in scope
        $ids = $this->selectAllResults
            ? $this->filteredQuery()->pluck("id")
            : collect($this->checked);

        if ($ids->isEmpty()) {
            return collect();
        }

        return Tag::whereHas("uploads", function ($q) use ($ids) {
            $q->whereIn("uploads.id", $ids);
        })
            ->orderBy("name")
            ->get();
    }

    public function render()
    {
        $items = $this->filteredQuery()
            ->with("tags:id,name,slug") // avoid N+1
            ->paginate($this->perPage);
        return view("livewire.file-library", compact("items"))->title("File Library");
    }
}

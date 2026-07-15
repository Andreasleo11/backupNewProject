<?php

namespace App\Livewire\Consumables;

use App\Models\Consumable;
use App\Models\ConsumableCategory;
use App\Models\AssetLocation;
use App\Models\StockTransaction;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class ConsumableManager extends Component
{
    use WithPagination;

    public $search = '';
    public $selectedCategory = '';

    public $name, $sku, $category_id, $current_stock, $min_stock, $location_id;
    public $unit, $reorder_point;
    public $editingConsumableId = null;
    public $showForm = false;

    public $transactionType = 'In'; // In, Out
    public $transactionQuantity = 1;
    public $targetUserId = null;
    public $targetEmployeeNik = null;
    public $notes = '';
    public $reference = '';
    public $selectedConsumableId = null;

    protected $updatesQueryString = ['search', 'selectedCategory'];

    public function render()
    {
        $consumables = Consumable::query()
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('sku', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedCategory, function ($query) {
                $query->where('category_id', $this->selectedCategory);
            })
            ->paginate(10);

        $transactions = StockTransaction::with(['consumable', 'user', 'targetUser', 'targetEmployee'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $selectedConsumable = $this->selectedConsumableId ? Consumable::find($this->selectedConsumableId) : null;

        return view('livewire.consumables.consumable-manager', [
            'consumables' => $consumables,
            'transactions' => $transactions,
            'categories' => ConsumableCategory::all(),
            'locations' => AssetLocation::all(),
            'employees' => \App\Infrastructure\Persistence\Eloquent\Models\Employee::all(),
            'selectedConsumable' => $selectedConsumable,
        ]);
    }

    public function resetFields()
    {
        $this->name = '';
        $this->sku = '';
        $this->category_id = '';
        $this->current_stock = 0;
        $this->min_stock = 5;
        $this->unit = 'pcs';
        $this->reorder_point = 5;
        $this->location_id = '';
        $this->editingConsumableId = null;
        $this->showForm = false;
    }

    public function showAddForm()
    {
        $this->resetFields();
        $this->showForm = true;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required',
            'category_id' => 'required',
            'current_stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
        ]);

        $consumable = Consumable::updateOrCreate(
            ['id' => $this->editingConsumableId],
            [
                'name' => $this->name,
                'sku' => $this->sku,
                'category_id' => $this->category_id,
                'current_stock' => $this->current_stock,
                'min_stock' => $this->min_stock,
                'unit' => $this->unit,
                'reorder_point' => $this->reorder_point,
                'location_id' => $this->location_id ?: null,
            ]
        );

        $this->resetFields();
        session()->flash('message', $this->editingConsumableId ? 'Consumable updated.' : 'Consumable created.');
        $this->dispatch('consumableUpdated', $consumable->id);
    }

    public function edit($id)
    {
        $consumable = Consumable::findOrFail($id);
        $this->editingConsumableId = $id;
        $this->name = $consumable->name;
        $this->sku = $consumable->sku;
        $this->category_id = $consumable->category_id;
        $this->current_stock = $consumable->current_stock;
        $this->min_stock = $consumable->min_stock;
        $this->unit = $consumable->unit;
        $this->reorder_point = $consumable->reorder_point;
        $this->location_id = $consumable->location_id;
        $this->showForm = true;
    }

    public function openTransactionModal($id)
    {
        $this->selectedConsumableId = $id;
        $this->transactionType = 'In';
        $this->transactionQuantity = 1;
        $this->targetUserId = null;
        $this->targetEmployeeNik = null;
        $this->notes = '';
        $this->reference = '';
    }

    public function submitTransaction()
    {
        $this->validate([
            'transactionQuantity' => 'required|integer|min:1',
            'transactionType' => 'required|in:In,Out',
            'targetEmployeeNik' => 'required_if:transactionType,Out',
        ]);

        if (! $this->selectedConsumableId) {
            session()->flash('error', 'No consumable selected.');
            return;
        }

        try {
            DB::transaction(function () {
                $consumable = Consumable::where('id', $this->selectedConsumableId)->lockForUpdate()->firstOrFail();

                if ($this->transactionType === 'Out' && $consumable->current_stock < $this->transactionQuantity) {
                    session()->flash('error', 'Not enough stock.');
                    // Throw to rollback
                    throw new \Exception('insufficient_stock');
                }

                StockTransaction::create([
                    'consumable_id' => $this->selectedConsumableId,
                    'type' => $this->transactionType,
                    'quantity' => $this->transactionQuantity,
                    'user_id' => auth()->id() ?: 1, // Fallback for testing
                    'target_user_id' => $this->targetUserId ?: null,
                    'target_employee_nik' => $this->targetEmployeeNik ?: null,
                    'notes' => $this->notes,
                    'reference' => $this->reference,
                ]);

                if ($this->transactionType === 'In') {
                    $consumable->increment('current_stock', $this->transactionQuantity);
                } else {
                    // Double-check to avoid negative after concurrent changes
                    if ($consumable->current_stock - $this->transactionQuantity < 0) {
                        session()->flash('error', 'Not enough stock.');
                        throw new \Exception('insufficient_stock');
                    }
                    $consumable->decrement('current_stock', $this->transactionQuantity);
                }
            });
        } catch (\Exception $e) {
            if ($e->getMessage() === 'insufficient_stock') {
                return;
            }
            throw $e;
        }

        session()->flash('message', 'Stock updated.');
        $id = $this->selectedConsumableId;
        $this->selectedConsumableId = null;
        $this->dispatch('consumableUpdated', $id);
        $this->dispatch('transactionCreated');
    }

    public function delete($id)
    {
        $consumable = Consumable::findOrFail($id);
        $consumable->delete();
        session()->flash('message', 'Consumable deleted successfully.');
        $this->dispatch('consumableUpdated', $id);
    }
}


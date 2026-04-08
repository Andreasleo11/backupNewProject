<?php

namespace App\DataTables;

use App\Models\hrd\ImportantDoc;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ImportantDocumentDataTable extends DataTable
{
    /**
     * The warning threshold in months.
     *
     * @var int
     */
    public $thresholdDays = 60;
    public $threshold = 2;
    public $today;

    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('document', function($doc) {
                $idLine = $doc->document_id ? '<div class="text-[10px] font-bold text-slate-400 uppercase tracking-tight">' . e($doc->document_id) . '</div>' : '';
                return '<div>' . 
                            '<div class="font-bold text-slate-900 text-sm">' . e($doc->name) . '</div>' . 
                            $idLine . 
                       '</div>';
            })
            ->editColumn(
                'action',
                '<div class="flex items-center justify-center gap-1.5">
                    <button type="button" @click="$store.docLibrary.openDetail({{ $id }})"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors" title="Quick View">
                        <i class="bx bx-show text-lg"></i>
                    </button>
                    <a href="{{ route(\'hrd.importantDocs.edit\', $id) }}"
                        class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="Edit">
                        <i class="bx bx-edit text-lg"></i>
                    </a>
                    <div x-data="{ open: false }" class="inline-block">
                        <button type="button" @click="open = true"
                            class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Delete">
                            <i class="bx bx-trash text-lg"></i>
                        </button>
                        <template x-teleport="body">
                            <div>
                                <div x-show="open" x-transition.opacity class="fixed inset-0 z-[100] bg-black/30 backdrop-blur-sm" @click="open = false" x-cloak></div>
                                <div x-show="open" x-transition class="fixed inset-0 z-[110] flex items-center justify-center px-4" x-cloak>
                                    <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl ring-1 ring-slate-200 overflow-hidden">
                                        <div class="flex items-center justify-between border-b border-slate-100 px-6 py-4 bg-slate-50">
                                            <h2 class="text-sm font-bold text-slate-900 uppercase tracking-widest flex items-center gap-2">
                                                <i class="bx bx-error-circle text-rose-500"></i> Delete Confirmation
                                            </h2>
                                            <button type="button" @click="open = false" class="rounded-full p-2 text-slate-400 hover:bg-rose-50 hover:text-rose-600"><i class="bx bx-x text-xl"></i></button>
                                        </div>
                                        <div class="px-6 py-6 text-sm text-slate-600">Are you sure you want to delete this document? This action cannot be undone.</div>
                                        <div class="flex justify-end gap-3 border-t border-slate-100 px-6 py-4 bg-slate-50">
                                            <button type="button" @click="open = false" class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-5 py-2 text-xs font-bold text-slate-700 hover:bg-slate-50">Cancel</button>
                                            <form method="POST" action="{{ route(\'hrd.importantDocs.delete\', $id) }}">
                                                @csrf @method(\'DELETE\')
                                                <button type="submit" class="inline-flex items-center rounded-xl bg-rose-600 px-6 py-2 text-xs font-bold text-white hover:bg-rose-700">
                                                    <i class="bx bx-trash-alt mr-1.5"></i>Confirm Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>',
            )
            ->filterColumn('type', function($query, $keyword) {
                if (empty($keyword)) return;
                $query->whereHas('type', function($q) use ($keyword) {
                    $q->where('name', $keyword);
                });
            })
            ->addColumn('status_type', function($row) {
                $today = $this->today ? $this->today->startOfDay() : now()->startOfDay();
                $diffDays = $today->diffInDays($row->expired_date, false);

                if ($diffDays < 0) return 'expired';
                if ($diffDays <= ($this->thresholdDays ?? 60)) return 'expiring';
                return 'active';
            })
            ->rawColumns(['action', 'expired_date', 'document'])
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param ImportantDoc $model
     */
    public function query(ImportantDoc $model): QueryBuilder
    {
        return $model::with('type')->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
            ->setTableId('importantdocument-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('frtip')
            ->orderBy(4, 'asc')
            ->buttons([]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->searchable(false),
            Column::computed('document')
                ->title('Document Info')
                ->addClass('align-middle'),
            Column::make('type')
                ->title('Category')
                ->data('type.name')
                ->searchable(true)
                ->orderable(false)
                ->addClass('text-center align-middle'),
            Column::make('description')
                ->title('Notes')
                ->addClass('align-middle')
                ->searchable(false)
                ->orderable(false),
            Column::make('expired_date')
                ->data('expired_date')
                ->title('Expiry')
                ->addClass('text-center align-middle')->renderRaw('
                function(data, type, row, meta){
                    if (type === \'display\') {
                        let dbDate = new Date(data);
                        let now = new Date(\'' . ($this->today ? $this->today->toDateString() : now()->toDateString()) . '\');
                        now.setHours(0,0,0,0);
                        dbDate.setHours(0,0,0,0);

                        let diffTime = dbDate - now;
                        let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        let thresholdDays = ' . ($this->thresholdDays ?? 60) . ';

                        let options = { day: \'2-digit\', month: \'2-digit\', year: \'numeric\' };
                        let displayDate = new Date(data).toLocaleDateString(\'id-ID\', options);

                        if (diffDays < 0) {
                            return \'<div class=\"flex flex-col items-center gap-1\">\' +
                                        \'<span class=\"inline-flex items-center rounded-full bg-rose-100 px-2.5 py-0.5 text-xs font-bold text-rose-700\">\' +
                                            \'Expired \' + Math.abs(diffDays) + \'d ago\' +
                                        \'</span>\' +
                                        \'<span class=\"text-[10px] font-medium text-slate-400\">\' + displayDate + \'</span>\' +
                                    \'</div>\';
                        } else if (diffDays <= thresholdDays) {
                            let label = (diffDays === 0) ? \'Expires Today\' : \'Expiring in \' + diffDays + \'d\';
                            return \'<div class=\"flex flex-col items-center gap-1\">\' +
                                        \'<span class=\"inline-flex items-center rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-bold text-amber-700\">\' +
                                            label +
                                        \'</span>\' +
                                        \'<span class=\"text-[10px] font-medium text-slate-400\">\' + displayDate + \'</span>\' +
                                    \'</div>\';
                        } else {
                            return \'<span class=\"text-sm font-medium text-slate-600\">\' + displayDate + \'</span>\';
                        }
                    }
                    return data;
                }
            '),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center align-middle'),
            Column::make('status_type')->title('Status Type')->visible(false)->searchable(true)
        ];
    }

    /**
     * Get filename for export.
     */
    protected function filename(): string
    {
        return 'ImportantDocument_' . date('YmdHis');
    }
}

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
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn(
                'action',
                '<div class="flex items-center justify-center gap-1">
                    <a href="{{ route(\'hrd.importantDocs.detail\', $id) }}"
                        class="inline-flex items-center rounded-md border border-slate-200 bg-white px-2.5 py-1 text-xs font-medium text-slate-700 hover:bg-slate-50">
                        <i class="bx bx-info-circle mr-1"></i>Detail
                    </a>
                    <a href="{{ route(\'hrd.importantDocs.edit\', $id) }}"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-indigo-700">
                        <i class="bx bx-edit mr-1"></i>Edit
                    </a>
                    <div x-data="{ open: false }" class="inline-block">
                        <button type="button" @click="open = true"
                            class="inline-flex items-center rounded-md bg-rose-600 px-2.5 py-1 text-xs font-semibold text-white hover:bg-rose-700">
                            <i class="bx bx-trash-alt mr-1"></i>Delete
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
            ->orderBy(5, 'asc')
            // ->dom('Bfrtip')
            ->buttons([
                Button::make('excel'),
                Button::make('csv'),
                // Button::make('pdf'),
                Button::make('print'),
                Button::make('reset'),
                Button::make('reload'),
            ]);
    }

    /**
     * Get the dataTable columns definition.
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->addClass('text-center align-middle'),
            Column::make('document_id')->addClass('text-center align-middle'),
            Column::make('name')->addClass('text-center align-middle'),
            Column::make('type')
                ->data('type.name')
                ->searchable(false)
                ->orderable(false)
                ->addClass('text-center align-middle'),
            Column::make('description')->addClass('text-center align-middle'),
            Column::make('expired_date')
                ->data('expired_date')
                ->title('Expired Date')
                ->addClass('text-center align-middle')->renderRaw('
                function(data, type, row, meta){
                    if (type === \'display\') {
                        // Example date string from the database
                        let dateString = data;

                        // Parse the date string into a JavaScript Date object
                        let dbDate = new Date(dateString);

                        // Get the current date
                        let currentDate = new Date();

                        // Calculate a date that is 2 months from now
                        let twoMonthsFromNow = new Date();
                        twoMonthsFromNow.setMonth(twoMonthsFromNow.getMonth() + 2);

                        // Compare the parsed database date with the calculated date
                        if (dbDate.getTime() < twoMonthsFromNow.getTime()) {
                            return \'<span class="badge rounded-pill text-bg-danger px-3 py-2 fs-6 fw-medium">\' + dateString + \'</span>\';
                        }
                    }
                    return data; // Return the original data for other types
                }
            '),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),
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

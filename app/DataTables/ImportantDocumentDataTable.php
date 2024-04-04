<?php

namespace App\DataTables;

use App\Models\hrd\ImportantDoc;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Yajra\DataTables\Html\Builder as HtmlBuilder;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ImportantDocumentDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param QueryBuilder $query Results from query() method.
     * @return \Yajra\DataTables\EloquentDataTable
     */
    public function dataTable(QueryBuilder $query): EloquentDataTable
    {
        return (new EloquentDataTable($query))
            ->addColumn('action', '
            <a href="{{ route("hrd.importantDocs.detail", $id) }}"
                class="btn btn-secondary me-1">
                <div class="col d-flex align-middle">
                    <box-icon name="info-circle" color="white" class="pb-1"></box-icon>
                    <span class="ms-1">Detail</span>
                </div>
            </a>
            <a href="{{ route("hrd.importantDocs.edit", $id) }}"
                class="btn btn-primary me-1">
                <div class="col d-flex">
                    <box-icon name="edit" color="white" class="pb-1"></box-icon>
                    <span class="ms-1">Edit</span>
                </div>
            </a>

            <button data-bs-toggle="modal"
                data-bs-target="#delete-confirmation-modal-{{ $id }}"
                class="btn btn-danger"
                onclick="event.preventDefault(); document.getElementById("delete-form").submit()">
                <div class="col d-flex">
                    <box-icon name="trash" color="white" class="pb-1"></box-icon>
                    <span class="ms-1">Delete</span>
                </div>
            </button>
            ')
            // ->editColumn('expired_date', '{{ \Carbon\Carbon::parse($expired_date)->format(\'d-m-Y\') }}')
            ->setRowId('id');
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\ImportantDocument $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ImportantDoc $model): QueryBuilder
    {
        return $model::with('type')->newQuery();
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html(): HtmlBuilder
    {
        return $this->builder()
                    ->setTableId('importantdocument-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->orderBy(5, 'asc')
                    //->dom('Bfrtip')
                    ->buttons([
                        Button::make('excel'),
                        Button::make('csv'),
                        // Button::make('pdf'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    ]);
    }

    /**
     * Get the dataTable columns definition.
     *
     * @return array
     */
    public function getColumns(): array
    {
        return [
            Column::make('id')->addClass('text-center align-middle'),
            Column::make('document_id')->addClass('text-center align-middle'),
            Column::make('name')->addClass('text-center align-middle'),
            Column::make('type')->data('type.name')->searchable(false)->orderable(false)->addClass('text-center align-middle'),
            Column::make('description')->addClass('text-center align-middle'),
            Column::make('expired_date')->data('expired_date')->title('Expired Date')->addClass('text-center align-middle')->renderRaw('
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
                        console.log(twoMonthsFromNow);

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
     *
     * @return string
     */
    protected function filename(): string
    {
        return 'ImportantDocument_' . date('YmdHis');
    }
}

<?php

namespace App\DataTables\Admin;

use App\Models\EvaluationDataWeekly;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Html\Editor\Editor;
use Illuminate\Database\Eloquent\Builder as QueryBuilder;
use Yajra\DataTables\EloquentDataTable;
use Carbon\Carbon;

class EvaluationDataWeeklyManagementDataTable extends DataTable
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
            ->addIndexColumn()
            ->addColumn('karyawan.Nama', function (EvaluationDataWeekly $record) {
                return $record->karyawan->Nama ?? $record->karyawan->name ?? '<span class="text-slate-400 italic">Terhapus/Kosong</span>';
            })
            ->editColumn('Month', function (EvaluationDataWeekly $record) {
                return $record->Month ? Carbon::parse($record->Month)->translatedFormat('d F Y') : '-';
            })
            ->addColumn('action', function (EvaluationDataWeekly $record) {
                // Since this model has no 'id' column mapped correctly, we might need a composite key or simply depend on DB constraints if id is missing.
                // Assuming we use an id or pass NIK+Month if necessary. If id is present in table despite Eloquent configuration, we can use it.
                // For safety, assuming $record->_id or $record->id exists if it's a standard table without an explicitly disabled PK.
                // Let's pass the NIK instead and Month in query string if id is not reliable, but for Laravel resource destroy we usually need an ID.
                $itemId = $record->id ?? ($record->NIK . '|' . $record->Month);
                $deleteUrl = route('admin.evaluation-data-weekly.destroy', $itemId);
                
                return '
                    <button type="button" 
                            onclick="deleteRow(\'' . $deleteUrl . '\')"
                            class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded text-rose-700 bg-rose-100 hover:bg-rose-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-colors tooltip-trigger"
                            data-tippy-content="Hapus Baris">
                        <i class="bx bx-trash text-sm"></i>
                    </button>
                ';
            })
            ->rawColumns(['karyawan.Nama', 'action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\EvaluationDataWeekly $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(EvaluationDataWeekly $model): \Illuminate\Database\Eloquent\Builder
    {
        return $model->newQuery()
            ->with(['karyawan']);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('evaluation-data-weekly-management-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom('<"flex flex-col sm:flex-row justify-between items-center bg-white p-4 border-b border-slate-100 rounded-t-xl gap-4"lf><"overflow-x-auto"rt><"flex flex-col sm:flex-row justify-between items-center bg-slate-50 p-4 border-t border-slate-100 rounded-b-xl gap-4"ip>')
            ->orderBy(1)
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'language' => [
                    'search' => '',
                    'searchPlaceholder' => 'Cari NIK/Nama/Dept...',
                    'lengthMenu' => '_MENU_ baris',
                    'info' => 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
                    'infoEmpty' => 'Data tidak ditemukan',
                    'zeroRecords' => 'Tidak ada data yang cocok',
                    'paginate' => [
                        'first' => '<i class="bx bx-chevrons-left"></i>',
                        'last' => '<i class="bx bx-chevrons-right"></i>',
                        'next' => '<i class="bx bx-chevron-right"></i>',
                        'previous' => '<i class="bx bx-chevron-left"></i>',
                    ],
                ]
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns(): array
    {
        return [
            Column::make('DT_RowIndex')->title('No')->searchable(false)->orderable(false)->width(50)->addClass('text-center text-slate-500 font-medium'),
            Column::make('Month')->title('Tanggal')->addClass('text-slate-700 font-medium'),
            Column::make('NIK')->title('NIK')->addClass('text-slate-600 font-medium'),
            Column::make('karyawan.Nama')->title('Nama')->name('karyawan.Nama')->addClass('text-slate-800 font-semibold'),
            Column::make('dept')->title('Dept')->addClass('text-slate-600 uppercase text-xs font-bold'),
            Column::make('Alpha')->title('A')->searchable(false)->addClass('text-center font-bold text-rose-500'),
            Column::make('Telat')->title('T')->searchable(false)->addClass('text-center font-bold text-amber-500'),
            Column::make('Izin')->title('I')->searchable(false)->addClass('text-center font-bold text-sky-500'),
            Column::make('Sakit')->title('S')->searchable(false)->addClass('text-center font-bold text-indigo-500'),
            Column::computed('action')
                  ->exportable(false)
                  ->printable(false)
                  ->width(60)
                  ->addClass('text-center'),
        ];
    }
}

@extends('layouts.app')

@section('content')
    <div class="container py-5">
        <h1 class="mb-4 h3 fw-semibold text-dark">Laporan Harian Karyawan (Departemen Anda)</h1>

        <form method="GET" action="{{ route('reports.depthead.index') }}" class="mb-4">
            <div class="row g-2 align-items-center">
                <div class="col-auto">
                    <select name="filter_employee_id" class="form-select">
                        <option value="">-- Pilih NIK atau Nama --</option>
                        @foreach ($employeesDropdown as $emp)
                            <option value="{{ $emp->employee_id }}"
                                {{ (string) $emp->employee_id === (string) $filterEmployeeId ? 'selected' : '' }}>
                                {{ $emp->employee_id }} - {{ $emp->employee_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @if (
                    (auth()->user()->is_head && auth()->user()->department->name === 'PERSONALIA') ||
                        auth()->user()->department->name === 'MANAGEMENT')
                    <div class="col-auto">
                        <select name="filter_department_no" class="form-select">
                            <option value="">-- Pilih Department No --</option>
                            @foreach ($departmentNos as $deptNo)
                                <option value="{{ $deptNo }}" {{ $filterDepartmentNo === $deptNo ? 'selected' : '' }}>
                                    {{ $deptNo }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('reports.depthead.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </div>
        </form>


        @if ($employees->isEmpty())
            <div class="alert alert-warning" role="alert">
                Tidak ada laporan yang ditemukan dari anggota departemen Anda.
            </div>
        @else
            <div class="table-responsive bg-white rounded shadow-sm">
                <table class="table table-hover table-bordered align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">NIK</th>
                            <th scope="col">Dept No</th>
                            <th scope="col">Nama Karyawan</th>
                            <th scope="col">Terakhir Update</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($employees as $employee)
                            <tr>
                                <td class="text-monospace">
                                    {{ $employee->employee_id }}
                                </td>
                                <td class="text-monospace">
                                    {{ $employee->departement_id }}
                                </td>
                                <td>
                                    {{ $employee->employee_name }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($employee->latest_work_date)->format('d M Y') }} -
                                    {{ $employee->latest_work_time }}
                                </td>
                                <td>
                                    <a href="{{ route('reports.depthead.show', $employee->employee_id) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

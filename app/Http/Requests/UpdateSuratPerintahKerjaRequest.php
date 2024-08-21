<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSuratPerintahKerjaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // You can add authorization logic here if needed.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'no_dokumen' => 'required|string|max:255',
            'pelapor' => 'string|max:255',
            'dept' => 'required|string|max:255',
            'tanggal_lapor' => 'date',
            'judul_laporan' => 'required|string|max:255',
            'keterangan_laporan' => 'required|string|max:1000',
            'pic' => 'nullable|string|max:255',
            'keterangan_pic' => 'nullable|string|max:1000',
            'tanggal_terima' => 'nullable|date',
            'status_laporan' => 'nullable|integer',
            'tanggal_selesai' => 'nullable|date',
            'tanggal_estimasi' => 'nullable|date',
        ];
    }

    /**
     * Get the custom messages for the defined validation rules.
     */
    public function messages(): array
    {
        return [
            'no_dokumen.required' => 'No Dokumen is required.',
            'no_dokumen.string' => 'No Dokumen must be a string.',
            'no_dokumen.max' => 'No Dokumen may not be greater than 255 characters.',
            'dept.required' => 'Departemen is required.',
            'dept.string' => 'Departemen must be a string.',
            'dept.max' => 'Departemen may not be greater than 255 characters.',
            'judul_laporan.required' => 'Judul Laporan is required.',
            'judul_laporan.string' => 'Judul Laporan must be a string.',
            'judul_laporan.max' => 'Judul Laporan may not be greater than 255 characters.',
            'keterangan_laporan.required' => 'Keterangan Laporan is required.',
            'keterangan_laporan.string' => 'Keterangan Laporan must be a string.',
            'keterangan_laporan.max' => 'Keterangan Laporan may not be greater than 1000 characters.',
            'pic.string' => 'PIC must be a string.',
            'pic.max' => 'PIC may not be greater than 255 characters.',
            'keterangan_pic.string' => 'Keterangan PIC must be a string.',
            'keterangan_pic.max' => 'Keterangan PIC may not be greater than 1000 characters.',
            'status_laporan.integer' => 'Status Laporan must be an integer.',
            'tanggal_terima.date' => 'Tanggal Terima must be a valid date.',
            'tanggal_selesai.date' => 'Tanggal Selesai must be a valid date.',
            'tanggal_estimasi.date' => 'Tanggal Estimasi must be a valid date.',
        ];
    }
}

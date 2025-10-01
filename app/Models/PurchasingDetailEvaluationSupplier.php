<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PurchasingDetailEvaluationSupplier extends Model
{
    protected $table = 'purchasing_detail_evaluation_supplier';

    protected $fillable = [
        'header_id',
        'month',
        'year',
        'kualitas_barang',
        'ketepatan_kuantitas_barang',
        'ketepatan_waktu_pengiriman',
        'kerjasama_permintaan_mendadak',
        'respon_klaim',
        'sertifikasi',
        'customer_stopline',
    ];

    public function header()
    {
        return $this->belongsTo(PurchasingHeaderEvaluationSupplier::class, 'header_id', 'id');
    }
}

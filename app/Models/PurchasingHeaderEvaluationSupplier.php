<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasingHeaderEvaluationSupplier extends Model
{
    protected $table = "purchasing_header_evaluation_supplier";

    protected $fillable = [
        "doc_num",
        "vendor_code",
        "vendor_name",
        "start_month",
        "end_month",
        "year",
        "year_end",
        "grade",
        "status",
    ];

    public function details()
    {
        return $this->hasMany(PurchasingDetailEvaluationSupplier::class, "header_id", "id");
    }

    public function contact()
    {
        return $this->belongsTo(
            PurchasingContact::class, // Model relasi
            "vendor_name", // foreign key di tabel purchasing_header_evaluation_supplier
            "vendor_name", // primary/unique key di tabel purchasing_contacts
        );
    }
}

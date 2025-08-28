<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchasingHeaderEvaluationSupplier extends Model
{
    protected $table = "purchasing_header_evaluation_supplier";

    protected $fillable = ["doc_num", "vendor_code", "vendor_name", "year", "grade", "status"];

    public function details()
    {
        return $this->hasMany(PurchasingDetailEvaluationSupplier::class, "header_id", "id");
    }
}

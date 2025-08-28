<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SummaryFormKerusakan extends Model
{
    use HasFactory;
    protected $table = "summary_form_kerusakan";

    protected $fillable = ["doc_num", "customer", "release_date"];
}

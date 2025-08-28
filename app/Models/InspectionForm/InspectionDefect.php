<?php

namespace App\Models\InspectionForm;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InspectionDefect extends Model
{
    use HasFactory;

    protected $fillable = ["name", "department"];
}

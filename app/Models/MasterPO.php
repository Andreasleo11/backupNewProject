<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPO extends Model
{
    use HasFactory;
    protected $table = 'master_po';

    protected $fillable = [
        'po_number',
        'approved_date',
        'status',
        'filename',
        'reason',
        // Add other fields as needed
    ];
}

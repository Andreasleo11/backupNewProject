<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpkRemark extends Model
{
    use HasFactory;
    protected $table = 'spk_remarks';

    protected $fillable = [
        'spk_id',
        'remarks',
        'status',
    ];

    public function spkRelation()
    {
        return $this->belongsTo(User::SuratPerintahKerjaKomputer);
    }
}

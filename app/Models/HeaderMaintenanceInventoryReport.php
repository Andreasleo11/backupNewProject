<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HeaderMaintenanceInventoryReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'no_dokumen',
        'master_id',
        'revision_date',
        'periode_caturwulan'
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
                $month = Carbon::parse($model->created_at)->month;

                if ($month >= 1 && $month <= 4) {
                    $model->periode_caturwulan = 1;
                } elseif ($month >= 5 && $month <= 8) {
                    $model->periode_caturwulan = 2;
                } elseif ($month >= 9 && $month <= 12) {
                    $model->periode_caturwulan = 3; 
                }
        });
    }

    public function detail()
    {
        return $this->hasMany(DetailMaintenanceInventoryReport::class, 'header_id');
    }

    public function master()
    {
        return $this->belongsTo(MasterInventory::class);
    }
}

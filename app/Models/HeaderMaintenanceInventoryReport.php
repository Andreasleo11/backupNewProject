<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderMaintenanceInventoryReport extends Model
{
    use HasFactory;

    protected $fillable = ['no_dokumen', 'master_id', 'revision_date', 'periode_caturwulan'];

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

    public static function generateNoDokumen()
    {
        // Get today's date in YYMMDD format
        $date = now()->format('ymd');

        // Count the number of documents already created today
        $countToday = self::whereDate('created_at', now()->format('Y-m-d'))->count();

        // Generate the sequence number, starting from 001
        $sequenceNumber = str_pad($countToday + 1, 3, '0', STR_PAD_LEFT);

        // Return the formatted document number
        return 'MIR/'.$date.'/'.$sequenceNumber;
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

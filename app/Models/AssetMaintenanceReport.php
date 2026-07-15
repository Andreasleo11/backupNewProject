<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetMaintenanceReport extends Model
{
    use HasFactory;

    protected $table = 'asset_maintenance_reports';

    protected $fillable = ['document_number', 'asset_id', 'period', 'year', 'revision_date'];

    protected static function booted()
    {
        static::saving(function ($model) {
            if (!$model->created_at) {
                $model->created_at = now();
            }
            $date = Carbon::parse($model->created_at);
            $month = $date->month;
            $model->year = $model->year ?? $date->year;

            if ($month >= 1 && $month <= 4) {
                $model->period = 1;
            } elseif ($month >= 5 && $month <= 8) {
                $model->period = 2;
            } else {
                $model->period = 3;
            }
        });
    }

    public static function generateNoDokumen()
    {
        $date = now()->format('ymd');
        $countToday = self::whereDate('created_at', now()->format('Y-m-d'))->count();
        $sequenceNumber = str_pad($countToday + 1, 3, '0', STR_PAD_LEFT);
        return 'MIR/' . $date . '/' . $sequenceNumber;
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function details()
    {
        return $this->hasMany(MaintenanceReportDetail::class, 'report_id');
    }
}

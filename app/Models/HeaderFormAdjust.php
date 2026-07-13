<?php

namespace App\Models;

use App\Infrastructure\Persistence\Eloquent\Models\VerificationReport;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderFormAdjust extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'report_id',
        'verification_report_id', // bridge column — new records use this
        'autograph_1', 'autograph_2', 'autograph_3',
        'autograph_4', 'autograph_5', 'autograph_6', 'autograph_7',
    ];

    public function evaluationData()
    {
        return $this->hasMany(FormAdjustMaster::class, 'header_id', 'id');
    }

    /**
     * Legacy relation — points to old Report model (historical data).
     */
    public function report()
    {
        return $this->hasOne(Report::class, 'id', 'report_id');
    }

    /**
     * New relation — points to VerificationReport (new records after migration).
     */
    public function verificationReport()
    {
        return $this->belongsTo(VerificationReport::class, 'verification_report_id');
    }
}

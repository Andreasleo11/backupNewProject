<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceReportDetail extends Model
{
    use HasFactory;

    protected $table = 'maintenance_report_details';

    protected $fillable = ['report_id', 'checklist_item_id', 'custom_item_name', 'condition', 'remark', 'checked_by'];

    public function report()
    {
        return $this->belongsTo(AssetMaintenanceReport::class, 'report_id');
    }

    public function checklistItem()
    {
        return $this->belongsTo(MaintenanceChecklistItem::class, 'checklist_item_id');
    }
}

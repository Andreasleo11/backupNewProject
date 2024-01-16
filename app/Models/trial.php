<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class trial extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer',
        'part_name',
        'part_no',
        'model',
        'cavity',
        'status_trial',
        'material',
        'status_material',
        'color',
        'material_consump',
        'dimension_tooling',
        'member_trial',
        'request_trial',
        'trial_date',
        'time_set_up_tooling',
        'timme_setting_tooling',
        'time_finish_inject',
        'time_set_down_tooling',
        'trial_cost',
        'tonage',
        'qty',
        'adjuster',
        'requested_by',
        'requested_by_name',
        'verify_by',
        'verify_by_name',
        'confirmed_by_1',
        'confirmed_by_name_1',
        'confirmed_by_2',
        'confirmed_by_name_2',
        'confirmed_by_3',
        'confirmed_by_name_3',
        'approved_by',
        'approved_by_name',
    ];
}

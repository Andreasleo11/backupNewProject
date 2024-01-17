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
        'autograph_1',
        'autograph_user_1',
        'autograph_2',
        'autograph_user_2',
        'autograph_3',
        'autograph_user_3',
        'autograph_4',
        'autograph_user_4',
        'autograph_5',
        'autograph_user_5',
        'autograph_6',
        'autograph_user_6',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;
    protected $table = 'reports';

    protected $fillable = [
        'Rec_Date',
        'Verify_Date',
        'Customer',
        'Invoice_No',
        'autograph_1',
        'autograph_2',
        'autograph_3',
        'autograph_user_1',
        'autograph_user_2',
        'autograph_user_3',
        // Add other fields as needed
    ];

    // Define relationships if needed
    public function details()
    {
        return $this->hasMany(Detail::class);
    }

    public function updateAutograph($section, $signaturePath)
    {
        switch ($section) {
            case 1:
                $this->update(['autograph_1' => $signaturePath]);
                break;
            case 2:
                $this->update(['autograph_2' => $signaturePath]);
                break;
            case 3:
                $this->update(['autograph_3' => $signaturePath]);
                break;
            default:
                // Handle other cases if needed
                break;
        }
    }
}

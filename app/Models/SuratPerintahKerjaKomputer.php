<?php

namespace App\Models;

use App\Notifications\SPKCreated;
use App\Notifications\SPKUpdated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

class SuratPerintahKerjaKomputer extends Model
{
    use HasFactory;
    protected $table = 'surat_perintah_kerja_komputer';

    protected $fillable = [
        'no_dokumen',
        'pelapor',
        'dept',
        'to_department',
        'tanggal_lapor',
        'judul_laporan',
        'keterangan_laporan',
        'pic',
        'keterangan_pic',
        'status_laporan',
        'tanggal_terima',
        'tanggal_selesai',
        'tanggal_estimasi',
    ];

    public function deptRelation()
    {
        return $this->belongsTo(Department::class, 'dept', 'name');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'pelapor', 'name');
    }

    public function spkRemarks()
    {
        return $this->hasMany(SpkRemark::class, 'spk_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($spk) {
            $prefix = 'DI';
            switch ($spk->to_department) {
                case 'COMPUTER':
                    $middle = 'CP';
                    break;
                case 'PERSONALIA':
                    $middle = 'HRD';
                    break;
                case 'MAINTENANCE':
                    $middle = 'MT';
                    break;
                default:
                    $middle = 'UNKNOWN';
                    break;
            }
            $thirdfix = "SPK";

            $lastNumber = str_pad($spk->id, 5, '0', STR_PAD_LEFT);


            $no_dokumen = "$prefix/$middle/$thirdfix/$lastNumber";

            $spk->no_dokumen = $no_dokumen;
            $spk->save();

            $spk->sendNotification('created');
        });

        static::updated(function ($spk) {
            $spk->sendNotification('updated');
        });
    }

    private function sendNotification($event)
    {
        $details = $this->prepareNotificationDetails($event);
        $this->notifyUsers($details, $event);
    }

    private function prepareNotificationDetails($event)
    {
        $status = $this->getStatusText($this->status_laporan);

        $commonDetails = [
            'greeting' => 'Surat Perintah Kerja Komputer Notification',
            'actionText' => 'Check Now',
            'actionURL' => route('spk.detail', $this->id),
        ];

        if ($event == 'created') {
            $commonDetails['body'] = "Notification for SPK : <br>
                - No Dokumen : $this->no_dokumen <br>
                - Pelapor : $this->pelapor <br>
                - Departemen : $this->dept <br>";
        } else {
            $keteranganPic = $this->keterangan_pic ?: '-';
            $commonDetails['body'] = "Notification for SPK : <br>
                - No Dokumen : $this->no_dokumen <br>
                - PIC : $this->pic  <br>
                - Keterangan PIC : $keteranganPic <br>
                - Status : $status";
        }

        return $commonDetails;
    }

    private function getStatusText($status)
    {
        switch ($status) {
            case 0:
                return 'WAITING';
            case 1:
                return 'IN PROGRESS';
            case 2:
                return 'DONE';
            default:
                return 'UNKNOWN';
        }
    }

    private function notifyUsers($details, $event)
    {
        if ($event == 'created' || $this->status_laporan === 0) {
            $users = User::whereHas('department', function ($query) {
                $query->where('name', 'COMPUTER');
            })->get();
            Notification::send($users, new SPKCreated($this, $details));
        } else {
            $user = $this->createdBy;
            $user->notify(new SPKUpdated($this, $details));
        }
    }
}

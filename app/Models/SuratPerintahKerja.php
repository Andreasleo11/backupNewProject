<?php

namespace App\Models;

use App\Notifications\SPKCreated;
use App\Notifications\SPKUpdated;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

class SuratPerintahKerja extends Model
{
    use HasFactory;
    protected $table = 'surat_perintah_kerja';

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
        'requested_by_autograph',
        'prepared_by_autograph',
        'pic_autograph',
        'finished_by_autograph',
        'dept_head_autograph',
        'requested_by',
        'is_revision',
        'revision_count',
        'revision_reason',
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
        return $this->hasMany(SpkRemark::class, 'spk_id')->where('is_revision', 0);
    }

    public function revisionRemarks()
    {
        return $this->hasMany(SpkRemark::class, 'spk_id')->where('is_revision', 1);
    }

    protected static function boot()
    {
        parent::boot();

        static::created(function ($spk) {
            $spk->sendNotification('created');
        });

        static::updated(function ($spk) {
            $statusChanged = $spk->isDirty('status_laporan');
            $keteranganPicChanged = $spk->isDirty('keterangan_pic');

            if (($statusChanged || $keteranganPicChanged)) {
                // Create SPK Remark
                $remarks = $spk->keterangan_pic;
                $status = $spk->status_laporan;
                $spkId = $spk->id;
                $revisionReason = $spk->revision_reason;

                if (!$spk->is_revision) {
                    SpkRemark::create([
                        'spk_id' => $spkId,
                        'status' => $status,
                        'remarks' => $remarks,
                    ]);
                } elseif ($spk->is_revision) {
                    SpkRemark::create([
                        'spk_id' => $spkId,
                        'status' => $status,
                        'remarks' => $remarks ?? $revisionReason,
                        'is_revision' => true,
                    ]);
                }
            }

            if ($statusChanged) {
                $spk->sendNotification('updated');
            }
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
        } elseif ($event == 'updated') {
            $keteranganPic = $this->keterangan_pic ?: '-';

            if ($this->is_revision) {
                $commonDetails['body'] = "Notification for SPK : <br>
                    Revision-$this->revision_count <br>
                    - Revision Reason : $this->revision_reason <br>
                    - No Dokumen : $this->no_dokumen <br>
                    - PIC : $this->pic  <br>
                    - Keterangan PIC : $keteranganPic <br>
                    - Status : $status";
            } else {
                $commonDetails['body'] = "Notification for SPK : <br>
                    - No Dokumen : $this->no_dokumen <br>
                    - PIC : $this->pic  <br>
                    - Keterangan PIC : $keteranganPic <br>
                    - Status : $status";
            }
        }

        return $commonDetails;
    }

    private function getStatusText($status)
    {
        switch ($status) {
            case 0:
                return 'WAITING CREATOR';
            case 1:
                return 'WAITING PIC';
            case 2:
                return 'IN PROGRESS';
            case 3:
                return 'DONE';
            case 4:
                return 'FINISH';
            default:
                return 'UNKNOWN';
        }
    }

    private function notifyUsers($details, $event)
    {
        if ($event == 'created') {
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

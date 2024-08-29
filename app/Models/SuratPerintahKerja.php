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
        'from_department',
        'to_department',
        'tanggal_lapor',
        'judul_laporan',
        'keterangan_laporan',
        'pic',
        'tindakan',
        'status_laporan',
        'tanggal_mulai',
        'tanggal_selesai',
        'tanggal_estimasi',
        'creator_autograph',
        'dept_head_autograph',
        'admin_autograph',
        'pic_autograph',
        'approved_autograph',
        'requested_by',
        'is_revision',
        'revision_count',
        'revision_reason',
        'is_urgent',
        'for',
    ];

    public function fromDepartment()
    {
        return $this->belongsTo(Department::class, 'from_department', 'name');
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
            $keteranganPicChanged = $spk->isDirty('tindakan');

            if (($statusChanged && $spk->tindakan || $keteranganPicChanged)) {
                // Create SPK Remark
                $remarks = $spk->tindakan;
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
            'greeting' => 'Surat Perintah Kerja Notification',
            'actionText' => 'Check Now',
            'actionURL' => route('spk.detail', $this->id),
        ];

        if ($event == 'created') {
            $commonDetails['body'] = "Notification for SPK : <br>
                - No Dokumen : $this->no_dokumen <br>
                - Pelapor : $this->pelapor <br>
                - Departmen : $this->from_department <br>";
        } elseif ($event == 'updated') {
            $keteranganPic = $this->tindakan ?: '-';

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
                return 'WAITING DEPT HEAD';
            case 6:
                return 'WAITING PPIC';
            case 2:
                return 'WAITING ADMIN';
            case 3:
                return 'IN PROGRESS';
            case 4:
                return 'DONE';
            case 5:
                return 'FINISH';
            default:
                return 'UNKNOWN';
        }
    }

    private function notifyUsers($details, $event)
    {
        $creator = $this->createdBy;
        $users = [$creator];

        if ($event == 'created') {
            //? WHO WILL BE NOTIFIED?
        } elseif ($event == 'updated') {
            if ($this->to_department === 'COMPUTER') {
                $users = User::whereHas('department', function ($query) {
                    $query->where('name', 'COMPUTER');
                })->get();
            } elseif ($this->to_department === 'MAINTENANCE MOULDING') {
                switch ($this->status_laporan) {
                    case 1:
                        $user = User::where('is_head', true)->whereHas('department', function ($query) {
                            $query->where('name', $this->from_department);
                        })->first();
                        break;
                    case 6:
                        $user = User::where('is_head', true)->whereHas('department', function ($query) {
                            $query->where('name', 'PPIC');
                        })->first();
                        break;
                    case 2:
                        $user = User::where('name', 'umi_kulsum')->first();
                        break;
                    case 3:
                    case 4:
                        $user = User::where('is_head', true)->whereHas('department', function ($query) {
                            $query->where('name', $this->to_department);
                        })->first();
                        break;
                    default:
                        $user = $creator;
                        break;
                }
            } elseif ($this->to_department === 'MAINTENANCE') {
                switch ($this->status_laporan) {
                    case 1:
                        $user = User::where('is_head', true)->whereHas('department', function ($query) {
                            $query->where('name', $this->from_department);
                        })->first();
                        break;
                    case 2:
                        $user = User::where('name', 'iza')->first();
                        break;
                    case 3:
                    case 4:
                        $user = User::where('is_head', true)->whereHas('department', function ($query) {
                            $query->where('name', $this->to_department);
                        })->first();
                        break;
                    default:
                        $user = $creator;
                        break;
                }
            } elseif ($this->to_department === 'PERSONALIA') {
                //? WHO WILL BE NOTIFIED?
            }

            // Check if $user is not null, then add it to the $users array
            if ($user) {
                $users[] = $user; // Use array append
            }

            Notification::send($users, new SPKUpdated($this, $details));
        }
    }
}

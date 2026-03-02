<?php

namespace App\Models;

use App\Infrastructure\Persistence\Eloquent\Models\Department;
use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 *
 * @property int         $id
 * @property string      $NIK
 * @property string|null $dept
 * @property string      $Month
 * @property int         $Alpha
 * @property int         $Telat
 * @property int         $Izin
 * @property int         $Sakit
 * @property string      $kerajinan_kerja
 * @property string      $kerapian_kerja
 * @property string      $prestasi
 * @property string      $loyalitas
 * @property string      $perilaku_kerja
 * @property string      $kemampuan_kerja
 * @property string      $kecerdasan_kerja
 * @property string      $qualitas_kerja
 * @property string      $disiplin_kerja
 * @property string      $kepatuhan_kerja
 * @property string      $lembur
 * @property string      $efektifitas_kerja
 * @property string      $relawan
 * @property string      $integritas
 * @property int         $total
 * @property string|null $pengawas
 * @property string|null $depthead
 * @property string|null $generalmanager
 * @property bool|null   $is_lock
 * @property string|null $remark
 */
class EvaluationData extends Model
{
    use HasFactory;

    protected $table = 'evaluation_datas';

    /** Table has no created_at / updated_at columns */
    public $timestamps = false;

    /** Table DOES have an auto-increment `id` column */
    public $incrementing = true;

    protected $fillable = [
        'NIK',
        'dept',
        'Month',
        'Alpha',
        'Telat',
        'Izin',
        'Sakit',
        // Old system (KONTRAK / TETAP)
        'kerajinan_kerja',
        'kerapian_kerja',
        'prestasi',
        'loyalitas',
        'perilaku_kerja',
        // New system (YAYASAN / MAGANG)
        'kemampuan_kerja',
        'kecerdasan_kerja',
        'qualitas_kerja',
        'disiplin_kerja',
        'kepatuhan_kerja',
        'lembur',
        'efektifitas_kerja',
        'relawan',
        'integritas',
        // Computed / approval fields
        'total',
        'pengawas',
        'depthead',
        'generalmanager',
        'is_lock',
        'remark',
    ];

    protected $casts = [
        'is_lock' => 'boolean',
        'Month'   => 'date',
        'total'   => 'integer',
        'Alpha'   => 'integer',
        'Telat'   => 'integer',
        'Izin'    => 'integer',
        'Sakit'   => 'integer',
    ];

    // ──────────────────────────────────────────────
    // Relationships
    // ──────────────────────────────────────────────

    /**
     * The employee this evaluation record belongs to.
     */
    public function karyawan()
    {
        return $this->belongsTo(Employee::class, 'NIK', 'nik');
    }

    /**
     * The department this evaluation record belongs to.
     */
    public function department()
    {
        return $this->belongsTo(Department::class, 'dept', 'dept_no');
    }

    // ──────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────

    /**
     * Whether this record has been approved by the department head.
     */
    public function isApprovedByDeptHead(): bool
    {
        return ! empty($this->depthead) && $this->depthead !== 'rejected';
    }

    /**
     * Whether this record has been approved by the general manager / HRD.
     */
    public function isApprovedByGM(): bool
    {
        return ! empty($this->generalmanager) && $this->generalmanager !== 'rejected';
    }

    /**
     * Whether this record has been rejected at any stage.
     */
    public function isRejected(): bool
    {
        return $this->depthead === 'rejected' || $this->generalmanager === 'rejected';
    }
}

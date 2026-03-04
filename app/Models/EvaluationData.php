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
/**
 * EvaluationData
 *
 * One record = one employee's monthly evaluation.
 *
 * Scoring systems:
 *   Regular (KONTRAK/TETAP) → old 5-field system: kerajinan, kerapian, prestasi, loyalitas, perilaku
 *   Yayasan / Magang        → new 9-field system: kemampuan, kecerdasan, qualitas, disiplin,
 *                             kepatuhan, lembur, efektifitas, relawan, integritas
 *
 * Approval lifecycle:
 *   pending → graded → dept_approved → fully_approved
 *                   ↘ rejected
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
        // Type & status (new columns)
        'evaluation_type',
        'approval_status',
        // Attendance
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
    // Status helpers (approval_status column)
    // ──────────────────────────────────────────────

    /** Awaiting grading by the supervisor (pengawas). */
    public function isPending(): bool
    {
        return $this->approval_status === 'pending';
    }

    /** Graded by supervisor, awaiting dept head approval. */
    public function isGraded(): bool
    {
        return $this->approval_status === 'graded';
    }

    /** Approved by dept head, awaiting HRD/GM final approval. */
    public function isDeptApproved(): bool
    {
        return $this->approval_status === 'dept_approved';
    }

    /** Fully approved by HRD/GM. Export is now available. */
    public function isFullyApproved(): bool
    {
        return $this->approval_status === 'fully_approved';
    }

    /** Rejected at any stage. */
    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    /** Whether this record can be included in an export. */
    public function canBeExported(): bool
    {
        return $this->isFullyApproved();
    }

    // ──────────────────────────────────────────────
    // Legacy status helpers (backward compat)
    // ──────────────────────────────────────────────

    /** @deprecated Use isDeptApproved() or approval_status directly. */
    public function isApprovedByDeptHead(): bool
    {
        return ! empty($this->depthead) && $this->depthead !== 'rejected';
    }

    /** @deprecated Use isFullyApproved() or approval_status directly. */
    public function isApprovedByGM(): bool
    {
        return ! empty($this->generalmanager) && $this->generalmanager !== 'rejected';
    }

    // ──────────────────────────────────────────────
    // Type Resolution
    // ──────────────────────────────────────────────

    /**
     * Resolve the evaluation type — prefers the stored `evaluation_type` column
     * (set by migration backfill) over the join-based derivation.
     *
     * @return 'yayasan'|'magang'|'regular'
     */
    public function evaluationType(): string
    {
        // Use stored column when available (fast path — no join needed)
        if (! empty($this->evaluation_type)) {
            return $this->evaluation_type;
        }

        // Fallback: derive from karyawan relationship
        $scheme = $this->karyawan?->employment_scheme ?? '';

        return match (true) {
            str_contains($scheme, 'YAYASAN') => 'yayasan',
            str_contains($scheme, 'MAGANG')  => 'magang',
            default                          => 'regular',
        };
    }

    /**
     * Whether this record uses the new 9-field scoring system (Yayasan / Magang).
     */
    public function useNewScoringSystem(): bool
    {
        return in_array($this->evaluationType(), ['yayasan', 'magang'], true);
    }
}

<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Infrastructure\Persistence\Eloquent\Models\Department;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected string $guard_name = 'web';

    /**
     * Create a new factory instance for the model.
     *
     * Required because this model is in Infrastructure namespace
     * instead of the default App\Models namespace.
     */
    protected static function newFactory()
    {
        return \Database\Factories\UserFactory::new();
    }

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'employee_id' => 'integer',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected $hidden = ['password', 'remember_token'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }


    public function getDepartmentAttribute()
    {
        if ($this->department_id) {
            return $this->department()->first();
        }

        return $this->employee ? $this->employee->department : null;
    }

    public function signatures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(UserSignature::class, 'user_id', 'id');
    }

    /**
     * Get the signature path for the current user's default active signature.
     */
    public function getSignaturePathAttribute(): ?string
    {
        $signature = $this->signatures()
            ->where('is_default', true)
            ->whereNull('revoked_at')
            ->first();

        return $signature ? route('signatures.show', $signature->id) : null;
    }
}

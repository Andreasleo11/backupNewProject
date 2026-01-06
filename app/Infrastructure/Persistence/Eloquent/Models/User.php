<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Models\Department;
use App\Models\Specification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected string $guard_name = 'web';

    protected $table = 'users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'department_id',
        'specification_id',
        'is_active',
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

    public function specification(): BelongsTo
    {
        return $this->belongsTo(Specification::class);
    }

    /**
     * Determine if the user can access the Filament admin panel.
     * Uses Spatie Permission for efficient role checking.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('super-admin');
    }
}

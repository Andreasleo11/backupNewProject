<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Models\Department;
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

    /**
     * Determine if the user can access the Filament admin panel.
     * Uses Spatie Permission for efficient role checking.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // Allow access if user has any admin-related role or is super-admin
        return $this->hasAnyRole(['super-admin', 'admin', 'manager']) || $this->hasPermission('access_admin');
    }
}

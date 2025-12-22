<?php

namespace App\Infrastructure\Persistence\Eloquent\Models;

use App\Infrastructure\Persistence\Eloquent\Models\Employee;
use App\Models\Specification;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory, HasApiTokens, Notifiable, SoftDeletes, HasRoles;

    protected $table = 'users';

    protected $fillable = [
        'name', 'email', 'password', 'is_active', 'employee_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'employee_id' => 'integer',
    ];

    protected $hidden = ['password', 'remember_token'];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return User::where('email', $this->email)->first()->role->name === 'SUPERADMIN';
    }
    
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function specification()
    {
        return $this->belongsTo(Specification::class);
    }
}
  
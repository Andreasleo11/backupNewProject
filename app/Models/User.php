<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "email",
        "password",
        "role_id",
        "department_id",
        "specification_id",
        "remember_token",
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ["password", "remember_token"];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        "email_verified_at" => "datetime",
        "password" => "hashed",
    ];

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function specification()
    {
        return $this->belongsTo(Specification::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, "role_user");
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, "permission_user");
    }

    public function hasPermission($permission)
    {
        if ($this->permissions()->where("name", $permission)->first()) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->permissions()->where("name", $permission)->first()) {
                return true;
            }
        }
        return false;
    }

    protected static function boot()
    {
        parent::boot();

        // Event listener for user created event
        static::created(function ($user) {
            $user->syncPermissions();
        });

        // Event listener for user updated event
        static::updated(function ($user) {
            $user->syncPermissions();
        });

        // Event listener for user deleted event
        static::deleted(function ($user) {
            $user->permissions()->detach(); // Detach all permissions associated with the user
        });
    }

    public function syncPermissions()
    {
        // Reload roles relationship to ensure it's up to date
        $this->load("roles");

        // Retrieve all roles associated with the user
        $roles = $this->roles;

        // Initialize an array to store permission IDs
        $permissionIdsToSync = [];

        // Retrieve permission IDs for each role
        foreach ($roles as $role) {
            $permissionIds = $role->permissions()->pluck("permissions.id")->toArray();
            $permissionIdsToSync = array_merge($permissionIdsToSync, $permissionIds);
        }

        // Sync permission IDs to the permission_user pivot table
        $this->permissions()->sync($permissionIdsToSync);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return User::where("email", $this->email)->first()->role->name === "SUPERADMIN";
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class NavUserGroup extends Model
{
    protected $fillable = ['name', 'description'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'nav_user_group_members', 'group_id', 'user_id');
    }

    public function assignments()
    {
        return $this->morphMany(NavMenuAssignment::class, 'subject');
    }
}

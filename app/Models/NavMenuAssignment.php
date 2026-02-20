<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NavMenuAssignment extends Model
{
    protected $fillable = ['route_name', 'subject_type', 'subject_id'];

    /**
     * The entity this assignment grants access to: User, Role, Permission, or NavUserGroup.
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}

<?php

namespace App\Models;

/**
 * DEPRECATED: This model is kept for backward compatibility only.
 * 
 * Please use: App\Infrastructure\Persistence\Eloquent\Models\User
 * 
 * This follows Clean Architecture pattern where models belong
 * in the Infrastructure layer, not the Domain layer.
 * 
 * @deprecated Use App\Infrastructure\Persistence\Eloquent\Models\User instead
 */
class User extends \App\Infrastructure\Persistence\Eloquent\Models\User
{
    // This class extends the Infrastructure User model
    // for backward compatibility with existing code
}

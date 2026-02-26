<?php

namespace App\Policies;

use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DisciplineAccessPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view any discipline records.
     */
    public function viewAnyDiscipline(User $user): bool
    {
        return $user->is_head === 1 || $this->isSpecialUser($user) || $user->hasRole('super-admin');
    }

    /**
     * Determine if the user can view all discipline records (across all departments).
     */
    public function viewAllDiscipline(User $user): bool
    {
        return in_array($user->email, $this->getSpecialAccessEmails(), true) || $user->hasRole('super-admin');
    }

    /**
     * Determine if the user can view Yayasan discipline records.
     */
    public function viewYayasanDiscipline(User $user): bool
    {
        // Department heads and special users can view
        return $user->is_head === 1 || $this->isSpecialUser($user) || $user->hasRole('super-admin');
    }

    /**
     * Determine if the user has special elevated access.
     */
    private function isSpecialUser(User $user): bool
    {
        // Special user ID 120 or users with special emails
        return $user->id === 120 || $this->viewAllDiscipline($user);
    }

    /**
     * Get the list of emails with special access privileges.
     * This should ideally be moved to config in the future.
     */
    private function getSpecialAccessEmails(): array
    {
        return [
            'ani_apriani@daijo.co.id',
            'bernadett@daijo.co.id',
        ];
    }
}

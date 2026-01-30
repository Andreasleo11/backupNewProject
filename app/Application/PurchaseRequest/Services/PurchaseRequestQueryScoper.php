<?php

namespace App\Application\PurchaseRequest\Services;

use App\Enums\ToDepartment;
use App\Infrastructure\Persistence\Eloquent\Models\User;
use Illuminate\Database\Eloquent\Builder;

final class PurchaseRequestQueryScoper
{
    /**
     * Apply query scoping based on user role and permissions
     */
    public function scopeForUser(User $user, Builder $query): Builder
    {
        $userDepartmentName = $user->department->name ?? null;
        $isPersonaliaHead = $userDepartmentName === 'PERSONALIA' && $user->is_head === 1;
        $isHead = $user->is_head === 1;
        $isPurchaser = $user->specification?->name === 'PURCHASER';
        $isGM = $user->is_gm === 1;

        if ($isPersonaliaHead) {
            return $this->scopeForPersonaliaHead($query);
        }

        if ($isGM) {
            return $this->scopeForGM($user, $query);
        }

        if ($isHead) {
            return $this->scopeForHead($user, $query);
        }

        if ($isPurchaser) {
            return $this->scopeForPurchaser($user, $query);
        }

        if ($user->hasRole('super-admin')) {
            return $this->scopeForSuperAdmin($query);
        }

        return $this->scopeForRegularUser($user, $query);
    }

    /**
     * Scope for Personalia Head users
     */
    private function scopeForPersonaliaHead(Builder $query): Builder
    {
        return $query->where(function ($query) {
            $query
                ->whereNotNull('autograph_1')
                ->whereNotNull('autograph_2')
                ->whereNotNull('autograph_5')
                ->where(function ($query) {
                    $query
                        ->whereNull('autograph_3')
                        ->orWhereNotNull('autograph_3')
                        ->where(function ($query) {
                            $query
                                ->where('to_department', ToDepartment::PERSONALIA->value)
                                ->where('type', 'office')
                                ->orWhere('to_department', ToDepartment::COMPUTER->value);
                        });
                })
                ->orWhere('from_department', 'PERSONALIA');
        });
    }

    /**
     * Scope for GM users
     */
    private function scopeForGM(User $user, Builder $query): Builder
    {
        $userDepartmentName = $user->department->name ?? null;

        return $query
            ->whereNotNull('autograph_1')
            ->whereNotNull('autograph_2')
            ->whereNull('autograph_6')
            ->where(function ($query) use ($userDepartmentName) {
                $query->where('type', 'factory');
                if ($userDepartmentName === 'MOULDING') {
                    $query->where('from_department', 'MOULDING');
                } else {
                    $query->where('from_department', '!=', 'MOULDING');
                }
            });
    }

    /**
     * Scope for Department Head users
     */
    private function scopeForHead(User $user, Builder $query): Builder
    {
        $userDepartmentName = $user->department->name ?? null;

        $query->where(function ($query) use ($userDepartmentName) {
            $query->where('from_department', $userDepartmentName);
        });

        if ($userDepartmentName === 'PURCHASING') {
            $query->orWhere('to_department', ToDepartment::PURCHASING->value);
        } elseif ($userDepartmentName === 'LOGISTIC') {
            $query->orWhere('from_department', 'STORE');
        }

        return $query;
    }

    /**
     * Scope for Purchaser users
     */
    private function scopeForPurchaser(User $user, Builder $query): Builder
    {
        $userDepartmentName = $user->department->name ?? null;

        $query->where(function ($query) {
            $query
                ->where(function ($query) {
                    $query->where(function ($query) {
                        $query->where('type', 'office')->orWhere('from_department', 'MOULDING');
                    });
                })
                ->orWhere(function ($query) {
                    $query->where('type', 'factory');
                });
        });

        if ($userDepartmentName === 'COMPUTER' || $userDepartmentName === 'PURCHASING') {
            $query->where('to_department', ToDepartment::COMPUTER->value);
        } elseif ($user->email === 'nur@daijo.co.id') {
            $query->where('to_department', ToDepartment::MAINTENANCE->value);
        } elseif ($userDepartmentName === 'PERSONALIA') {
            $query->where('to_department', ToDepartment::PERSONALIA->value);
        }

        $query->whereNotNull('autograph_1');

        return $query;
    }

    /**
     * Scope for Super Admin users
     */
    private function scopeForSuperAdmin(Builder $query): Builder
    {
        return $query;
    }

    /**
     * Scope for regular users
     */
    private function scopeForRegularUser(User $user, Builder $query): Builder
    {
        $userDepartmentName = $user->department->name ?? null;

        return $query->where('from_department', $userDepartmentName);
    }
}

<?php

namespace App\Support;

use App\Models\PurchaseRequest;
use App\Models\User;

final class PurchaseRequestAccess
{
    public static function canView(User $user, PurchaseRequest $pr): bool
    {
        $userDepartmentName = $user->department->name ?? null;
        $isPersonaliaHead = $userDepartmentName === 'PERSONALIA' && (int) $user->is_head === 1;
        $isHead = (int) $user->is_head === 1;
        $isPurchaser = ($user->specification->name ?? null) === 'PURCHASER';
        $isGM = (int) $user->is_gm === 1;

        // SUPERADMIN (matches your index logic style)
        if (($user->role->name ?? null) === 'SUPERADMIN') {
            return $pr->autograph_1 !== null;
        }

        // Personalia head rule (taken from your index query intent)
        if ($isPersonaliaHead) {
            // Personalia head can see:
            // - PRs already signed by maker+depthead+purchaser
            // - AND (to Personnel office OR to Computer) logic
            // - OR PRs from PERSONALIA
            if ($pr->from_department === 'PERSONALIA') {
                return true;
            }

            $baseSigned = $pr->autograph_1 && $pr->autograph_2 && $pr->autograph_5;
            if (!$baseSigned) return false;

            // allow if to Personnel (office) or Computer, similar to your query block
            if ($pr->to_department === 'Personnel' && $pr->type === 'office') return true;
            if ($pr->to_department === 'Computer') return true;

            return true; // keep permissive for now, since your original query is complex
        }

        // GM rule: must have autograph_1 and autograph_2 and no autograph_6
        if ($isGM) {
            if (!($pr->autograph_1 && $pr->autograph_2) || $pr->autograph_6) {
                return false;
            }
            if ($pr->type !== 'factory') return false;

            if ($userDepartmentName === 'MOULDING') {
                return $pr->from_department === 'MOULDING';
            }

            return $pr->from_department !== 'MOULDING';
        }

        // Head rule: can see own dept PR
        if ($isHead) {
            if ($pr->from_department === $userDepartmentName) return true;

            if ($userDepartmentName === 'PURCHASING') {
                return $pr->to_department === 'Purchasing';
            }

            if ($userDepartmentName === 'LOGISTIC') {
                return $pr->from_department === 'STORE';
            }

            return false;
        }

        // Purchaser rule
        if ($isPurchaser) {
            if (!$pr->autograph_1) return false;

            // mirrors your query logic loosely
            if ($userDepartmentName === 'COMPUTER') {
                return $pr->to_department === 'Computer';
            }
            if ($userDepartmentName === 'PURCHASING') {
                return $pr->to_department === 'Purchasing';
            }
            if ($user->email === 'nur@daijo.co.id') {
                return $pr->to_department === 'Maintenance';
            }
            if ($userDepartmentName === 'PERSONALIA') {
                return $pr->to_department === 'Personnel';
            }

            // otherwise purchaser sees signed ones (as per your index)
            return true;
        }

        // Default: creator / same dept
        if ($pr->from_department === $userDepartmentName) return true;

        // creator can always view
        if ((int)$pr->user_id_create === (int)$user->id) return true;

        return false;
    }
}

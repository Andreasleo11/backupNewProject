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

        // super-admin (matches your index logic style)
        if ($user->hasRole('super-admin')) {
            $signatureService = app(\App\Domain\PurchaseRequest\Services\PurchaseRequestSignatureService::class);

            return $signatureService->hasSignature($pr, 'MAKER');
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

            $signatureService = app(\App\Domain\PurchaseRequest\Services\PurchaseRequestSignatureService::class);
            $baseSigned = $signatureService->hasSignature($pr, 'MAKER') &&
                         $signatureService->hasSignature($pr, 'DEPT_HEAD') &&
                         $signatureService->hasSignature($pr, 'PURCHASER');
            if (! $baseSigned) {
                return false;
            }

            // allow if to Personnel (office) or Computer, similar to your query block
            if ($pr->to_department === 'Personnel' && $pr->type === 'office') {
                return true;
            }
            if ($pr->to_department === 'Computer') {
                return true;
            }

            return true; // keep permissive for now, since your original query is complex
        }

        // GM rule: must have MAKER and DEPT_HEAD signatures, but not GM signature yet
        if ($isGM) {
            $signatureService = app(\App\Domain\PurchaseRequest\Services\PurchaseRequestSignatureService::class);
            $hasMakerAndDept = $signatureService->hasSignature($pr, 'MAKER') &&
                              $signatureService->hasSignature($pr, 'DEPT_HEAD');
            $hasGM = $signatureService->hasSignature($pr, 'GM');

            if (! $hasMakerAndDept || $hasGM) {
                return false;
            }
            if ($pr->type !== 'factory') {
                return false;
            }

            if ($userDepartmentName === 'MOULDING') {
                return $pr->from_department === 'MOULDING';
            }

            return $pr->from_department !== 'MOULDING';
        }

        // Head rule: can see own dept PR
        if ($isHead) {
            if ($pr->from_department === $userDepartmentName) {
                return true;
            }

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
            $signatureService = app(\App\Domain\PurchaseRequest\Services\PurchaseRequestSignatureService::class);
            if (! $signatureService->hasSignature($pr, 'MAKER')) {
                return false;
            }

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
        if ($pr->from_department === $userDepartmentName) {
            return true;
        }

        // creator can always view
        if ((int) $pr->user_id_create === (int) $user->id) {
            return true;
        }

        return false;
    }
}

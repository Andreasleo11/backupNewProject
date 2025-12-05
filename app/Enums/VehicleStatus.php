<?php

namespace App\Enums;

enum VehicleStatus: string
{
    case ACTIVE = 'active';
    case MAINTENANCE = 'maintenance';
    case RETIRED = 'retired';
    case SOLD = 'sold';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::MAINTENANCE => 'Maintenance',
            self::RETIRED => 'Retired',
            self::SOLD => 'Sold',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::ACTIVE => 'check2-circle',
            self::MAINTENANCE => 'tools',
            self::RETIRED => 'archive',
            self::SOLD => 'cart-check',
        };
    }

    /**
     * Tailwind badge classes (chip status).
     */
    public function badgeClasses(): string
    {
        return match ($this) {
            self::ACTIVE => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
            self::MAINTENANCE => 'bg-amber-100 text-amber-700 ring-1 ring-amber-200',
            self::RETIRED => 'bg-slate-100 text-slate-700 ring-1 ring-slate-200',
            self::SOLD => 'bg-rose-100 text-rose-700 ring-1 ring-rose-200',
        };
    }

    /**
     * Alias lama: kalau ada view yang masih pakai ->variant()
     */
    public function variant(): string
    {
        return $this->badgeClasses();
    }

    /**
     * Classes untuk tombol filter saat AKTIF (selected).
     */
    public function filterActiveClasses(): string
    {
        return match ($this) {
            self::ACTIVE => 'border-emerald-600 bg-emerald-600 text-white',
            self::MAINTENANCE => 'border-amber-500 bg-amber-500 text-white',
            self::RETIRED => 'border-slate-600 bg-slate-600 text-white',
            self::SOLD => 'border-rose-600 bg-rose-600 text-white',
        };
    }

    /**
     * Classes untuk tombol filter saat TIDAK aktif.
     */
    public function filterInactiveClasses(): string
    {
        return match ($this) {
            self::ACTIVE => 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
            self::MAINTENANCE => 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100',
            self::RETIRED => 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100',
            self::SOLD => 'border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100',
        };
    }

    /**
     * Tailwind classes khusus untuk state "checked" pada radio group.
     * Perhatikan: setiap class sudah diprefix dengan `peer-checked:`.
     */
    public function radioCheckedClasses(): string
    {
        return match ($this) {
            self::ACTIVE => 'peer-checked:bg-emerald-100 peer-checked:text-emerald-700 peer-checked:ring-1 peer-checked:ring-emerald-200 peer-checked:border-emerald-300',
            self::MAINTENANCE => 'peer-checked:bg-amber-100 peer-checked:text-amber-700 peer-checked:ring-1 peer-checked:ring-amber-200 peer-checked:border-amber-300',
            self::RETIRED => 'peer-checked:bg-slate-100 peer-checked:text-slate-700 peer-checked:ring-1 peer-checked:ring-slate-200 peer-checked:border-slate-300',
            self::SOLD => 'peer-checked:bg-rose-100 peer-checked:text-rose-700 peer-checked:ring-1 peer-checked:ring-rose-200 peer-checked:border-rose-300',
        };
    }
}

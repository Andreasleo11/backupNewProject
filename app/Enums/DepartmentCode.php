<?php

namespace App\Enums;

enum DepartmentCode: string
{
    case ACCOUNTING = '100';
    case BUSINESS = '200';
    case PERSONALIA = '310';
    case PPIC = '311';
    case PURCHASING = '320';
    case STORE = '330';
    case LOGISTIC = '331';
    case QC = '340';
    case QA = '341';
    case MAINTENANCE = '350';
    case MAINTENANCE_MACHINE = '351';
    case SECOND_PROCESS = '361';
    case ASSEMBLY = '362';
    case MOULDING = '363';
    case PLASTIC_INJECTION = '390';
    case PE = '500';
    case COMPUTER = '600';

    public function getLabel(): string
    {
        return match ($this) {
            self::ACCOUNTING => 'Accounting',
            self::BUSINESS => 'Business',
            self::PERSONALIA => 'Personalia',
            self::PPIC => 'PPIC',
            self::PURCHASING => 'Purchasing',
            self::STORE => 'Store',
            self::LOGISTIC => 'Logistic',
            self::QC => 'QC',
            self::QA => 'QA',
            self::MAINTENANCE => 'Maintenance',
            self::MAINTENANCE_MACHINE => 'Maintenance Machine',
            self::SECOND_PROCESS => 'Second Process',
            self::ASSEMBLY => 'Assembly',
            self::MOULDING => 'Moulding',
            self::PLASTIC_INJECTION => 'Plastic Injection',
            self::PE => 'PE',
            self::COMPUTER => 'Computer',
        };
    }

    public static function fromDepartmentName(string $name): ?self
    {
        return match (strtoupper(trim($name))) {
            'ACCOUNTING' => self::ACCOUNTING,
            'BUSINESS' => self::BUSINESS,
            'PERSONALIA' => self::PERSONALIA,
            'PPIC' => self::PPIC,
            'PURCHASING' => self::PURCHASING,
            'STORE' => self::STORE,
            'LOGISTIC' => self::LOGISTIC,
            'QC' => self::QC,
            'QA' => self::QA,
            'MAINTENANCE' => self::MAINTENANCE,
            'MAINTENANCE MACHINE' => self::MAINTENANCE_MACHINE,
            'SECOND PROCESS' => self::SECOND_PROCESS,
            'ASSEMBLY' => self::ASSEMBLY,
            'MOULDING' => self::MOULDING,
            'PLASTIC INJECTION' => self::PLASTIC_INJECTION,
            'PE' => self::PE,
            'COMPUTER' => self::COMPUTER,
            default => null,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Services;

use App\Exports\InventoryMasterExport;
use App\Models\DetailHardware;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

final class InventoryExportService
{
    /**
     * Download inventory master export.
     */
    public function downloadExport(): BinaryFileResponse
    {
        return Excel::download(new InventoryMasterExport, 'listKomputer.xlsx');
    }

    /**
     * Generate QR code for a hardware item.
     */
    public function generateQrCode(int $detailHardwareId): array
    {
        $data = DetailHardware::with('masterInventory', 'hardwareType')->findOrFail($detailHardwareId);

        $qrData = sprintf(
            '%s~%s~%s~%s',
            $data->brand,
            $data->hardwareType->name,
            $data->hardware_name,
            $data->remark
        );

        $qrCode = new QrCode(
            data: $qrData,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 100,
            margin: 5,
        );

        $writer = new PngWriter;
        $qrCodeResult = $writer->write($qrCode);

        $base64Image = base64_encode($qrCodeResult->getString());

        return [
            'qrcoded' => $base64Image,
            'data' => $data,
        ];
    }
}

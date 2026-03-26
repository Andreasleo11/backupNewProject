<?php

declare(strict_types=1);

namespace App\Application\PurchaseRequest\DTOs;

final class CreatePurchaseRequestDTO
{
    /** @param list<PurchaseRequestItemDTO> $items */
    public function __construct(
        public readonly int $requestedByUserId,
        public readonly string $fromDepartment,
        public readonly string $toDepartment, // normalized: PURCHASING/PERSONALIA/...
        public readonly string $branch,
        public readonly string $datePr,
        public readonly string $dateRequired,
        public readonly ?string $remark,
        public readonly string $supplier,
        public readonly string $pic,
        public readonly bool $isDraft,
        public readonly ?bool $isImport,
        /** @var list<PurchaseRequestItemDTO> */
        public readonly array $items,
    ) {}

    public static function fromValidated(
        \Illuminate\Http\Request $request,
        \App\Domain\PurchaseRequest\Services\PriceSanitizer $priceSanitizer
    ): self {
        $user = $request->user();

        $items = array_map(function ($item) use ($priceSanitizer) {
            return new PurchaseRequestItemDTO(
                itemName: (string) $item['item_name'],
                quantity: (float) $item['quantity'],
                purpose: (string) ($item['purpose'] ?? ''),
                price: $priceSanitizer->sanitize($item['price'] ?? 0),
                uom: (string) $item['uom'],
                currency: (string) ($item['currency'] ?? 'IDR'),
            );
        }, $request->input('items', []));

        return new self(
            requestedByUserId: (int) $user->id,
            fromDepartment: (string) $request->input('from_department'),
            toDepartment: (string) $request->input('to_department'),
            branch: (string) $request->branch,
            datePr: (string) $request->input('date_of_pr'),
            dateRequired: (string) $request->input('date_of_required'),
            remark: $request->input('remark'), // nullable
            supplier: (string) $request->input('supplier'),
            pic: (string) $request->input('pic'),
            isDraft: (bool) $request->is_draft,
            isImport: $request->has('is_import') ? filter_var($request->is_import, FILTER_VALIDATE_BOOLEAN) : null,
            items: $items
        );
    }
}

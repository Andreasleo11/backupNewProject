<?php

declare(strict_types=1);

namespace App\Livewire\Signature;

use App\Application\Signature\UseCases\RevokeSignature;
use App\Application\Signature\UseCases\SetDefaultSignature;
use App\Domain\Signature\Repositories\UserSignatureRepository;
use Livewire\Attributes\Url;
use Livewire\Component;

final class ManageSignatures extends Component
{
    #[Url]
    public ?string $return_to = null;

    /** @var array<int, array> */
    public array $items = [];

    public function mount(UserSignatureRepository $repo): void
    {
        $this->loadData($repo);
    }

    private function loadData(UserSignatureRepository $repo): void
    {
        $entities = $repo->listByUser(auth()->id(), true); // only active by default
        $this->items = array_map(function ($e) {
            return [
                'id' => $e->id,
                'label' => $e->label,
                'is_default' => $e->isDefault,
                'revoked_at' => $e->revokedAt?->format('c'),
                'url' => route('signatures.show', $e->id),
            ];
        }, $entities);
    }

    public function setDefault(int $id, SetDefaultSignature $uc): void
    {
        try {
            $uc->handle((int) auth()->id(), $id);
            $this->dispatch('toast', message: 'Default signature updated', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }

        $this->loadData(app(UserSignatureRepository::class));
    }

    public function revoke(int $id, RevokeSignature $uc): void
    {
        try {
            $uc->handle((int) auth()->id(), $id, reason: 'user action');
            $this->dispatch('toast', message: 'Signature revoked', type: 'success');
        } catch (\Exception $e) {
            $this->dispatch('toast', message: $e->getMessage(), type: 'error');
        }

        $this->loadData(app(UserSignatureRepository::class));
    }

    public function render()
    {
        return view('livewire.signature.manage-signatures');
    }
}

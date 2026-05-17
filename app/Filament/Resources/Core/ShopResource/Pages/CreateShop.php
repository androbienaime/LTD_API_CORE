<?php

namespace App\Filament\Resources\Core\ShopResource\Pages;

use App\Filament\Resources\Core\ShopResource;
use App\Services\ShopMembershipService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateShop extends CreateRecord
{
    protected static string $resource = ShopResource::class;

    /**
     * Hook appelé après le save Filament — on délègue au service.
     */
    protected function afterSave(): void
    {
        dd($this->record);
        /** @var ShopMembershipService $service */
        $service = app(ShopMembershipService::class);

        $members = $this->form->getRawState()['memberships'] ?? [];

        $service->sync($this->record, $members);
    }

      /**
     * À la création, ajouter l'account connecté comme super_admin.
     */
protected function afterCreate(): void
{
    /** @var Account|null $account */
    $account = auth('account')->user();

    /** @var ShopMembershipService $service */
    $service = app(ShopMembershipService::class);

    $members = $this->form->getRawState()['memberships'] ?? [];

    // ← On n'auto-ajoute que si c'est bien un account connecté
    if ($account) {
        $alreadyIncluded = collect($members)
            ->pluck('account_id')
            ->contains($account->id);

        if (! $alreadyIncluded) {
            $superAdminRole = \Spatie\Permission\Models\Role::findByName('super-admin', 'account');

            $members[] = [
                'account_id' => $account->id,
                'role_id'    => $superAdminRole->id,
            ];
        }
    }

    $service->sync($this->record, $members);
}
}

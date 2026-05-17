<?php

namespace App\Filament\Resources\Core\ShopResource\Pages;

use App\Filament\Resources\Core\ShopResource;
use App\Services\ShopMembershipService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditShop extends EditRecord
{
    protected static string $resource = ShopResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

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
}

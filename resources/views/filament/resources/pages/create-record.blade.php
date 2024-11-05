<x-filament-panels::page
    @class([
        'fi-resource-create-record-page',
        'fi-resource-' . str_replace('/', '-', $this->getResource()::getSlug()),
    ])
>
    <x-filament-panels::form
        id="form"
        :wire:key="$this->getId() . '.forms.' . $this->getFormStatePath()"
        wire:submit="create"
    >
        <div class="relative min-h-screen pb-16">
            <!-- Votre contenu du formulaire ici -->
            {{ $this->form }}

            <div class="py-4">
                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />
            </div>
        </div>

    </x-filament-panels::form>

    <x-filament-panels::page.unsaved-data-changes-alert />
</x-filament-panels::page>



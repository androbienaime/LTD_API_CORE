<?php

namespace App\Filament\Auth\Shop;

use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse as Responsable;
use Illuminate\Http\RedirectResponse;
use Livewire\Features\SupportRedirects\Redirector;

class LoginResponse implements Responsable
{
    public function toResponse($request): RedirectResponse | Redirector
    {
        // Récupérer le panel actuel
        $panel = filament()->getCurrentPanel();

        // Récupérer le path du panel
        $panelPath = $panel->getPath() ?? '/';

        return redirect()->intended($panelPath);
    }
}

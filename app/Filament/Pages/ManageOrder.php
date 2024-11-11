<?php

namespace App\Filament\Pages;

use App\Settings\OrderSettings;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageOrder extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = OrderSettings::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // ...
            ]);
    }
}

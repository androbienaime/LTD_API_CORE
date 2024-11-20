<?php

namespace App\Filament\Pages;

use App\Settings\OrderSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class ManageOrder extends SettingsPage
{

    protected static string $settings = OrderSetting::class;

    public static function getNavigationGroup() : string {
        return __("Settings");
    }
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make("percentValuesProcessingOrder")
            ]);
    }
}

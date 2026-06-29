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
            Forms\Components\TextInput::make("percentValuesProcessingOrder"),

            Forms\Components\Select::make("keyReferencesMethod")
                ->options([
                    "increment" => __("Increment"),
                    "random"    => __("Random"),
                ])
                ->default("increment")
                ->label(__("Key References Method"))
                ->live(), // ← déclenche le re-render des champs dépendants

            Forms\Components\TextInput::make("keyReferences")
                ->label(__("Key References"))
                ->numeric(
                    fn (Forms\Get $get) => $get("keyReferencesMethod") === "increment"
                )
                ->rules(
                    fn (Forms\Get $get) => $get("keyReferencesMethod") === "increment"
                        ? ["nullable", "integer", "min:0"]
                        : ["nullable", "string"]
                ),
        ]);
    }
}

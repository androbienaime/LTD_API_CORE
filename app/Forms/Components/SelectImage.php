<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;

class SelectImage extends Select
{


//    protected string $view = 'forms.components.select-image';

    protected function setUp(): void
    {
        parent::setUp();

        $this->optionsLimit(50)
            ->searchable()
            ->selectablePlaceholder(false);

        // Personnaliser l'affichage de l'option
        $this->getOptionLabelUsing(function ($value) {
            $options = $this->getOptions();
            if (!isset($options[$value])) {
                return $value;
            }

            $option = $options[$value];
            $label = is_array($option) ? ($option['label'] ?? $value) : $option;
            $imageUrl = is_array($option) ? ($option['image_url'] ?? null) : null;

            // On retourne juste le texte pour le label
            return $label;
        });

        // Personnaliser le rendu HTML de l'option
        $this->getOptionLabelsUsing(function () {
            $options = $this->getOptions();

            return collect($options)->map(function ($option, $value) {
                $label = is_array($option) ? ($option['label'] ?? $value) : $option;
                $imageUrl = is_array($option) ? ($option['image_url'] ?? null) : null;

                return view('components.select-image', [
                    'label' => $label,
                    'imageUrl' => $imageUrl,
                ])->render();
            })->toArray();

        });
    }
}

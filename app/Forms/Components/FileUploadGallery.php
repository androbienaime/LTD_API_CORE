<?php

namespace App\Forms\Components;

use Filament\Support\Enums\Alignment;
use Filament\Forms\Components\Component;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Facades\FilamentAsset;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class FileUploadGallery extends SpatieMediaLibraryFileUpload
{
    protected string $view = 'forms.components.file-upload-gallery';

    public static function make(): static
    {
        return app(static::class);
    }
}

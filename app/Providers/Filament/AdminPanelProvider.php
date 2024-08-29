<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin2017ltv')
            ->login()
            ->colors([
                'primary' => '#1d63a8',
                'secondary' => "#efba20"
            ])
            ->navigationGroups([
                NavigationGroup::make("Orders")
                 ->label('Orders')
                 ->icon('heroicon-o-shopping-cart')
                 ->collapsed(),
                NavigationGroup::make("Catalogs")
                 ->label('Catalogs')
                 ->icon('heroicon-o-shopping-cart'),
                 NavigationGroup::make("Customers")
                 ->label('Customers')
                 ->icon('heroicon-o-users'),
                 NavigationGroup::make("Shipping")
                 ->label('Shipping')
                 ->icon('heroicon-o-truck'),
                 NavigationGroup::make("International")
                 ->label('International')
                 ->icon('heroicon-o-globe-americas'),
                 NavigationGroup::make("Module")
                 ->label('Module')
                 ->icon('heroicon-o-plus-circle'),
                 NavigationGroup::make("Shops")
                 ->label('Shops')
                 ->icon('heroicon-o-shopping-bag'),
                 NavigationGroup::make("Settings")
                 ->label('Settings')
                 ->icon('heroicon-o-cog-8-tooth'),
            ])
            ->sidebarWidth('16rem')
            ->brandLogo(fn () => view('filament.admin.logo'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}

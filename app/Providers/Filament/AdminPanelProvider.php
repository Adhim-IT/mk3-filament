<?php

namespace App\Providers\Filament;

use Althinect\FilamentSpatieRolesPermissions\FilamentSpatieRolesPermissionsPlugin;
use Althinect\FilamentSpatieRolesPermissions\Resources\PermissionResource;
use App\Filament\Pages\Auth\Login;
use App\Filament\Resources\AuthorResource;
use App\Filament\Resources\BookResource;
use App\Filament\Resources\BorrowResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\RoleResource;
use App\Filament\Resources\UserResource;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationBuilder;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->colors([
                'primary' => Color::Amber,
            ])
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
            ])
            ->plugin(FilamentSpatieRolesPermissionsPlugin::make())
            ->navigation(function (NavigationBuilder $builder): NavigationBuilder {
                $user = auth()->user();
                $isAdmin = $user && $user->role_id == 1;

                $navigationItems = [];
                $settingItems = [];

                if ($isAdmin) {
                    $navigationItems = [
                        ...BookResource::getNavigationItems(),
                        ...BorrowResource::getNavigationItems(),
                        ...CategoryResource::getNavigationItems(),
                        ...AuthorResource::getNavigationItems(),
                        ...UserResource::getNavigationItems(),
                    ];

                    $settingItems = [
                        ...RoleResource::getNavigationItems(),
                        ...PermissionResource::getNavigationItems(),
                    ];
                }

                return $builder->groups([
                    NavigationGroup::make()
                        ->items([
                            NavigationItem::make('Dashboard')
                                ->icon('heroicon-o-home')
                                ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.dashboard'))
                                ->url(fn (): string => Dashboard::getUrl()),
                        ]),
                    NavigationGroup::make('Pages')
                        ->items($navigationItems),
                    NavigationGroup::make('Settings')
                        ->items($settingItems),
                ]);
            });
    }
}

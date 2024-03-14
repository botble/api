<?php

namespace Botble\Api\Providers;

use Botble\Api\Facades\ApiHelper;
use Botble\Api\Http\Middleware\ForceJsonResponseMiddleware;
use Botble\Api\Models\PersonalAccessToken;
use Botble\Base\Facades\DashboardMenu;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Supports\ServiceProvider;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Setting\PanelSections\SettingCommonPanelSection;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Events\RouteMatched;
use Laravel\Sanctum\Sanctum;

class ApiServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        if (class_exists('ApiHelper')) {
            AliasLoader::getInstance()->alias('ApiHelper', ApiHelper::class);
        }
    }

    public function boot(): void
    {
        $this
            ->setNamespace('packages/api')
            ->loadRoutes()
            ->loadAndPublishConfigurations(['api', 'permissions'])
            ->loadAndPublishTranslations()
            ->loadMigrations()
            ->loadHelpers()
            ->loadAndPublishViews();

        if (ApiHelper::enabled()) {
            $this->loadRoutes(['api']);
        }

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        DashboardMenu::default()->beforeRetrieving(function () {
            DashboardMenu::make()
                ->registerItem([
                    'id' => 'cms-plugins-sanctum-token',
                    'name' => trans('packages/api::sanctum-token.name'),
                    'icon' => 'ti ti-key',
                    'url' => route('api.sanctum-token.index'),
                    'permissions' => ['api.sanctum-token.index'],
                ]);
        });

        $this->app['events']->listen(RouteMatched::class, function () {
            if (ApiHelper::enabled()) {
                $this->app['router']->pushMiddlewareToGroup('api', ForceJsonResponseMiddleware::class);
            }

            if (version_compare('7.0.0', get_core_version(), '>=')) {
                DashboardMenu::registerItem([
                    'id' => 'cms-packages-api',
                    'priority' => 9999,
                    'parent_id' => 'cms-core-settings',
                    'name' => 'packages/api::api.settings',
                    'icon' => null,
                    'url' => route('api.settings'),
                    'permissions' => ['api.settings'],
                ]);
            } else {
                PanelSectionManager::default()
                    ->registerItem(
                        SettingCommonPanelSection::class,
                        fn () => PanelSectionItem::make('settings.common.api')
                            ->setTitle(trans('packages/api::api.settings'))
                            ->withDescription(trans('packages/api::api.settings_description'))
                            ->withIcon('ti ti-api')
                            ->withPriority(110)
                            ->withRoute('api.settings')
                    );
            }
        });

        $this->app->booted(function () {
            config([
                'scribe.routes.0.match.prefixes' => ['api/*'],
                'scribe.routes.0.apply.headers' => [
                    'Authorization' => 'Bearer {token}',
                    'Api-Version' => 'v1',
                ],
            ]);
        });
    }

    protected function getPath(string|null $path = null): string
    {
        return __DIR__ . '/../..' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

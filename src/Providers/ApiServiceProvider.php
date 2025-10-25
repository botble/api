<?php

namespace Tiryaq\Api\Providers;

use Tiryaq\Api\Commands\GenerateDocumentationCommand;
use Tiryaq\Api\Commands\ProcessScheduledNotificationsCommand;
use Tiryaq\Api\Commands\SendPushNotificationCommand;
use Tiryaq\Api\Facades\ApiHelper;
use Tiryaq\Api\Http\Middleware\ApiEnabledMiddleware;
use Tiryaq\Api\Http\Middleware\ApiKeyMiddleware;
use Tiryaq\Api\Http\Middleware\ForceJsonResponseMiddleware;
use Tiryaq\Api\Models\PersonalAccessToken;
use Tiryaq\Base\Events\SystemUpdateDBMigrated;
use Tiryaq\Base\Facades\PanelSectionManager;
use Tiryaq\Base\PanelSections\PanelSectionItem;
use Tiryaq\Base\Supports\ServiceProvider;
use Tiryaq\Base\Traits\LoadAndPublishDataTrait;
use Tiryaq\Setting\PanelSections\SettingCommonPanelSection;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Routing\Events\RouteMatched;
use Laravel\Sanctum\Sanctum;

class ApiServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        $this->app['config']->set([
            'scribe.routes.0.match.prefixes' => ['api/*'],
            'scribe.routes.0.apply.headers' => [
                'Authorization' => 'Bearer {token}',
                'Api-Version' => 'v1',
            ],
        ]);

        if (class_exists('ApiHelper')) {
            AliasLoader::getInstance()->alias('ApiHelper', ApiHelper::class);
        }
    }

    public function boot(): void
    {
        if (version_compare('7.2.0', get_core_version(), '>')) {
            return;
        }

        $this
            ->setNamespace('packages/api')
            ->loadRoutes()
            ->loadAndPublishConfigurations(['api', 'permissions'])
            ->loadAndPublishTranslations()
            ->loadMigrations()
            ->loadAndPublishViews()
            ->publishAssets()
            ->loadRoutes(['api']);

        Sanctum::usePersonalAccessTokenModel(PersonalAccessToken::class);

        $this->app['events']->listen(RouteMatched::class, function () {
            // Always add the API enabled middleware first
            $this->app['router']->pushMiddlewareToGroup('api', ApiEnabledMiddleware::class);

            // Add force JSON response middleware
            $this->app['router']->pushMiddlewareToGroup('api', ForceJsonResponseMiddleware::class);

            // Add API key middleware if API key is configured
            if (ApiHelper::hasApiKey()) {
                $this->app['router']->pushMiddlewareToGroup('api', ApiKeyMiddleware::class);
            }
        });

        PanelSectionManager::beforeRendering(function () {
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
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateDocumentationCommand::class,
                ProcessScheduledNotificationsCommand::class,
                SendPushNotificationCommand::class,
            ]);
        }

        $this->app->booted(function () {
            add_filter('core_acl_role_permissions', function (array $permissions) {
                $apiPermissions = $this->app['config']->get('packages.api.permissions', []);

                if (! $apiPermissions) {
                    return $permissions;
                }

                foreach ($apiPermissions as $permission) {
                    $permissions[$permission['flag']] = $permission;
                }

                return $permissions;
            }, 120);
        });

        $this->app['events']->listen(SystemUpdateDBMigrated::class, function () {
            $this->app['migrator']->run($this->getPath('database/migrations'));
        });
    }

    protected function getPath(?string $path = null): string
    {
        return __DIR__ . '/../..' . ($path ? '/' . ltrim($path, '/') : '');
    }
}

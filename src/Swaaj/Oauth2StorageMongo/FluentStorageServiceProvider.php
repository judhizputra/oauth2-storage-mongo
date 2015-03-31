<?php
/**
 * Fluent Storage Service Provider for the OAuth 2.0 Server
 *
 * @package   lucadegasperi/oauth2-server-laravel
 * @author    Luca Degasperi <luca@lucadegasperi.com>
 * @copyright Copyright (c) Luca Degasperi
 * @licence   http://mit-license.org/
 * @link      https://github.com/lucadegasperi/oauth2-server-laravel
 */

namespace Swaaj\Oauth2StorageMongo;

use Illuminate\Support\ServiceProvider;

class FluentStorageServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->package('swaaj/oauth2-storage-mongo', 'oauth2-storage-mongo', __DIR__.'/');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerStorageBindings();
        $this->registerInterfaceBindings();
    }

    /**
     * Bind the storage implementations to the IoC container
     * @return void
     */
    public function registerStorageBindings()
    {
        $provider = $this;

        $this->app->bindShared('Swaaj\Oauth2StorageMongo\FluentAccessToken', function () use ($provider) {
            $storage = new FluentAccessToken($provider->app['db']);
            $storage->setConnectionName($provider->getConnectionName());
            return $storage;
        });

        $this->app->bindShared('Swaaj\Oauth2StorageMongo\FluentAuthCode', function () use ($provider) {
            $storage = new FluentAuthCode($provider->app['db']);
            $storage->setConnectionName($provider->getConnectionName());
            return $storage;
        });

        $this->app->bindShared('Swaaj\Oauth2StorageMongo\FluentClient', function ($app) use ($provider) {
            $limitClientsToGrants = $app['config']->get('oauth2-server-laravel::oauth2.limit_clients_to_grants');
            $storage = new FluentClient($provider->app['db'], $limitClientsToGrants);
            $storage->setConnectionName($provider->getConnectionName());
            return $storage;
        });

        $this->app->bindShared('Swaaj\Oauth2StorageMongo\FluentRefreshToken', function () use ($provider) {
            $storage = new FluentRefreshToken($provider->app['db']);
            $storage->setConnectionName($provider->getConnectionName());
            return $storage;
        });

        $this->app->bindShared('Swaaj\Oauth2StorageMongo\FluentScope', function ($app) use ($provider) {
            $limitClientsToScopes = $app['config']->get('oauth2-server-laravel::oauth2.limit_clients_to_scopes');
            $limitScopesToGrants = $app['config']->get('oauth2-server-laravel::oauth2.limit_scopes_to_grants');
            $storage = new FluentScope($provider->app['db'], $limitClientsToScopes, $limitScopesToGrants);
            $storage->setConnectionName($provider->getConnectionName());
            return $storage;
        });

        $this->app->bindShared('Swaaj\Oauth2StorageMongo\FluentSession', function () use ($provider) {
            $storage = new FluentSession($provider->app['db']);
            $storage->setConnectionName($provider->getConnectionName());
            return $storage;
        });
    }

    /**
     * Bind the interfaces to their implementations
     * @return void
     */
    public function registerInterfaceBindings()
    {
        $this->app->bind('League\OAuth2\Server\Storage\ClientInterface',       'Swaaj\Oauth2StorageMongo\FluentClient');
        $this->app->bind('League\OAuth2\Server\Storage\ScopeInterface',        'Swaaj\Oauth2StorageMongo\FluentScope');
        $this->app->bind('League\OAuth2\Server\Storage\SessionInterface',      'Swaaj\Oauth2StorageMongo\FluentSession');
        $this->app->bind('League\OAuth2\Server\Storage\AuthCodeInterface',     'Swaaj\Oauth2StorageMongo\FluentAuthCode');
        $this->app->bind('League\OAuth2\Server\Storage\AccessTokenInterface',  'Swaaj\Oauth2StorageMongo\FluentAccessToken');
        $this->app->bind('League\OAuth2\Server\Storage\RefreshTokenInterface', 'Swaaj\Oauth2StorageMongo\FluentRefreshToken');
    }

    /**
     * @return string
     */
    public function getConnectionName()
    {
        return ($this->app['config']->get('oauth2-server-laravel::oauth2.database') !== 'default') ? $this->app['config']->get('oauth2-server-laravel::oauth2.database') : null;
    }
}
 
<?php

namespace ProSales\WebSms;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Support\ServiceProvider;
use ProSales\WebSms\Exceptions\InvalidConfigException;

class WebSmsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Bootstrap the provider
     *
     * @return void
     */
    public function boot()
    {
        // Config file path.
        $dist = __DIR__.'/config/websms.php';

        // If we're installing in to a Lumen project, config_path
        // won't exist so we can't auto-publish the config
        if (function_exists('config_path')) {
            // Publishes config File.
            $this->publishes([
                $dist => config_path('websms.php'),
            ]);
        }

        // Merge config.
        $this->mergeConfigFrom($dist, 'websms');
    }

    /**
     * Register the service provider
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(WebSmsClient::class, function ($app) {
           return $this->createClient($app['config']);
        });
    }

    /**
     * Return the classes which are provided by this service provider
     *
     * @return array
     */
    public function provides()
    {
        return [
            WebSmsClient::class
        ];
    }

    /**
     *
     * @param Repository $config
     *
     * @return WebSmsClient
     *
     * @throws InvalidConfigException
     */
    public function createClient(Repository $config)
    {
        $client = new WebSmsClient();

        $client->setEndpoint($config->get('endpoint'));

        if ($config->get('auth.accessToken')) {
            $client->setAccessToken($config->get('auth.accessToken'));
        } elseif ($config->get('auth.username') and $config->get('auth.password')) {
            $client->setUsernamePassword(
                $config->get('auth.username'),
                $config->get('auth.password')
            );
        } else {
            throw new InvalidConfigException('Either the access token or the username/password are required');
        }

        return $client;
    }
}
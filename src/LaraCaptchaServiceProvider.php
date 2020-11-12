<?php

namespace LaraCaptcha;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class LaraCaptchaServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        $app->bind('laracaptcha', function () use ($app) {
            return new LaraCaptchaManager(
                $app['config']['lara_captcha.sitekey'],
                $app['config']['lara_captcha.secret'],
                $app['config']['lara_captcha.form_id'],
                $app['config']['lara_captcha.hidden_field_name']
            );
        });

        $this->mergeConfigFrom(__DIR__ . '/../config/lara_captcha.php', 'lara_captcha');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $app = $this->app;

        $this->publishes([
            __DIR__ . '/../config/lara_captcha.php' => config_path('lara_captcha.php')
        ]);

        Blade::directive('laracaptcha', function () use ($app) {
            return $app['laracaptcha']->directive();
        });

        Validator::extend('recaptcha', function ($attribute, $value, $parameters, $validator) use ($app) {
            $action = isset($parameters[0]) ? $parameters[0] : $app['laracaptcha']::DEFAULT_ACTION;
            $scoreThreshold = isset($parameters[1]) ? $parameters[1] : $app['laracaptcha']::DEFAULT_SCORE_THRESHOLD;

            return $app['laracaptcha']->verify($action, $scoreThreshold, $value, $app['request']->ip());
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['laracaptcha'];
    }
}

<?php

namespace Mashbox\Sage\AffiliateWP;

use Roots\Acorn\ServiceProvider;

class AffiliateWPServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('affiliatewp', AffiliateWP::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (defined('AFFILIATEWP_PLUGIN_URL')) {
            $this->bindFilters();
        }

        $this->publishes([
            __DIR__ . '/../publishes/resources/views' => $this->app->resourcePath('views/affiliatewp'),
        ], 'affiliatewp-template-views');
    }

    public function bindFilters()
    {
        $affiliatewp = $this->app['affiliatewp'];

        add_filter('locate_template', [$affiliatewp, 'template'], 10, 2);
        add_filter('affwp_get_template_part', [$affiliatewp, 'template']);
        add_filter('affwp_get_template', [$affiliatewp, 'template'], 1000);
    }
}
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
        if (class_exists( 'Affiliate_WP' )) {
            $this->bindFilters();
        }

        $this->publishes([
            __DIR__ . '/../publishes/resources/views' => $this->app->resourcePath('views/affiliatewp'),
        ], 'affiliatewp-template-views');
    }

    public function bindFilters()
    {        
        $affiliatewp = $this->app['affiliatewp'];

        add_filter('affwp_template_paths', [$affiliatewp, 'template_paths'], 10, 1);
        add_filter('affwp_get_template_part', [$affiliatewp, 'template'], 10, 2);
    }
}
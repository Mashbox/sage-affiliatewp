<?php

namespace Mashbox\Sage\AffiliateWP;

use Illuminate\Contracts\Container\Container as ContainerContract;
use Roots\Acorn\Sage\ViewFinder;
use Roots\Acorn\View\FileViewFinder;
use Illuminate\Support\Str;

use function Roots\view;

class AffiliateWP
{
    protected ViewFinder $sageFinder;
    protected FileViewFinder $fileFinder;
    protected ContainerContract $app;

    public function __construct(
        ViewFinder $sageFinder,
        FileViewFinder $fileFinder,
        ContainerContract $app
    ) {
        $this->app = $app;
        $this->fileFinder = $fileFinder;
        $this->sageFinder = $sageFinder;
    }

    /**
     * Add the Sage storage to the list of paths.
     */
    public function template_paths(array $template_paths): array
    {
        // Add the compiled directory to the path
        $template_paths[] = config('view.compiled');

        return $template_paths;
    }

    /**
     * Filter a template path, taking into account theme templates and creating
     * blade loaders as needed.
     */
    public function template(array $templates, string $slug, ?string $name): string
    {
        if ( isset( $name ) ) {
			$slug .= '-' . $name;
		}

        // Locate any matching template within the theme.
        $themeTemplate = $this->locateThemeTemplate($slug);

        if (!$themeTemplate) {
            return $templates[0];
        }

        // Return filename for status screen
        if (
            is_admin() &&
            !wp_doing_ajax()
        ) {
            return $themeTemplate;
        }

        // Include directly unless it's a blade file.
        if (!Str::endsWith($themeTemplate, '.blade.php')) {
            return $themeTemplate;
        }

        // We have a template, create a loader file and return it's path.
        return basename( view(
            $this->fileFinder->getPossibleViewNameFromPath(realpath($themeTemplate))
        )->makeLoader() );
    }

    /**
     * Check if template is an AffiliateWP template.
     */
    protected function isAffiliateWPTemplate(string $template): bool
    {
        return $this->relativeTemplatePath($template) !== $template;
    }

    /**
     * Return the theme relative template path.
     */
    protected function relativeTemplatePath(string $template): string
    {
        $defaultPaths = [
            AFFILIATEWP_PLUGIN_DIR . 'templates',
        ];

        if (is_child_theme()) {
            // Parent theme templates in woocommerce/ subfolder.
            $defaultPaths[] = get_template_directory() . '/templates';
        }

        return str_replace(
            apply_filters('sage-affiliatewp/templates', $defaultPaths),
            '',
            $template
        );
    }

    /**
     * Locate the theme's AffiliateWP blade template when available.
     */
    protected function locateThemeTemplate(string $template): string
    {
        // Absolute plugin template path -> woocommerce/single-product.php
        $themeTemplate = 'affiliatewp/' . $this->relativeTemplatePath($template);

        // Return absolute theme template path.
        return locate_template($this->sageFinder->locate($themeTemplate));
    }
}
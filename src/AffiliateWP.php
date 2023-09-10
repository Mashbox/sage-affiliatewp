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
     * Support blade templates for the main template include.
     */
    public function templateInclude(string $template): string
    {
        if (!$this->isAffiliateWPTemplate($template)) {
            return $template;
        }
        return $this->locateThemeTemplate($template) ?: $template;
    }

    /**
     * Filter a template path, taking into account theme templates and creating
     * blade loaders as needed.
     */
    public function template(string $templates, string $slug, string $nameame = ''): string
    {
        // Locate any matching template within the theme.
        $themeTemplate = $this->locateThemeTemplate($templateName ?: $template);
        if (!$themeTemplate) {
            return $template;
        }

        // Include directly unless it's a blade file.
        if (!Str::endsWith($themeTemplate, '.blade.php')) {
            return $themeTemplate;
        }

        // We have a template, create a loader file and return it's path.
        return view(
            $this->fileFinder->getPossibleViewNameFromPath(realpath($themeTemplate))
        )->makeLoader();
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
            // WooCommerce plugin templates
            \WC_ABSPATH . 'templates/',
        ];

        if (is_child_theme()) {
            // Parent theme templates in woocommerce/ subfolder.
            $defaultPaths[] = get_template_directory() . '/' . WC()->template_path();
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
        $themeTemplate = AFFILIATEWP_PLUGIN_DIR . 'templates' . $this->relativeTemplatePath($template);
        // Return absolute theme template path.
        return locate_template($this->sageFinder->locate($themeTemplate));
    }
}
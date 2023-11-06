<?php
/**
 * Rank Math SEO Capabilities class.
 *
 * Generated by Capabilities Extractor
 */
class publishpress_capabilities_rank_math_seo
{
    private static $instance = null;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new publishpress_capabilities_rank_math_seo();
        }

        return self::$instance;
    }

    public function __construct()
    {
        //Rank Math SEO Capabilities
        add_filter('cme_plugin_capabilities', [$this, 'cme_rank_math_seo_capabilities']);
    }

    /**
     * Rank Math SEO Capabilities
     *
     * @param array $plugin_caps
     * 
     * @return array
     */
    public function cme_rank_math_seo_capabilities($plugin_caps)
    {

        if (defined('RANK_MATH_VERSION')) {
            $plugin_caps['Rank Math SEO'] = apply_filters(
                'cme_rank_math_seo_capabilities',
                [
                    'rank_math_404_monitor',
                    'rank_math_admin_bar',
                    'rank_math_analytics',
                    'rank_math_content_ai',
                    'rank_math_edit_htaccess',
                    'rank_math_general',
                    'rank_math_link_builder',
                    'rank_math_onpage_advanced',
                    'rank_math_onpage_analysis',
                    'rank_math_onpage_general',
                    'rank_math_onpage_snippet',
                    'rank_math_onpage_social',
                    'rank_math_redirections',
                    'rank_math_role_manager',
                    'rank_math_site_analysis',
                    'rank_math_sitemap',
                    'rank_math_titles'
                ]
            );
        }

        return $plugin_caps;
    }
}
publishpress_capabilities_rank_math_seo::instance();
?>

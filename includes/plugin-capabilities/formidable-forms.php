<?php
/**
 * Formidable Forms Capabilities class.
 *
 * Generated by Capabilities Extractor
 */
class publishpress_capabilities_formidable_forms
{
    private static $instance = null;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new publishpress_capabilities_formidable_forms();
        }

        return self::$instance;
    }

    public function __construct()
    {
        //Formidable Forms Capabilities
        add_filter('cme_plugin_capabilities', [$this, 'cme_formidable_forms_capabilities']);
    }

    /**
     * Formidable Forms Capabilities
     *
     * @param array $plugin_caps
     * 
     * @return array
     */
    public function cme_formidable_forms_capabilities($plugin_caps)
    {

        if (defined('REPLACE_WITH_PLUGIN_VERSION_CONSTANT')) {
            $plugin_caps['Formidable Forms'] = apply_filters(
                'cme_formidable_forms_capabilities',
                [
                    'frm_change_settings',
                    'frm_delete_entries',
                    'frm_delete_forms',
                    'frm_edit_forms',
                    'frm_view_entries',
                    'frm_view_forms'
                ]
            );
        }

        return $plugin_caps;
    }
}
publishpress_capabilities_formidable_forms::instance();
?>

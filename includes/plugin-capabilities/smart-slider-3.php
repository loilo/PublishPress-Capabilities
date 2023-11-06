/**
 * Smart Slider 3 Capabilities class.
 * Generated by Capabilities Extractor
 */
namespace PublishPress\Plugin_Capabilities;

class Smart_Slider_3
{
    private static $instance = null;

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new Smart_Slider_3();
        }

        return self::$instance;
    }

    public function __construct()
    {
        //Smart Slider 3 Capabilities
        add_filter('cme_plugin_capabilities', [$this, 'cme_smart_slider_3_capabilities']);
    }

    /**
     * Smart Slider 3 Capabilities
     *
     * @param array $plugin_caps
     * 
     * @return array
     */
    public function cme_smart_slider_3_capabilities($plugin_caps)
    {

        if (defined('SMARTSLIDER3_LIBRARY_PATH')) {
            $plugin_caps['Smart Slider 3'] = apply_filters(
                'cme_smart_slider_3_capabilities',
                [
                    'smartslider',
                    'smartslider_config',
                    'smartslider_delete',
                    'smartslider_edit'
                ]
            );
        }

        return $plugin_caps;
    }
}

\PublishPress\Plugin_Capabilities\Smart_Slider_3::instance();

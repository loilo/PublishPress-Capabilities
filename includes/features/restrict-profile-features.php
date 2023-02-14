<?php
namespace PublishPress\Capabilities;

class PP_Capabilities_Profile_Features
{

    /**
     * Class hooks and filters
     *
     * @return void
     */
    public static function instance() {
        //ajax handler for updating profile features elements
        add_action('wp_ajax_ppc_update_profile_features_element_by_ajax', [__CLASS__, 'profileElementUpdateAjaxHandler']);
        //request status response code handler
        add_action('admin_notices', [__CLASS__, 'handleRequestResponseCode']);
        //add removeable args
        add_filter('removable_query_args', [__CLASS__, 'addRemovableArgs']);
        //implement profile features restriction
        add_action('admin_head', [__CLASS__, 'applyProfileRestriction'], 1);
    }

    /**
     * Ajax handler for updating profile features elements
     *
     * @since 2.7.0
     */
    public static function profileElementUpdateAjaxHandler()
    {
        $response['status']  = 'error';
        $response['message'] = __('An error occured!', 'capabilities-pro');
        $response['content'] = '';
        $redirect_url = admin_url('admin.php?page=pp-capabilities-profile-features');

        $security       = isset($_POST['security']) ? sanitize_key($_POST['security']) : false;
        $page_elements  = isset($_POST['page_elements']) ? $_POST['page_elements'] : [];// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $cap_name = (is_multisite() && is_super_admin()) ? 'read' : 'manage_capabilities';

        if (!$security || !wp_verify_nonce($security, 'ppc-profile-edit-action')) {
            $response['redirect'] = $redirect_url . '&profile_features_status=0&&profile_features_message=0';
        } elseif (!current_user_can($cap_name)) {
            $response['redirect'] = $redirect_url . '&profile_features_status=0&&profile_features_message=1';
        } else {
            $response['status']  = 'success';
            $profile_features_elements = self::elementsLayout();
            $profile_feature_role = get_option("capsman_profile_features_elements_testing_role", 'subscriber');
            $profile_element_updated = (array) get_option("capsman_profile_features_updated", []);
            $profile_element_updated[$profile_feature_role] = 1;
            $role_profile_features_elements = [];
            foreach ($page_elements as $key => $data) {
                $new_element_key  = sanitize_key($key);
                $new_element_data = [
                    'label'        => sanitize_text_field($data['label']),
                    'elements'     => sanitize_text_field($data['elements']),
                    'element_type' => sanitize_key($data['element_type'])
                ];
                $role_profile_features_elements[$new_element_key] = $new_element_data;
            }
            $profile_features_elements[$profile_feature_role] = $role_profile_features_elements;
            update_option('capsman_profile_features_elements', $profile_features_elements, false);
            update_option('capsman_profile_features_updated', $profile_element_updated, false);
            delete_option('capsman_profile_features_elements_testing_role');
            if (isset($_COOKIE['ppc_test_user_tester_'.COOKIEHASH]) && !empty($_COOKIE['ppc_test_user_tester_'.COOKIEHASH])) {
                $user = wp_get_current_user();
                $redirect_url = add_query_arg(
                    [
                        'ppc_test_user'   => base64_encode(get_current_user_id()),
                        'profile_feature_action' => 1,
                        'ppc_return_back' => 1,
                        '_wpnonce'        => wp_create_nonce('ppc-test-user')
                    ], 
                    home_url()
                );
                $response['redirect'] = $redirect_url;
            } else {
                $response['redirect'] = $redirect_url . '&profile_features_status=1&profile_features_message=0';
            }
        }
        wp_send_json($response);
    }

    /**
     * Get all response code messages
     *
     * @return void
     */
    public static function getResponseMessages() {
        $response_messages = [
            0 => [
                0 => esc_html__('You do not have permission to manage profile features.', 'capsman-enhanced'),
                1 => esc_html__('Security verification failed.', 'capsman-enhanced')
            ],
            1 => [
                0 => esc_html__('Profile features element updated successfully.', 'capsman-enhanced')
            ]
        ];

        return $response_messages;
    }

    /**
     * Request status response code handler
     *
     * @return void
     */
    public static function handleRequestResponseCode() {
        if (!isset($_REQUEST['page']) 
            || (isset($_REQUEST['page']) && $_REQUEST['page'] !== 'pp-capabilities-profile-features')
            || !isset($_REQUEST['profile_features_status'])
            || !isset($_REQUEST['profile_features_message'])
        ) {
            return;
        }

        $status_code       = intval($_REQUEST['profile_features_status']);
        $status_message    = intval($_REQUEST['profile_features_message']);
        $response_messages = self::getResponseMessages();
        if ($status_code === 0 && isset($response_messages[$status_code][$status_message])) {
            ak_admin_error($response_messages[$status_code][$status_message]);
        } elseif ($status_code === 1 && isset($response_messages[$status_code][$status_message])) {
            ak_admin_notify($response_messages[$status_code][$status_message]);
        }

    }

    /**
     * Add removeable args
     *
     * @param array $args
     * @return array
     */
    public static function addRemovableArgs($args) {

        $args = (array) $args;

        return array_merge(
            $args,
            [
                'profile_features_status',
                'profile_features_message',
            ]
        );
    }

    /**
     * Get all admin features layout.
     *
     * @return array Elements layout.
     */
    public static function elementsLayout()
    {
        $elements = !empty(get_option('capsman_profile_features_elements')) ? (array)get_option('capsman_profile_features_elements') : [];
        $elements = array_filter($elements);

        return apply_filters('pp_capabilities_profile_features_elements', $elements);
    }

    /**
     * Implement profile features restriction
     *
     * @return void
     */
    public static function applyProfileRestriction() {

        if (!function_exists('get_current_screen')) {
            return;
        }

        $screen = get_current_screen();

        if (is_object($screen) && isset($screen->base) && in_array($screen->base, ['user-edit', 'profile'])) {
            if (!is_array(get_option("capsman_disabled_profile_features", []))) {
                return;
            }

            $restrict_elements = [];

            // Only restrictions associated with this user's role(s) will be applied
            $role_restrictions = array_intersect_key(
                get_option("capsman_disabled_profile_features", []), 
                array_fill_keys(wp_get_current_user()->roles, true)
            );

            foreach ($role_restrictions as $features) {
                if (is_array($features)) {
                    $restrict_elements = array_merge($restrict_elements, $features);
                }
            }

            // apply the stored restrictions by css
            if ($restrict_elements = array_unique($restrict_elements)) {
                $original_restrict_styles =  implode(',', array_map('esc_attr', $restrict_elements)) . ' {display:none !important;}';;
                /**
                 * Headers are showing for secs before been hidden due
                 * to the fact we're just adding class to them.
                 * 
                 * So, we should hide them by default and then re update
                 * the inline styles value
                 */
                $restrict_elements[] = '#profile-page form h1, #profile-page form h2, #profile-page form h3, #profile-page form h4, #profile-page form h5, #profile-page form h6, #profile-page form tr';
                $inline_styles = implode(',', array_map('esc_attr', $restrict_elements)) . ' {display:none !important;}';
                //add inline styles
                ppc_add_inline_style($inline_styles, 'ppc-profile-dummy-css-handle');
                //add inline script to update inline css
                $inline_script = "
                jQuery(document).ready( function($) {
                    $('#ppc-profile-dummy-css-handle-inline-css').html('{$original_restrict_styles}');
                });";
                ppc_add_inline_script($inline_script, 'ppc-profile-dummy-css-handle');
            }
        }

    }

}

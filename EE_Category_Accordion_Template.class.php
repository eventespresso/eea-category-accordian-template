<?php

/**
 * Class  EE_Category_Accordion_Template
 *
 * @package             Event Espresso
 * @subpackage          espresso-new-addon
 * @author              Brent Christensen
 */
class EE_Category_Accordion_Template extends EE_Addon
{
    /**
     * @return void
     * @throws EE_Error
     */
    public static function register_addon()
    {
        if (! defined('EE_CATEGORY_ACCORDION_TEMPLATE_PATH')) {
            // define the plugin directory path and URL
            define('EE_CATEGORY_ACCORDION_TEMPLATE_PATH', plugin_dir_path(__FILE__));
            define('EE_CATEGORY_ACCORDION_TEMPLATE_URL', plugin_dir_url(__FILE__));
            define('EE_CATEGORY_ACCORDION_TEMPLATE_TEMPLATES', EE_CATEGORY_ACCORDION_TEMPLATE_PATH . DS . 'templates');
        }

        // register addon via Plugin API
        EE_Register_Addon::register(
            'Category_Accordion_Template',
            [
                'version' => EE_CATEGORY_ACCORDION_TEMPLATE_VERSION,
                'min_core_version' => '4.3.0',
                'base_path' => EE_CATEGORY_ACCORDION_TEMPLATE_PATH,
                'main_file_path' => EE_CATEGORY_ACCORDION_TEMPLATE_PATH . 'espresso-calendar-table-template.php',
                'autoloader_paths' => [
                    'EE_Category_Accordion_Template' => EE_CATEGORY_ACCORDION_TEMPLATE_PATH . 'EE_Category_Accordion_Template.class.php',
                ],
                'shortcode_paths' => [EE_CATEGORY_ACCORDION_TEMPLATE_PATH . 'EES_Espresso_Category_Accordion_Template.shortcode.php'],
                // The below is for if plugin update engine is being used for auto-updates. not needed if PUE is not being used.
                'pue_options' => [
                    'pue_plugin_slug' => 'eea-category-accordian-template',
                    'plugin_basename' => EE_CATEGORY_ACCORDION_TEMPLATE_PLUGIN_FILE,
                    'checkPeriod'     => '24',
                    'use_wp_update'   => false,
                ],
            ]
        );
    }


    /**
     * @return     void
     */
    public function additional_admin_hooks()
    {
        // is admin and not in M-Mode ?
        if (
            is_admin()
            && (
                class_exists('EventEspresso\core\domain\services\database\MaintenanceStatus')
                && EventEspresso\core\domain\services\database\MaintenanceStatus::isDisabled()
            ) || ! EE_Maintenance_Mode::instance()->level()
        ) {
            add_filter('plugin_action_links', [$this, 'plugin_actions'], 10, 2);
        }
    }


    /**
     * plugin_actions
     *
     * Add a settings link to the Plugins page, so people can go straight from the plugin page to the settings page.
     *
     * @param $links
     * @param $file
     * @return array
     */
    public function plugin_actions($links, $file)
    {
        if ($file == EE_CATEGORY_ACCORDION_TEMPLATE_PLUGIN_FILE) {
            // before other links
            array_unshift(
                $links,
                '<a href="admin.php?page=espresso_category_accordion_template">' . __('Settings') . '</a>'
            );
        }
        return $links;
    }
}
// End of file EE_Category_Accordion_Template.class.php
// Location: wp-content/plugins/espresso-new-addon/EE_Category_Accordion_Template.class.php

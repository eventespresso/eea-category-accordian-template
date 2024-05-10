<?php

/*
  Plugin Name: Event Espresso - Category Accordion Template (EE4.3+)
  Plugin URI: http://www.eventespresso.com
  Description: Will display the categories in bars, once clicked events associated with that category will appear in an "accordion" style. If category colours are turned on, the block to the left will be that colour, otherwise it will default to grey.
  Requirements: (optional) CSS skills to customize styles, some renaming of the table columns
  Shortcode Example: [ESPRESSO_CATEGORY_ACCORDION_TEMPLATE]
  Shortcode Parameters: show_featured=1 (shows the featured image), table_header=0 (hides the TH row)
  Version: 0.0.1.rc.008
  Author: Event Espresso
  Author URI: http://www.eventespresso.com
  Copyright 2014 Event Espresso (email : support@eventespresso.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA02110-1301USA
 *
 * ------------------------------------------------------------------------
 *
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package		Event Espresso
 * @ author			Event Espresso
 * @ copyright	(c) 2008-2014 Event Espresso  All Rights Reserved.
 * @ license		http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link				http://www.eventespresso.com
 * @ version	 	EE4
 *
 * ------------------------------------------------------------------------
 */
// category_accordion_template version
define('EE_CATEGORY_ACCORDION_TEMPLATE_VERSION', '0.0.1.rc.008');
define('EE_CATEGORY_ACCORDION_TEMPLATE_PLUGIN_FILE', plugin_basename(__FILE__));

function load_espresso_category_accordion_template()
{
    if (class_exists('EE_Addon')) {
        require_once(plugin_dir_path(__FILE__) . 'EE_Category_Accordion_Template.class.php');
        EE_Category_Accordion_Template::register_addon();
    }
}
add_action('AHEE__EE_System__load_espresso_addons', 'load_espresso_category_accordion_template');

// End of file espresso_category_accordion_template.php
// Location: wp-content/plugins/espresso-new-addon/espresso_category_accordion_template.php

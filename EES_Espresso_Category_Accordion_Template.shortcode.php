<?php

/*
 * EES_Espresso_Category_Accordion_Template
 *
 * @package			Event Espresso
 * @subpackage		espresso-new-addon
 * @author 			Brent Christensen
 */

class EES_Espresso_Category_Accordion_Template extends EES_Shortcode
{
    /**
     *  set_hooks - for hooking into EE Core, modules, etc
     *
     * @return     void
     */
    public static function set_hooks()
    {
    }


    /**
     *  set_hooks_admin - for hooking into EE Admin Core, modules, etc
     *
     * @return     void
     */
    public static function set_hooks_admin()
    {
    }


    /**
     *  set_definitions
     *
     * @return     void
     */
    public static function set_definitions()
    {
    }


    /**
     *  run - initial shortcode module setup called during "wp_loaded" hook
     *  this method is primarily used for loading resources that will be required by the shortcode when it is actually
     *  processed
     *
     * @param WP $WP
     * @return void
     * @throws EE_Error
     * @throws ReflectionException
     */
    public function run(WP $WP)
    {
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        // You might want this, but delete if you don't need the template tags
        EE_Registry::instance()->load_helper('Event_View');
        EE_Registry::instance()->load_helper('Venue_View');
    }


    /**
     *  enqueue_scripts - Load the scripts and css
     *
     * @return     void
     */
    public function enqueue_scripts()
    {
        // Check to see if the category_accordion_template css file exists in the '/uploads/espresso/' directory
        if (is_readable(EVENT_ESPRESSO_UPLOAD_DIR . 'css' . DS . 'espresso_category_accordion_template.css')) {
            // This is the url to the css file if available
            wp_register_style(
                'espresso_category_accordion_template',
                EVENT_ESPRESSO_UPLOAD_URL . 'css' . DS . 'espresso_category_accordion_template.css'
            );
        } else {
            // EE category_accordion_template style
            wp_register_style(
                'espresso_category_accordion_template',
                EE_CATEGORY_ACCORDION_TEMPLATE_URL . 'css' . DS . 'espresso_category_accordion_template.css'
            );
        }
        // category_accordion_template script
        wp_register_script(
            'espresso_category_accordion_template',
            EE_CATEGORY_ACCORDION_TEMPLATE_URL . 'scripts' . DS . 'espresso_category_accordion_template.js',
            ['jquery'],
            EE_CATEGORY_ACCORDION_TEMPLATE_VERSION,
            true
        );
        // enqueue
        wp_enqueue_style('espresso_category_accordion_template');
        wp_enqueue_script('espresso_category_accordion_template');
    }


    /**
     * [ESPRESSO_CATEGORY_ACCORDION_TEMPLATE]
     *
     * @param array|string $attributes
     * @return string
     */
    public function process_shortcode($attributes = []): string
    {
        // make sure $attributes is an array
        $attributes = array_merge(
        // defaults
            [
                'title'         => null,
                'limit'         => 10,
                'css_class'     => null,
                'show_expired'  => false,
                'month'         => null,
                'category_slug' => null,
                'order_by'      => 'start_date',
                'sort'          => 'ASC',
                'show_featured' => '0',
                'table_header'  => '1',
            ],
            (array) $attributes
        );
        // run the query
        global $wp_query;
        $wp_query = new EE_Category_Accordion_Template_Query($attributes);
//      d( $wp_query );
        // now filter the array of locations to search for templates
        add_filter('FHEE__EEH_Template__locate_template__template_folder_paths', [$this, 'template_folder_paths']);
        // load our template
        $category_accordion_template = EEH_Template::locate_template(
            'espresso-calendar-table-template.template.php',
            $attributes
        );
        // now reset the query and postdata
        wp_reset_query();
        wp_reset_postdata();
        return $category_accordion_template;
    }


    /**
     *    template_folder_paths
     *
     * @param array $template_folder_paths
     * @return    array
     */
    public function template_folder_paths(array $template_folder_paths = []): array
    {
        $template_folder_paths[] = EE_CATEGORY_ACCORDION_TEMPLATE_TEMPLATES;
        return $template_folder_paths;
    }
}

/**
 *
 * Class EE_Category_Accordion_Template_Query
 *
 * Description
 *
 * @package             Event Espresso
 * @subpackage          core
 * @author              Brent Christensen
 * @since               4.4
 *
 */
class EE_Category_Accordion_Template_Query extends WP_Query
{
    private int $_limit = 10;

    private bool $_show_expired = false;

    private ?string $_month = '';

    private ?string $_category_slug = '';

    /**
     * @var array|string|null
     */
    private $_order_by = [];

    private ?string $_sort = '';


    /**
     * @param array $args
     */
    function __construct($args = [])
    {
        // incoming args could be a mix of WP query args + EE shortcode args
        foreach ($args as $key => $value) {
            $property = '_' . $key;
            // if the arg is a property of this class, then it's an EE shortcode arg
            if (property_exists($this, $property)) {
                // set the property value
                $this->$property = $value;
                // then remove it from the array of args that will later be passed to WP_Query()
                unset($args[ $key ]);
            }
        }
        // parse orderby attribute
        if ($this->_order_by !== null) {
            $this->_order_by = explode(',', $this->_order_by);
            $this->_order_by = array_map('trim', $this->_order_by);
        }
        $this->_sort = in_array($this->_sort, ['ASC', 'asc', 'DESC', 'desc']) ? strtoupper($this->_sort) : 'ASC';
        // add query filters
        EEH_Event_Query::add_query_filters();
        // set params that will get used by the filters
        EEH_Event_Query::set_query_params(
            $this->_month,
            $this->_category_slug,
            $this->_show_expired,
            $this->_order_by,
            $this->_sort
        );
        // the current "page" we are viewing
        $paged = max(1, get_query_var('paged'));
        // Force these args
        $args = array_merge(
            $args,
            [
                'post_type'              => 'espresso_events',
                'posts_per_page'         => $this->_limit,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
                'paged'                  => $paged,
                'offset'                 => ($paged - 1) * $this->_limit,
            ]
        );

        // run the query
        parent::__construct($args);
    }


    /**
     * @param string $SQL
     * @return string
     * @throws EE_Error
     * @throws ReflectionException
     * @deprecated $VID:$
     */
    public function posts_join(string $SQL): string
    {
        EE_Error::doing_it_wrong(
            __METHOD__,
            esc_html__(
                'EE_Category_Accordion_Template_Query::posts_join() is deprecated! Use EEH_Event_Query::posts_join_*() instead.',
                'event_espresso'
            ),
            '$VID:$'
        );

        // first off, let's remove any filters from previous queries
        remove_filter('posts_join', [$this, 'posts_join']);
        // generate the SQL
        if ($this->_category_slug !== null) {
            $SQL .= EEH_Event_Query::posts_join_sql_for_terms($SQL, true);
        }
        if ($this->_order_by !== null) {
            $SQL .= EEH_Event_Query::posts_join_for_orderby($SQL, $this->_order_by);
        }
        return $SQL;
    }


    /**
     * @param string $SQL
     * @return string
     * @throws EE_Error
     * @throws ReflectionException
     * @deprecated $VID:$
     */
    public function posts_where(string $SQL): string
    {
        EE_Error::doing_it_wrong(
            __METHOD__,
            esc_html__(
                'EE_Category_Accordion_Template_Query::posts_where() is deprecated! Use EEH_Event_Query::posts_where_*() instead.',
                'event_espresso'
            ),
            '$VID:$'
        );
        // first off, let's remove any filters from previous queries
        remove_filter('posts_where', [$this, 'posts_where']);
        // Show Expired ?
        $SQL .= EEH_Event_Query::posts_where_sql_for_show_expired($this->_show_expired);
        // Category
        $SQL .= EEH_Event_Query::posts_where_sql_for_event_category_slug($this->_category_slug);
        // Start Date
        $SQL .= EEH_Event_Query::posts_where_sql_for_event_list_month($this->_month);
        return $SQL;
    }


    /**
     * @param string $SQL
     * @return string
     * @throws EE_Error
     * @throws ReflectionException
     * @deprecated $VID:$
     */
    public function posts_orderby(string $SQL): string
    {
        EE_Error::doing_it_wrong(
            __METHOD__,
            esc_html__(
                'EE_Category_Accordion_Template_Query::posts_orderby() is deprecated! Use EEH_Event_Query::posts_orderby_sql() instead.',
                'event_espresso'
            ),
            '$VID:$'
        );
        // first off, let's remove any filters from previous queries
        remove_filter('posts_orderby', [$this, 'posts_orderby']);
        // generate the SQL
        return EEH_Event_Query::posts_orderby_sql($this->_order_by, $this->_sort);
    }
}

// End of file EES_Espresso_Category_Accordion_Template.shortcode.php
// Location: /wp-content/plugins/espresso-new-addon/EES_Espresso_Category_Accordion_Template.shortcode.php

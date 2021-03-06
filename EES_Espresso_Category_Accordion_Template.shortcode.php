<?php if ( ! defined( 'EVENT_ESPRESSO_VERSION' )) { exit(); }
/*
 * ------------------------------------------------------------------------
 *
 * Event Espresso
 *
 * Event Registration and Management Plugin for WordPress
 *
 * @ package			Event Espresso
 * @ author				Seth Shoultes
 * @ copyright		(c) 2008-2014 Event Espresso  All Rights Reserved.
 * @ license			http://eventespresso.com/support/terms-conditions/   * see Plugin Licensing *
 * @ link					http://www.eventespresso.com
 * @ version		 	EE4
 *
 * ------------------------------------------------------------------------
 *
 * EES_Espresso_Category_Accordion_Template
 *
 * @package			Event Espresso
 * @subpackage		espresso-new-addon
 * @author 				Brent Christensen
 * 
 *
 * ------------------------------------------------------------------------
 */
class EES_Espresso_Category_Accordion_Template  extends EES_Shortcode {



	/**
	 * 	set_hooks - for hooking into EE Core, modules, etc
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_hooks() {
	}



	/**
	 * 	set_hooks_admin - for hooking into EE Admin Core, modules, etc
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_hooks_admin() {
	}



	/**
	 * 	set_definitions
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public static function set_definitions() {
	}



	/**
	 * 	run - initial shortcode module setup called during "wp_loaded" hook
	 * 	this method is primarily used for loading resources that will be required by the shortcode when it is actually processed
	 *
	 *  @access 	public
	 *  @param 	 WP $WP
	 *  @return 	void
	 */
	public function run( WP $WP ) {
		add_action('wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );
		// You might want this, but delete if you don't need the template tags
		EE_Registry::instance()->load_helper( 'Event_View' );
		EE_Registry::instance()->load_helper( 'Venue_View' );
	}



	/**
	 * 	enqueue_scripts - Load the scripts and css
	 *
	 *  @access 	public
	 *  @return 	void
	 */
	public function enqueue_scripts() {
		//Check to see if the category_accordion_template css file exists in the '/uploads/espresso/' directory
		if ( is_readable( EVENT_ESPRESSO_UPLOAD_DIR . 'css' . DS . 'espresso_category_accordion_template.css' )) {
			//This is the url to the css file if available
			wp_register_style( 'espresso_category_accordion_template', EVENT_ESPRESSO_UPLOAD_URL . 'css' . DS . 'espresso_category_accordion_template.css' );
		} else {
			// EE category_accordion_template style
			wp_register_style( 'espresso_category_accordion_template', EE_CATEGORY_ACCORDION_TEMPLATE_URL . 'css' . DS . 'espresso_category_accordion_template.css' );
		}
		// category_accordion_template script
		wp_register_script( 'espresso_category_accordion_template', EE_CATEGORY_ACCORDION_TEMPLATE_URL . 'scripts' . DS . 'espresso_category_accordion_template.js', array( 'jquery' ), EE_CATEGORY_ACCORDION_TEMPLATE_VERSION, TRUE );
		// enqueue
		wp_enqueue_style( 'espresso_category_accordion_template' );
		wp_enqueue_script( 'espresso_category_accordion_template' );
	}



	/**
	 *    process_shortcode
	 *
	 *    [ESPRESSO_CATEGORY_ACCORDION_TEMPLATE]
	 *
	 * @access 	public
	 * @param 	array $attributes
	 * @return 	string
	 */
	public function process_shortcode( $attributes = array() ) {
		// make sure $attributes is an array
		$attributes = array_merge(
			// defaults
			array(
				'title' => NULL,
				'limit' => 10,
				'css_class' => NULL,
				'show_expired' => FALSE,
				'month' => NULL,
				'category_slug' => NULL,
				'order_by' => 'start_date',
				'sort' => 'ASC',
				'show_featured' => '0',
				'table_header' => '1'
			),
			(array)$attributes
		);
		// run the query
		global $wp_query;
		$wp_query = new EE_Category_Accordion_Template_Query( $attributes );
//		d( $wp_query );
		// now filter the array of locations to search for templates
		add_filter( 'FHEE__EEH_Template__locate_template__template_folder_paths', array( $this, 'template_folder_paths' ));
		// load our template
		$category_accordion_template = EEH_Template::locate_template( 'espresso-calendar-table-template.template.php', $attributes );
		// now reset the query and postdata
		wp_reset_query();
		wp_reset_postdata();
		return $category_accordion_template;
	}



	/**
	 *    template_folder_paths
	 *
	 * @access    public
	 * @param array $template_folder_paths
	 * @return    array
	 */
	public function template_folder_paths( $template_folder_paths = array() ) {
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
 * @package 			Event Espresso
 * @subpackage 	core
 * @author 				Brent Christensen
 * @since 				4.4
 *
 */
class EE_Category_Accordion_Template_Query extends WP_Query {

	private $_limit = 10;
	private $_show_expired = FALSE;
	private $_month = NULL;
	private $_category_slug = NULL;
	private $_order_by = NULL;
	private $_sort = NULL;


	/**
	 * @param array $args
	 */
	function __construct( $args = array() ) {
		// incoming args could be a mix of WP query args + EE shortcode args
		foreach ( $args as $key =>$value ) {
			$property = '_' . $key;
			// if the arg is a property of this class, then it's an EE shortcode arg
			if ( EEH_Class_Tools::has_property( $this, $property )) {
				// set the property value
				$this->$property = $value;
				// then remove it from the array of args that will later be passed to WP_Query()
				unset( $args[ $key ] );
			}
		}
		// parse orderby attribute
		if ( $this->_order_by !== NULL ) {
			$this->_order_by = explode( ',', $this->_order_by );
			$this->_order_by = array_map('trim', $this->_order_by);
		}
		$this->_sort = in_array( $this->_sort, array( 'ASC', 'asc', 'DESC', 'desc' )) ? strtoupper( $this->_sort ) : 'ASC';
		// the current "page" we are viewing
		$paged = max( 1, get_query_var( 'paged' ));
		// Force these args
		$args = array_merge( $args, array(
			'post_type' => 'espresso_events',
			'posts_per_page' => $this->_limit,
			'update_post_term_cache' => FALSE,
			'update_post_meta_cache' => FALSE,
			'paged' => $paged,
			'offset' => ( $paged - 1 ) * $this->_limit
		));
		// filter the query parts
		add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 1 );
		add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 1 );
		add_filter( 'posts_orderby', array( $this, 'posts_orderby' ), 10, 1 );

		// run the query
		parent::__construct( $args );
	}



	/**
	 *    posts_join
	 *
	 * @access    public
	 * @param $SQL
	 * @return    string
	 */
	public function posts_join( $SQL ) {
		// first off, let's remove any filters from previous queries
		remove_filter( 'posts_join', array( $this, 'posts_join' ));
		// generate the SQL
		if ( $this->_category_slug !== NULL ) {
			$SQL .= EED_Events_Archive::posts_join_sql_for_terms( TRUE );
		}
		if ( $this->_order_by !== NULL ) {
			$SQL .= EED_Events_Archive::posts_join_for_orderby( $this->_order_by );
		}
		return $SQL;
	}



	/**
	 *    posts_where
	 *
	 * @access    public
	 * @param $SQL
	 * @return    string
	 */
	public function posts_where( $SQL ) {
		// first off, let's remove any filters from previous queries
		remove_filter( 'posts_where', array( $this, 'posts_where' ));
		// Show Expired ?
		$this->_show_expired = $this->_show_expired ? TRUE : FALSE;
		$SQL .= EED_Events_Archive::posts_where_sql_for_show_expired( $this->_show_expired );
		// Category
		$SQL .=  EED_Events_Archive::posts_where_sql_for_event_category_slug( $this->_category_slug );
		// Start Date
		$SQL .= EED_Events_Archive::posts_where_sql_for_event_list_month( $this->_month );
		return $SQL;
	}



	/**
	 *    posts_orderby
	 *
	 * @access    public
	 * @param $SQL
	 * @return    string
	 */
	public function posts_orderby( $SQL ) {
		// first off, let's remove any filters from previous queries
		remove_filter( 'posts_orderby', array( $this, 'posts_orderby' ) );
		// generate the SQL
		$SQL = EED_Events_Archive::posts_orderby_sql( $this->_order_by, $this->_sort );
		return $SQL;
	}







}

// End of file EES_Espresso_Category_Accordion_Template.shortcode.php
// Location: /wp-content/plugins/espresso-new-addon/EES_Espresso_Category_Accordion_Template.shortcode.php
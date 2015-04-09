<?php


//*


//*/

if ( ! class_exists( 'GlottypomoAdmin' ) ):
class GlottypomoAdmin {
	private static $_instance = null;
	private $require_capability = 'manage_options';
	
	/**
	 * Getting a singleton.
	 *
	 * @return object single instance of GlottypomoAdmin
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) )
			self::$_instance = new self();
		return self::$_instance;
	}

	/**
	 * Private constructor
	 */
	private function __construct() {
		add_action( 'init' , array( &$this , 'init' ) );
		add_action( 'admin_menu' , array( &$this , 'add_admin_page' ) );
	}
	
	/**
	 *	Add Admin page to menu
	 *	
	 *	@action admin_memu
	 */
	function add_admin_page() {
		$page_hook = add_management_page( __( 'WP GlottyPoMo (management)' , 'wp-glottypomo' ), __( 'WP GlottyPoMo' , 'wp-glottypomo' ), $this->require_capability, 'glottypomo-management', array( &$this , 'render_management_page' ) );
		add_action( "load-$page_hook" , array( &$this , 'enqueue_admin_page_assets' ) );
	}
	/**
	 *	Add Admin page to menu
	 *	
	 *	@callback add_management_page
	 */
	function render_management_page() {
		?><div class="wrap"><?php
			?><h2><?php _e( 'GlottyPoMo' , 'wp-glottypomo' ); ?></h2><?php
			?><p><?php _e( 'Content for management' , 'wp-glottypomo' ); ?></p><?php
			?><div id="glotty-translations-table"></div><?php
		?></div><?php
	}
	
	/**
	 *	Add Admin page to menu
	 *	
	 *	@action "load-$page_hook" -> $page_hook of management page
	 */
	function enqueue_admin_page_assets() {
		wp_enqueue_style( 'glottypomo-admin-page' , plugins_url( '/css/glottypomo-admin-page.css' , dirname(__FILE__) ) );
		wp_register_script( 'jquery-serializeobject' , plugins_url( 'js/jquery.serializeobject.js' , dirname(__FILE__) ) ,array('jquery'));
		wp_register_script( 'js-base64' , plugins_url( 'js/base64.js' , dirname(__FILE__) ));
		wp_enqueue_script( 'glottypomo-admin-page' , plugins_url( 'js/glottypomo-admin-page.js' , dirname(__FILE__) ) ,array('wp-backbone','js-base64','jquery-serializeobject'));
		wp_localize_script('glottypomo-admin-page' , 'glottypomo' , array(
			'ajax_urls' => $this->ajax_urls,
			'ajax_data' => array(
				'po' => isset($_REQUEST['po']) ? $_REQUEST['po'] : false,
				'locale' => isset($_REQUEST['locale']) ? $_REQUEST['locale'] : false,
			),
		) );
		add_action('admin_footer',array( 'GlottypomoTemplates' , 'js_templates' ));
	}
	
	
	
	/**
	 *	Initing
	 *	Register Ajax Actions
	 */
	function init() {
		
		$action = 'save-entries';
		$args = array(
			'callback_args'		=> array(  'po' => '' , 'locale' => '' , 'entries' => array() ),
			'check_capability'	=> 'manage_options',
			'response_type'		=> 'application/json',
		);
		$this->ajax_urls[$action] = register_ajax_handler( $action , array( &$this , 'ajax_save_entries' ) , $args );

		$action = 'get-po-data';
		$args = array(
			'callback_args'		=> array( 'po' => '' , 'locale' => '' ),
			'check_capability'	=> 'manage_options',
			'response_type'		=> 'application/json',
		);
		$this->ajax_urls[$action] = register_ajax_handler( $action , array( &$this , 'ajax_get_po_data' ) , $args );
		
		$action = 'init-po';
		$args = array(
			'callback_args'		=> array( 'po' => '' , 'locale' => '' ),
			'check_capability'	=> 'manage_options',
			'response_type'		=> 'application/json',
		);
		$this->ajax_urls[$action] = register_ajax_handler( $action , array( &$this , 'ajax_init_po' ) , $args );
		
		add_action( 'wp_ajax_error' , array( &$this , 'ajax_error' ) );
	}
	
	function enqueue_assets( $page_hook ) {
		add_action( "load-$page_hook" , array( &$this , 'enqueue_admin_page_assets' ) );
	}
	
	/**
	 *	Save translation entries to po.
	 *	Create Mo.
	 *	
	 *	@param $args array(
	 *						'po'		// path relative to WP_CONTENT_DIR
	 *						'locale'	// Locale xx_XX
	 *						'entries'	// array(
	 *										$key => array(
	 *													'translations' => array(
	 *																		 string Translation,
	 *																		 [...],
	 *																		)
	 *													)
	 *										)
	 *						)
	 */
	function ajax_save_entries( $args ) {
		/*
		$args['po'] = path relative to wp_content   languages/textdomain-LOCALE
		$args['locale']
		$args['entries']
		*/
		$po = $this->_get_po( $args );
		$changed = (object) array('entries'=>array());
		$has_changes = false;
		foreach ( $args['entries'] as $key => $entry ) {
			
			if ( isset( $po->entries[$key] ) ) {
				foreach (array_keys( $entry['translations'] ) as $i) {
					// remove carriage returns.
					$po->entries[$key]->translations[$i] = $this->prepare_string( $entry['translations'][$i] );
					$has_changes = true;
				}
				$changed->entries[$key] = $po->entries[$key];
			} else {
				var_dump('not present!');
			}
		}
		if ( $has_changes ) {
			$this->_save_po($po,$args);
			$this->_save_mo( $args );
		}
		
		return $changed;
	}
	/**
	 *	Get PO contents
	 *	
	 *	@param $args array(
	 *						'po'		// path without locale and suffix relative to WP_CONTENT_DIR
	 *						'locale'	// Locale xx_XX
	 *						)
	 *	@return (object) array(
	 *		'po' => po file contents,
	 *		'has_pot' => whether it has a pot file
	 *		'has_po' => whether po file exists
	 *		'has_mo' => whether a mo file exists
	 *		'can_make_po' => can create po from pot
	 *		'plural_definitions' => plural definitions
	 *	);
	 */
	function ajax_get_po_data( $args ) {
		/*
		$args['po'] = path relative to wp_content   languages/textdomain-LOCALE
		$args['locale']
		*/
		$po = $this->_get_po( $args );
		if ( $po )
			$plural_header = $po->get_header('Plural-Forms');
		else 
			$plural_header = GlottypomoTools::get_plural_definition( $args['locale'] );
		
		$plural_def = GlottypomoTools::parse_plural_definition($plural_header);
		$has_pot = file_exists( $this->_get_pot_filename( $args ) );
		return (object) array(
			'po' => $po,
			'has_pot' => $has_pot,
			'has_po' => !!$po,
			'has_mo' => file_exists( $this->_get_pomo_filename( $args , 'mo' ) ),
			'can_make_po' => $has_pot,
			'plural_definitions' => $plural_def,
		);
	}
	/**
	 *	Save translation entries to po.
	 *	Create Mo.
	 *	
	 *	@param $args array(
	 *						'po'		// path without locale and suffix relative to WP_CONTENT_DIR
	 *						'locale'	// Locale xx_XX
	 *						)
	 *	@return (object) array(
	 *		'po' => po file contents,
	 *		'has_pot' => whether it has a pot file
	 *		'has_po' => whether po file exists
	 *		'has_mo' => whether a mo file exists
	 *		'can_make_po' => can create po from pot
	 *		'plural_definitions' => plural definitions
	 *	);
	 */
	function ajax_init_po( $args ) {
		/*
		$args['po'] = path relative to wp_content; plugin | theme | core
		$args['locale']
		*/
		
		$pot = $this->_get_pot( $args );
		$pofile = $this->_get_pomo_filename( $args , 'po' );
		$pot->set_header( 'Plural-Forms' , GlottypomoTools::get_plural_definition( $args['locale'] ) );
		$pot->set_header( 'X-Generator' , 'WP GlottyPoMo' );
		$pot->set_header( 'PO-Revision-Date' , date('r') );
		$pot->set_header('Last-Translator' , sprintf( '%s <%s>' , $current_user->display_name, $current_user->user_email ) );
		unset($pot->headers['POT-Creation-Date']);
		$pot->export_to_file($pofile);
		
		return $this->ajax_get_po_data( $args );
	}
	/**
	 *	Ajax Error handler
	 *	
	 *	@action wp_ajax_error
	 *	@param $wp_error WP_Error
	 *	@return void
	 */
	function ajax_error( $wp_error ) {
		
	}
	
	/**
	 *	Get pot file name
	 *	
	 *	@param $args array(
	 *						'po'		// path without locale and suffix relative to WP_CONTENT_DIR
	 *					)
	 *	@return string absolute path to pot file
	 */
	private function _get_pot_filename( $args ) {
		extract( $args );
		$potfile = WP_CONTENT_DIR . "/{$po}.pot";
		return $potfile;
	}
	/**
	 *	Get pot file name
	 *	
	 *	@param $args array(
	 *						'po'		// path without locale and suffix relative to WP_CONTENT_DIR
	 *						'locale'	// the locale. xx or xx_XX
	 *					)
	 *	@param $suffix string 'po' or 'mo'
	 *	@return string absolute path to po or mo file
	 */
	private function _get_pomo_filename( $args , $suffix ) {
		extract( $args );
		$pofile = WP_CONTENT_DIR . "/{$po}-{$locale}.{$suffix}";
		return $pofile;
	}
	
	/**
	 *	Get pot file name
	 *	
	 *	@param $args mixed. return value of _get_pomo_filename or array(
	 *						'po'		// path without locale and suffix relative to WP_CONTENT_DIR
	 *						'locale'	// the locale. xx or xx_XX
	 *					)
	 *	@return object PO
	 */
	private function _get_po( $args ) {
		if ( is_string( $args ) )
			$pofile = $args;
		else
			$pofile = $this->_get_pomo_filename( $args , 'po' );
		if ( file_exists( $pofile ) ) {
			require_once ABSPATH . WPINC . '/pomo/po.php';
			$po = new PO();
			$po->import_from_file( $pofile );
			foreach ( $po->entries as $key => $entry ) {
				$entry->b64_key = base64_encode($key);
				$po->entries[base64_encode($key)] = $entry;
				unset($po->entries[$key]);
			}
			return $po;
		}
		return false;
	}
	/**
	 *	Save data to po
	 *	
	 *	@param $po PO instance
	 *	@param $args array(
	 *						'po'		// path without locale and suffix relative to WP_CONTENT_DIR
	 *						'locale'	// the locale. xx or xx_XX
	 *					)
	 *	@return bool, return value of PO::export_to_file()
	 */
	private function _save_po( $po , $args ) {
		$pofile = $this->_get_pomo_filename( $args , 'po' );
		return $po->export_to_file($pofile);
	}
	
	/**
	 *	Save create mo out of po
	 *	
	 *	@param $args array(
	 *						'po'		// path without locale and suffix relative to WP_CONTENT_DIR
	 *						'locale'	// the locale. xx or xx_XX
	 *					)
	 *	@return bool, return value of MO::export_to_file()
	 */
	private function _save_mo( $args ) {
		require_once ABSPATH . WPINC . '/pomo/po.php';
		$pofile = $this->_get_pomo_filename( $args , 'po' );
		$po = new PO();
		$po->import_from_file( $pofile );

		$mofile = $this->_get_pomo_filename( $args , 'mo' );
		$mo = new MO();
		$mo->entries = $po->entries;
		$mo->headers = $po->headers;
		return $mo->export_to_file( $mofile );
	}
	/**
	 *	Get pot file data
	 *	
	 *	@param $args array(
	 *						'po'		// path without locale and suffix relative to WP_CONTENT_DIR
	 *					)
	 *	@return PO instance
	 */
	private function _get_pot( $args ) {
		$potfile = $this->_get_pot_filename( $args );
		return $this->_get_po( $potfile );
	}

	/**
	 *	Remove CR (\x0D) from string and trim.
	 *
	 *	@action admin_init
	 */
	protected function prepare_string( $str ) {
		$str = str_replace("\x0D",'',$str);
		return trim( $str );
	}
	
	
}

endif;
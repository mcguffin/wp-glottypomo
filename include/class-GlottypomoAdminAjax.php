<?php


//*


//*/

if ( ! class_exists( 'GlottypomoAdminAjax' ) ):
class GlottypomoAdminAjax {
	private static $_instance = null;
	
	private $ajax_urls = array();
	
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
	}
	
	
	
}

endif;
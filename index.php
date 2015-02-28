<?php

/*
Plugin Name: WP GlottyPoMo
Plugin URI: http://wordpress.org/
Description: Enter description here.
Author: Jörn Lund
Version: 1.0.0
Author URI: 
License: GPL3

Text Domain: wp-glottypomo
Domain Path: /languages/
*/

/*  Copyright 2014  Jörn Lund

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*
Plugin was generated by WP Plugin Scaffold
https://github.com/mcguffin/wp-plugin-scaffold
Command line args were: `"WP GlottyPoMo" admin_page:tools admin_page_js admin_page_css git --force`
*/

if ( ! class_exists( 'Glottypomo' ) ):
class Glottypomo {
	private static $_instance = null;

	/**
	 * Getting a singleton.
	 *
	 * @return object single instance of Glottypomo
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
		add_action( 'plugins_loaded' , array( &$this , 'load_textdomain' ) );
		add_action( 'init' , array( &$this , 'init' ) );
		register_activation_hook( __FILE__ , array( __CLASS__ , 'activate' ) );
		register_deactivation_hook( __FILE__ , array( __CLASS__ , 'deactivate' ) );
		register_uninstall_hook( __FILE__ , array( __CLASS__ , 'uninstall' ) );
	}

	/**
	 * Load text domain
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'wp-glottypomo' , false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
	/**
	 * Init hook.
	 * 
	 *  - Register assets
	 */
	function init() {
	}


	/**
	 *	Fired on plugin activation
	 */
	public static function activate() {
	
	
	}

	/**
	 *	Fired on plugin deactivation
	 */
	public static function deactivate() {
	}

	/**
	 *
	 */
	public static function uninstall(){

	}

}
Glottypomo::instance();

endif;

/**
 * Autoload Glottypomo Classes
 *
 * @param string $classname
 */
function glottypomo_autoload( $classname ) {
	$class_path = dirname(__FILE__). sprintf('/include/class-%s.php' , $classname ) ; 
	if ( file_exists($class_path) )
		require_once $class_path;
}
spl_autoload_register( 'glottypomo_autoload' );



/**
 * Ajax handlers
 */
function register_ajax_handler( $action , $callback , $args = null ) {
	if ( ! isset($GLOBALS['wp_ajax']) )
		$GLOBALS['wp_ajax'] = new WP_Ajax();
	return $GLOBALS['wp_ajax']->register_handler( $action , $callback , $args );
}
/**
 * Ajax handlers
 */
function unregister_ajax_handler( $action , $callback = null ) {
	if ( ! isset($GLOBALS['wp_ajax']) )
		return;
	return $GLOBALS['wp_ajax']->unregister_handler( $action , $callback );
}

if ( is_admin() ) {
	GlottypomoAdmin::instance();
	GlottypomoAdminTaxonomy::instance();
	GlottypomoAdminMenus::instance();
} else {
	/**
	 *	Init Frontend textdomain loading
	 */
	GlottypomoTextdomains::instance();
}

<?php


if ( ! class_exists( 'GlottypomoAdmin' ) ):
class GlottypomoAdmin {
	private static $_instance = null;
	
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
		add_action( 'admin_init' , array( &$this , 'admin_init' ) );
		add_action( 'admin_menu' , array( &$this , 'add_admin_page' ) );
	}
	
	/**
	 * Add Admin page to menu
	 */
	function add_admin_page() {
		$page_hook = add_management_page( __( 'WP GlottyPoMo (management)' , 'wp-glottypomo' ), __( 'WP GlottyPoMo' , 'wp-glottypomo' ), 'manage_options', 'glottypomo-management', array( &$this , 'render_management_page' ) );
		add_action( "load-$page_hook" , array( &$this , 'enqueue_admin_page_assets' ) );
	}
	function render_management_page() {
		?><div class="wrap"><?php
			?><h2><?php _e( 'WP GlottyPoMo (management)' , 'wp-glottypomo' ); ?></h2><?php
			?><p><?php _e( 'Content for management' , 'wp-glottypomo' ); ?></p><?php
		?></div><?php
	}
	
	/**
	 * Render Admin page
	 */
	function render_admin_page() {
		?><div class="wrap"><?php
			?><h2><?php _e( 'WP GlottyPoMo Admin' , 'wp-glottypomo' ); ?></h2><?php
			?><p><?php _e( 'Admin Page content' , 'wp-glottypomo' ); ?></p><?php
		?></div><?php
	}
	function enqueue_admin_page_assets() {
		wp_enqueue_style( 'glottypomo-admin-page' , plugins_url( '/css/glottypomo-admin-page.css' , dirname(__FILE__) ) );

		wp_enqueue_script( 'glottypomo-admin-page' , plugins_url( 'js/glottypomo-admin-page.js' , dirname(__FILE__) ) );
		wp_localize_script('glottypomo-admin-page' , 'glottypomo_admin_page' , array(
		) );
	}
	/**
	 * Admin init
	 */
	function admin_init() {
	}

	/**
	 * Enqueue options Assets
	 */
	function enqueue_assets() {

	}

}

GlottypomoAdmin::instance();
endif;
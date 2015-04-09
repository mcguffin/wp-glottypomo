<?php


/**
 *	Edit Taxonomy translations
 */
if ( ! class_exists( 'GlottypomoAdminMuPlugins' ) ):
class GlottypomoAdminMuPlugins extends GlottypomoAdminPomo {

	private static $_instance = null;

	protected $textdomain_prefix = 'taxonomy';

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
	 *	Prevent cloning
	 */
	private function __clone() {}

	/**
	 * Private constructor
	 */
	private function __construct() {
		
	}

	/**
	 *	(Re-)Create pot file for taxonomy
	 *	
	 *	@param $object_identifier string taxonomy slug
	 */
	function init_translation( $object_identifier ) {
		$this->create_pot( $object_identifier );
	}

	/**
	 *	Enqueue options Assets.
	 *	Hooks into 'load-edit-tags.php'
	 */
	function enqueue_assets() {
		wp_register_style( 'glottypomo-taxonomy' , plugins_url('css/glottypomo-taxonomy.css', dirname(__FILE__)) );
		wp_enqueue_style( 'glottypomo-taxonomy' );
	}
	
	/**
	 *	Go to taxonomy editor UI page if necessary.
	 *	Hooks into 'load-admin.php'
	 */
// 	function admin_translate_taxonomy( ) {
// 		if ( isset( $_REQUEST['action'] ) && $_REQUEST['action'] == 'translate-taxonomy' ) {
// 			if ( isset( $_REQUEST['taxonomy'] , $_REQUEST['target_locale'] ) ) {
// 				$taxonomy = $_REQUEST['taxonomy'];
// 				$target_locale = preg_replace( '/[^a-z0-9_]/i' , '' , $_REQUEST['target_locale']);
// 				
// 				if ( taxonomy_exists($taxonomy) && $target_locale ) {
// 					$nonce_name = "translate-taxonomy-$taxonomy";
// 					check_admin_referer( $nonce_name );
// 					
// 					$taxonomy_object = get_taxonomy($taxonomy);
// 					if ( ! current_user_can( $taxonomy_object->cap->manage_terms ) )
// 						wp_die( 'Insufficient Privileges' );
// 					
// 					
// 				}
// 			}
// 		}
// 	}

	
	/**
	 *	Show translate-taxonomy links.
	 *	Hooks into 'after-{$taxonomy}-table'
	 */
	function show_taxo_translate_link( $taxonomy ) {
		$languages = glottypomo_language_code_sep( get_option( 'glottypomo_additional_languages' ) , '_' );

		?><div id="glottypomo-translate-links" class="postbox col-wrap"><?php
		?><div class="inside"><?php
		?><h3><?php _e( 'Multilingual' , 'wp-glottypomo' ); ?></h3><?php

		if ( $languages ) {
			foreach ( $languages as $language_code ) {
				$nonce_name = "translate-taxonomy-$taxonomy";
				$href = add_query_arg(array(
					'taxonomy' => $taxonomy,
					'action' => 'translate-taxonomy', 
					'target_locale' => $language_code,
					'_wpnonce' => wp_create_nonce( $nonce_name ),
				),admin_url('admin.php'));
				
				$langname = glottypomo_get_language_name( $language_code );
				if ( $this->has_po( $taxonomy , $language_code ) ) {
					$label = sprintf(_x('Edit %s Translation' , 'language' , 'wp-glottypomo' ), $langname );
					printf( '<a href="%s" class="button button-primary">%s</a>' , $href , $label );
				} else {
					$label = sprintf(_x('Translate to %s' , 'language' , 'wp-glottypomo' ), $langname );
					printf( '<a href="%s" class="button button-secondary">%s</a>' , $href , $label );
				}
			}
		}
		?></div><?php
		?></div><?php
		?><script type="text/javascript">
		(function($){
			$('#glottypomo-translate-links').insertBefore('#col-container');
		})(jQuery);
		</script><?php
	}
	
	
	/**
	 *	Create a pot file from taxonomy
	 *
	 *	@param $taxonomy object taxonomy object
	 */
	function create_pot( $taxonomy ) {
		global $current_user;
		get_currentuserinfo();
		
		if ( ! wp_is_writable(WP_LANG_DIR) )
			return;
		
		if ( ! is_object( $taxonomy ) )
			$taxonomy = get_taxonomy( $taxonomy );
		
		$save_pot_file = $this->get_pot_file_path( $taxonomy->name );

		if ( ! wp_mkdir_p( dirname( $save_pot_file ) ) )
			return false;
		
		$terms = get_terms( $taxonomy->name , array(
			'hide_empty' => false,
			'child_of' => 0,
		));
		
		$po = $this->get_po( );
		$po->set_header('Project-Id-Version' , "Taxonomy {$taxonomy->name}");
		
		foreach ( $terms as $term ) {
			$glotty_comment = array(
				'heading'	=> $term->name,
				'label'		=> __('Term'),
			);
			$entry = new Translation_Entry(array(
				'singular' => $this->prepare_string( $term->name ),
				'extracted_comments' => 'glottybot:'.json_encode($glotty_comment),
			));
			$po->add_entry( $entry );
			
// 			'\x0A' : NO
// 			'\x0D' : YES!
			
			$entry = new Translation_Entry(array(
				'singular' => $this->prepare_string($term->description ),
				'extracted_comments' => 'glottybot:'.json_encode(array( 'label' => __('Description') )),
			));
			$po->add_entry( $entry );
		}
		$po->export_to_file( $save_pot_file );
		//*/
	}
	
}

endif;
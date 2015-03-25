<?php


/**
 *	Bae class for Taxonomy + Menu translations
 */
if ( ! class_exists( 'GlottypomoAdminPomo' ) ):
abstract class GlottypomoAdminPomo {
	/**
	 *	string taxonomy | menu
	 */
// 	private $pomo_prefix = 'glottypomo';
	
	/**
	 *	string taxonomy | menu
	 */
	protected $textdomain_prefix;
	
	
	/**
	 *	@param $object_identifier string category slug or menu id
	 *	@param $textdomain_prefix string category | menu
	 *	@return string texdomain
	 */
	protected function get_textdomain( $object_identifier ){
		return "{$this->textdomain_prefix}-{$object_identifier}";
	}

	/**
	 *	Get po or file name without suffix relative to WP_LANG_DIR
	 *
	 *	@param $object_identifier string category slug or menu id
	 *	@param $language string language code
	 *	@return string Path to po or file without suffix relative to wp-content/languages
	 */
	private function get_pomo_file_name( $object_identifier , $locale = false ) {
		$ret = $this->get_textdomain( $object_identifier  );
		if ( $locale ) 
			$ret .= '-' . $locale;
		return $ret;
	}
	

	/**
	 *	Get po file name relative to WP_LANG_DIR
	 *
	 *	@param $object_identifier string category slug or menu id
	 *	@param $language string language code
	 *	@return string Path to po file relative to wp-content/languages
	 */
	protected function get_po_file_name( $object_identifier , $language ) {
		return $this->get_pomo_file_name( $object_identifier , $language  ).".po";
	}
	/**
	 *	Get absolute po file path
	 *
	 *	@param $object_identifier string category slug or menu id
	 *	@param $language string language code
	 *	@param $in_path
	 *	@return string Absolute Path to po file
	 */
	protected function get_po_file_path( $object_identifier , $language ) {
		return $this->get_lang_dir() . DIRECTORY_SEPARATOR . $this->get_po_file_name( $object_identifier , $language  );
	}
	/**
	 *	Return true if a po file exists
	 *
	 *	@param $object_identifier string category slug or menu id
	 *	@param $language string language code
	 *	@return bool whether a po file exists for the given textdomain and language
	 */
	protected function has_po( $object_identifier , $language ) {
		return file_exists( $this->get_po_file_path( $object_identifier , $language  ) );
	}
	/**
	 *	Get path of existing pofile.
	 *	
	 *	@param $object_identifier string category slug or menu id
	 *	@param $language string language code
	 *	@return bool | string path to po file, false if no po exists
	 */
	protected function get_po_file( $object_identifier , $language ) {
		$pofile = $this->get_po_file_path( $object_identifier , $language  );
		return file_exists( $pofile ) ? $pofile : false;
	}
	
	
	
	/**
	 *	Get mo file name relative to WP_LANG_DIR
	 *
	 *	@param $object_identifier string category slug or menu id
	 *	@param $language string language code
	 *	@return string Path to po file relative to wp-content/languages
	 */
	protected function get_mo_file_name( $object_identifier , $language ) {
		return $this->get_pomo_file_name( $object_identifier , $language  ).".mo";
	}
	/**
	 *	Get absolute mo file path
	 *
	 *	@param $object_identifier string category slug or menu id
	 *	@param $language string language code
	 *	@return string Absolute Path to po file
	 */
	protected function get_mo_file_path( $object_identifier , $language ) {
		return $this->get_lang_dir() . DIRECTORY_SEPARATOR . $this->get_mo_file_name( $object_identifier , $language );
	}
	/**
	 *	Return true if a mo file exists
	 *
	 *	@param $object_identifier string category slug or menu id
	 *	@param $language string language code
	 *	@return bool whether a mo file exists for the given textdomain and language
	 */
	protected function has_mo( $object_identifier , $language ) {
		return file_exists( $this->get_mo_file_path( $object_identifier , $language ) );
	}
	/**
	 *	Get path of existing mofile.
	 *	
	 *	@param $object_identifier string category slug or menu id
	 *	@param $language string language code
	 *	@return bool | string path to mo file, false if no mo exists
	 */
	protected function get_mo_file( $object_identifier , $language ) {
		$mofile = $this->get_mo_file_path( $object_identifier , $language );
		return file_exists( $mofile ) ? $mofile : false;
	}
	
	
	public function get_translations( $object_identifier ) {
		$glob_pattern = $this->get_po_file_path( $object_identifier , '*' );
		$regex_pattern = '/-([a-z_]+)\.po/i';//$this->get_po_file_path( $object_identifier , '([a-zA-Z-_]+)' );
		$results = glob($glob_pattern);
		$translations = array();
		foreach( $results as $result ) {
			$matches = array();
			preg_match( $regex_pattern , $result , $matches );
			if ( isset($matches[1]) ) {
				$locale = $matches[1];
				$translations[ $locale ] = $this->get_lang_dirname() . DIRECTORY_SEPARATOR . $this->get_pomo_file_name( $object_identifier , false );
			}
		}
		return $translations;
	}
	
	/**
	 *	Get pot file name relative to WP_LANG_DIR
	 *
	 *	@param $object_identifier string category slug or menu id
	 *	@return string Path to po file relative to wp-content/languages
	 */
	protected function get_pot_file_name( $object_identifier ) {
		$textdomain = $this->get_textdomain( $object_identifier );
		return "{$textdomain}.pot";
	}
	/**
	 *	Get absolute pot file path
	 *
	 *	@param $object_identifier string category slug or menu id
	 *	@return string Absolute Path to pot file
	 */
	protected function get_pot_file_path( $object_identifier ) {
		return $this->get_lang_dir() . DIRECTORY_SEPARATOR . $this->get_pot_file_name($object_identifier);
	}

	private function get_lang_dir() {
		return WP_CONTENT_DIR . DIRECTORY_SEPARATOR . $this->get_lang_dirname();
	}

	private function get_lang_dirname() {
		return basename(WP_LANG_DIR) . DIRECTORY_SEPARATOR . 'glottybot';
	}
	
	/**
	 *	Get Editor URL for taxonomy or menu translations.
	 *  Currently returns URLs as used by the loco translate plugin.
	 *	Should put that in a Loco bridge later.
	 *
	 *	@param $object_identifier string category slug or menu id
	 *	@param $locale string language code
	 *	@return string url to the po editor
	 */
	public function get_po_edit_url( $object_identifier , $locale ) {
		$textdomain = $this->get_textdomain( $object_identifier );
		
		$edit_url = admin_url( 'tools.php' );
		$edit_url = add_query_arg( array(
			'page'		=> 'glottypomo-management',
			'tab'		=> $this->textdomain_prefix,
			'init'		=> $object_identifier,
			'po'		=> $this->get_lang_dirname() . DIRECTORY_SEPARATOR . $this->get_pomo_file_name( $object_identifier , false ),
			'locale'	=> $locale,
		) , $edit_url );
		
		/**
		 * Filter the Editor URL for po file.
		 *
		 * @param string $edit_url          URL for editing the po file
		 * @param string $object_identifier Taxonomy slug or menu ID
		 * @param string $language          language
		 */
//		tools.php?page=glottypomo-management&locale=de_DE&po=plugins/lang-items/languages/langitems
		$edit_url = apply_filters( "glottypomo_edit_po_url" , $edit_url , $this->textdomain_prefix , $object_identifier , $locale );
		return $edit_url;
	}
	
	/**
	 *	Get PO Object.
	 *
	 *	@return object PO
	 */
	function get_po() {
		// 
		require_once ABSPATH . WPINC . '/pomo/po.php';

		$po = new PO();
		$po->set_headers(array(
			'Project-Id-Version' => "",
			'Report-Msgid-Bugs-To' => '',
			'POT-Creation-Date' => date('r'),
			'Last-Translator' => sprintf( '%s <%s>' , $current_user->display_name, $current_user->user_email ),
			'Language-Team' => '',
			'Language' => '',
			'MIME-Version' => '',
			'Content-Type'	=> 'text/plain; charset=UTF-8',
			'Content-Transfer-Encoding' => '8bit'
		));
		return $po;
	}
	
	protected function prepare_string( $str ) {
		$str = str_replace("\x0D",'',$str);
		return trim( $str );
	}
	
}

endif;
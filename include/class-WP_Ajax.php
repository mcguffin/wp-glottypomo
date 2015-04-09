<?php


if ( ! class_exists( 'WP_Ajax' ) ):

class WP_Ajax {
	
	private $actions = [];
	
	/**
	 *	Register ajax handler
	 *	Will create a nonce
	 *
	 *	@param $action		string Action name
	 *	@param $callback	callable Action Callback
	 *	@param $args		array
	 *						'callback_args'		// assoc arguments to pass to $callback default false
	 *						'check_capability'	// which wp capability to check. default false,
	 *						'with_frontend'		// publicly available default false
	 *						'response_type'		// response mime type to send.
	 *	@return string Ajax URL 
	 */
	function register_handler( $action , $callback , $args ) {
		if ( ! is_callable( $callback ) || ! $action )
			return new WP_Error( '' , 'Invalid ajax action or callback.' );

		$args = wp_parse_args( $args ,  array(
			'callback_args'		=> false,
			'check_capability'	=> false, // 'cap' , array('meta_cap',$arg)
			'with_frontend'		=> false,
			'response_type'		=> false,//'application/json',
		) );
		
		extract( $args );
		add_action( "wp_ajax_{$action}" , array( &$this , 'handler' ) );
		if ( $with_frontend )
			add_action( "wp_ajax_nopriv_{$action}" , array( &$this , 'handler' ) );
			
		$args['callback']		= $callback;
		$this->actions[$action] = $args;

		if ( ! defined( 'DOING_AJAX' ) ) {
			$ajax_url = admin_url( 'admin-ajax.php' );
			$ajax_url = add_query_arg( array( 
				'action'	=> $action,
				'_wpnonce' => wp_create_nonce( $action ),
			) , $ajax_url );
			return $ajax_url;
		}
	}
	/**
	 *	Unregister ajax handler
	 *
	 *	@param $action		string Action name
	 *	@param $callback	callable Action Callback
	 */
	function unregister_handler( $action , $callback = null ) {
		if ( has_action( "wp_ajax_{$action}" ) )
			remove_action( "wp_ajax_{$action}" , array( &$this , 'handler' ) );
		if ( has_action( "wp_ajax_nopriv_{$action}" ) )
			remove_action( "wp_ajax_nopriv_{$action}" , array( &$this , 'handler' ) );
		if ( isset( $this->actions[$action] ) )
			unset($this->actions[$action]);
	}
	/**
	 *	Global Ajax handler
	 */
	function handler() {
		$result = false;
		if ( isset( $_REQUEST['action'] , $_REQUEST['_wpnonce'] ) && isset( $this->actions[ $_REQUEST['action'] ] ) ) {
			$action = $_REQUEST['action'];
			if ( wp_verify_nonce( $_REQUEST['_wpnonce'] , $_REQUEST['action'] ) ) {
				extract( $this->actions[ $_REQUEST['action'] ] );
				if ( false === $check_capability || current_user_can( $check_capability ) ) {
					if ( is_callable( $callback ) ) {
						$callback_args = $callback_args ? wp_parse_args( $_REQUEST , $callback_args ) : array();
						
						if ( $response_type )
							header('Content-Type: ' . $response_type);

						$result = call_user_func( $callback , $callback_args );
						if ( ! is_null($result) ) {
							switch ( $response_type ) {
								case 'application/json':
									if ( ! is_string( $result ) )
										$result = json_encode( $result );
									echo $result;
									break;
								case 'text/plain':
								case 'text/html':
								default:
									if ( is_scalar( $result ) )
										echo $result;
									break;
							}
							
						}
					} else {
						$result = new WP_Error( 'callback_error' , __('Invalid callback') );
					}
				} else {
					$result = new WP_Error( 'permission_error' , __('Operation not permitted.') );
				}
			} else {
				$result = new WP_Error( 'nonce_error' , __('Nonce failed') );
			}
		}
		if ( is_wp_error( $result ) ) {
			do_action( "wp_ajax_error_{$action}" , $result );
			do_action( "wp_ajax_error" , $result );
		}
		exit('');
	}
}
endif;


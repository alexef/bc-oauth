<?php
/**
 * Class to create or login a user
 * 
 */
Class BC_OAuth_User {

	public $user_id;
	public $user_login;
	public $user_email;
	public $is_spammer = false;
	public $is_new_user;

	/**
	 * Function to create a User
	 * @param string $username Username
	 * @param string $email Email
	 * @param string $password Password
	 * @param string $firsname Firstname
	 * @param string $lastname Lastname
	 * @param string $avatar Avatar
	 * @param string $nickname Nickname
	 * @param string $nicename Nicename
	 * @param string $display_name Displayname
	 * @param Array $custom_attr Custom meta values
	 * @return bol whether the user is created successfully or not
	 */
	function bc_oauth_create_user($username, $email = '', $password = false, $firsname = false, $lastname = false, $avatar = false, $nickname = false, $nicename = false, $display_name = false, $custom_attr = false){

		// check if email is alredy in the system
		if( $email != '' && get_user_by('email', $email) )
			return false;

		// fix for empty e-mail
		if( $email == '' && ! defined( 'WP_IMPORTING' ) )
			define('WP_IMPORTING', true);	
			
		// if the username is alredy take generate a new one
		if ( username_exists( sanitize_user($username, true ) ) ) {
			do {
				$username = $username . '-' . rand();
			}
			while ( username_exists( sanitize_user($username, true ) ) );
		}

		// generate a new password
		if( !$password )
			$password = wp_generate_password ();

		$new_user_id = wp_create_user( sanitize_user($username, true), $password, $email);

		// if the new user is created set the new user as loggedin
		if( $new_user_id ){
			
			$this->user_id = $new_user_id;
			$this->user_email = $email;
			$this->is_new_user = true;

			if($firsname)		update_user_meta($new_user_id, 'first_name', $firsname);
			if($lastname)		update_user_meta($new_user_id, 'last_name', $lastname);
			if($nickname)		update_user_meta($new_user_id, 'nickname', $nickname);
			if($avatar)			update_user_meta($new_user_id, 'user_url', $avatar);
			if($nicename)		update_user_meta($new_user_id, 'user_nicename', $nicename);
			if($display_name)	update_user_meta($new_user_id, 'display_name', $display_name);

			if( $custom_attr && is_array($custom_attr) ){
				foreach ($custom_attr as $key => $value) {
					update_user_meta($new_user_id,$key, $value);
				}
			}

			if(function_exists('wpmu_welcome_user_notification') && $this->user_email != '')
				wpmu_welcome_user_notification($this->user_id, $password);
			
			if( $this->login_user() )
				return true;

			return false;
		 }

		 return false;
		
	}

	function check_user($email = false, $custom_value = false){
		if(!$email && !$custom_value)
			return false;
		
		if($email)
			$user = get_user_by('email', $email);

		if( $custom_value && is_array($custom_value) ){
			foreach ($custom_value as $key => $value) {
				$user = $this->get_user_by_cutom_meta_value($key,$value);
			}
		}			
			
		if($user){
			$this->user_id = $user->ID;
			$this->user_email = $user->user_email;
			$this->user_login = $user->user_login;
			// ckeck if user is spammer
			if ( 1 == $user->spam){
				$this->is_spammer = true;
				return new WP_Error('invalid_username', __('<strong>ERROR</strong>: Your account has been marked as a spammer.'));
			}
			
			return true;
		}

		return false;
	}

	function login_user(){
		
		if( $this->is_spammer == true )
			return new WP_Error('invalid_username', __('<strong>ERROR</strong>: Your account has been marked as a spammer.'));
			
		if( !$this->user_email && !$this->user_id )
			return false;
			
		wp_set_current_user( $this->user_id );
		wp_set_auth_cookie( $this->user_id, true );
		//do_action( 'wp_login', $this->user_login );
		return true;
	}

	function finish_login($param = false){
		?>
		
		<h2>Success</h2>
		<script type="text/javascript">
		window.close();
		if (window.opener && !window.opener.closed) {
			//window.opener.location.reload();
			window.opener.location.href = '<?php echo site_url(); if($param) echo "/?{$param}"; ?>';
		}
		</script>

		<noscript>You are loggedin successfully. Please close this window and reload the <?php echo site_url() ?> website.</noscript>

		<?php
	}

	function get_user_by_cutom_meta_value($key, $value){
		global $wpdb;

		$id = $wpdb->get_results( $wpdb->prepare("SELECT * FROM {$wpdb->usermeta} WHERE meta_key = '{$key}' AND meta_value = '{$value}';") );

		if(!$id[0])
			return false;
			
		$user = get_user_by('id', $id[0]->user_id);
		
		if($user)
			return $user;
			
		return false;

	}
	
}

?>

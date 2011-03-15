<?php


/**
 * Show the activated oauth logins
 * 
 */
function bc_oauth_show_login(){
	?>
	<div id="bc-oauth-logins">
		<span class="label"><?php echo apply_filters('bc_outh_login_label', 'or connect using') ?></span>
		<ul>
			<?php do_action('bc_oauth_before_login_icons') ?>
			
			<?php do_action('bc_oauth_login_icons') ?>

			<?php do_action('bc_oauth_after_login_icons') ?>
		</ul>
		<div style="clear: both;"></div>
	</div>
	<?php
}
add_action('login_form', 'bc_oauth_show_login');
// add buddypress support
add_action('bp_after_sidebar_login_form', 'bc_oauth_show_login');



/**
 * OpenID
 * 
 */
// show the OpenID login Link
function bc_oauth_show_openid_login_link(){
		
	if( !get_site_option('oid_oauth_enabled') )
		return false;
	
?>
			<li class="bc-oauth-tooltip">
				<a href="#" onclick="window.open('<?php echo site_url() ?>/wp-load.php?action=bc_oauth&service=openid', 'login', 'width=800, height=600'); return false;" title="Sign in with your OpenID account">
					<div class="icon openid"></div>
				</a>
			</li>
<?php 

}
add_action('bc_oauth_login_icons', 'bc_oauth_show_openid_login_link');

// show the OpenID login form
function bc_oauth_show_openid_login_form(){
	?>
	<form method="post" action="">
		<img src="http://wiki.openid.net/f/openid-logo-wordmark.png" alt="OpenID Logo" />
		<label>Please insert your OpenID-Identity-URL:</label>
		<input type="text" name="openid_identifier" value="" />
		<input type="submit" value="Verify" />
	</form>
	<?php
}


/**
 * Facebook
 * 
 */
function bc_oauth_show_facebook_login_link(){
	
	if( !get_site_option('f_oauth_enabled') )
		return false;
		
?>
			<li>
				<a href="#" onclick="window.open('<?php echo site_url() ?>/wp-load.php?action=bc_oauth&service=facebook', 'login', 'width=800, height=600'); return false;" title="Sign in with your Facebook profile">
				   <div class="icon facebook"></div>
				</a>
			</li>
<?php 

}
add_action('bc_oauth_login_icons', 'bc_oauth_show_facebook_login_link');

/**
 * twitter
 * 
 */
function bc_oauth_show_twitter_login_link(){
					
	if( !get_site_option('t_oauth_enabled') )
		return false;
	
?>
			<li>
				<a href="#" onclick="window.open('<?php echo site_url() ?>/wp-load.php?action=bc_oauth&service=twitter', 'login', 'width=800, height=600'); return false;" title="Connect using your Twitter profile">
				   <div class="icon twitter"></div>
				</a>
			</li>
<?php 
}
add_action('bc_oauth_login_icons', 'bc_oauth_show_twitter_login_link');

/**
 * GMail
 * 
 */
function bc_oauth_show_google_login_link(){
			
	if( !get_site_option('g_oauth_enabled') )
		return false;
	
?>
			<li>
				<a href="#" onclick="window.open('<?php echo site_url() ?>/wp-load.php?action=bc_oauth&service=google', 'login', 'width=800, height=600'); return false;" title="Connect using your Google account">
				   <div class="icon google"></div>
				</a>
			</li>
<?php 
}
add_action('bc_oauth_login_icons', 'bc_oauth_show_google_login_link');

/**
 * LinkedIn
 * 
 */
function bc_oauth_show_linkedin_login_link(){
		
	if( !get_site_option('l_oauth_enabled') )
		return false;
	
?>
			<li>
				<a href="#" onclick="window.open('<?php echo site_url() ?>/wp-load.php?action=bc_oauth&service=linkedin', 'login', 'width=800, height=600'); return false;" title="Connect using your LinkedIn profile">
				   <div class="icon linkedin"></div>
				</a>
			</li>
<?php 
}
add_action('bc_oauth_login_icons', 'bc_oauth_show_linkedin_login_link');

/**
 * Windows Live
 * 
 */
function bc_oauth_show_windowslive_login_link(){
					
	if( !get_site_option('wl_oauth_enabled') )
		return false;
	
?>
			<li>
				<a href="#" onclick="window.open('<?php echo site_url() ?>/wp-load.php?action=bc_oauth&service=live', 'login', 'width=800, height=600'); return false;"  title="Connect using Windows Live account">
				   <div class="icon microsoft"></div>
				</a>
			</li>
<?php 
}
add_action('bc_oauth_login_icons', 'bc_oauth_show_windowslive_login_link');

/**
 * Yahoo
 * 
 */
function bc_oauth_show_yahoo_login_link(){
					
	if( !get_site_option('y_oauth_enabled') )
		return false;
	
?>
			<li>
				<a href="#" onclick="window.open('<?php echo site_url() ?>/wp-load.php?action=bc_oauth&service=yahoo', 'login', 'width=800, height=600'); return false;" title="Connect using your Yahoo account">
				   <div class="icon yahoo"></div>
				</a>
			</li>
<?php 
}
add_action('bc_oauth_login_icons', 'bc_oauth_show_yahoo_login_link');

/**
 * Ohloh
 * 
 */
function bc_oauth_show_ohloh_login_link(){
				
	if( !get_site_option('o_oauth_enabled') )
		return false;
	
?>
			<li>
				<a href="#" onclick="window.open('<?php echo site_url() ?>/wp-load.php?action=bc_oauth&service=ohloh', 'login', 'width=820, height=600'); return false;" title="Connect using your Ohloh profile">
				   <div class="icon ohloh"></div>
				</a>
			</li>
<?php 
}
add_action('bc_oauth_login_icons', 'bc_oauth_show_ohloh_login_link');

/**
 * MySpace
 * 
 */
function bc_oauth_show_myspace_login_link(){
				
	if( !get_site_option('m_oauth_enabled') )
		return false;
	
?>
			<li>
				<a href="#" onclick="window.open('<?php echo site_url() ?>/wp-load.php?action=bc_oauth&service=myspace', 'login', 'width=820, height=600'); return false;" title="Connect using your MySpace ID">
				   <div class="icon myspace"></div>
				</a>
			</li>
<?php 
}
add_action('bc_oauth_login_icons', 'bc_oauth_show_myspace_login_link');





/**
 * Show message if User has no E-Mail Address and has buddypress installed.
 * 
 */
function bc_oauth_error_on_empty_email(){
	
	if( !get_site_option('bc_oauth_empty_email_error_enabled') )
		return false;
	
	if( !function_exists('bp_core_add_message') || !function_exists('bp_get_loggedin_user_link') || !BP_SETTINGS_SLUG )
		return false;
		
	global $bp;
	
	add_filter('attribute_escape', 'bc_oauth_remove_attribute_escape_in_bp_message', 5, 2);
	
	if( isset($bp->loggedin_user->userdata->user_email) && empty($bp->loggedin_user->userdata->user_email) )
		bp_core_add_message('Your e-mail address is empty, please go to your <a href="'. bp_get_loggedin_user_link() . BP_SETTINGS_SLUG .'">profile page</a> and insert one. Otherwise you cant\'t get <b>important notices and informations</b> from ' . get_bloginfo('name') . '.' , 'info');
		
}
add_action('init', 'bc_oauth_error_on_empty_email');


/**
 * Overwrite the escaping of html tags to build links in the message
 * 
 * @param string $safe_text the escaped string
 * @param string $text the original string
 */
function bc_oauth_remove_attribute_escape_in_bp_message($safe_text, $text) {
	remove_filter('attribute_escape', 'bc_oauth_remove_attribute_escape_in_bp_message');
	return $text;
}

/**
 * Show successfull registrations
 * 
 * @param string $key of the user_meta
 */
function bc_oauth_register_stats($key){
	global $wpdb;
	
	$count = $wpdb->get_results( $wpdb->prepare("SELECT count(meta_key) AS count FROM {$wpdb->usermeta} WHERE meta_key = '{$key}' GROUP BY meta_key;") );
	
	if($count[0]->count)
		return $count[0]->count;
		
		
	return 0;
}


function bc_oauth_handle_tracker() {
	
}
add_action('plugins_loaded', 'bc_oauth_handle_tracker');

?>

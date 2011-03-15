<?php

function bc_oauth_admin_settings() {
	global $wpdb;

	if ( isset( $_POST['bc-oauth-admin-submit'] ) && check_admin_referer('bc-oauth-admin') ) {

		// Error message if users email is empty
		update_site_option( 'bc_oauth_empty_email_error_enabled', isset( $_POST['oid_oauth_enabled'] ) );
		
		// OpenID
		update_site_option( 'oid_oauth_enabled', isset( $_POST['oid_oauth_enabled'] ) );
		if( isset($_POST['oid_storage_path']) ) update_site_option( 'oid_storage_path', $_POST['oid_storage_path'] );

		// facebook
		update_site_option( 'f_oauth_enabled', isset( $_POST['f_oauth_enabled'] ) );
		if( isset($_POST['f_oauth_consumer_key']) ) update_site_option( 'f_oauth_consumer_key', $_POST['f_oauth_consumer_key'] );
		if( isset($_POST['f_oauth_consumer_secret'])) update_site_option( 'f_oauth_consumer_secret', $_POST['f_oauth_consumer_secret'] );
		if( isset($_POST['f_server_uri'])) update_site_option( 'f_server_uri', $_POST['f_server_uri'] );

		// LinkedIn
		update_site_option( 'l_oauth_enabled', isset( $_POST['l_oauth_enabled'] ) );
		if( isset($_POST['l_oauth_consumer_key']) ) update_site_option( 'l_oauth_consumer_key', $_POST['l_oauth_consumer_key'] );
		if( isset($_POST['l_oauth_consumer_secret'])) update_site_option( 'l_oauth_consumer_secret', $_POST['l_oauth_consumer_secret'] );
		if( isset($_POST['l_server_uri'])) update_site_option( 'l_server_uri', $_POST['l_server_uri'] );
		
		// GMail
		update_site_option( 'g_oauth_enabled', isset( $_POST['g_oauth_enabled'] ) );
		if( isset($_POST['g_oauth_consumer_key']) ) update_site_option( 'g_oauth_consumer_key', $_POST['g_oauth_consumer_key'] );
		if( isset($_POST['g_oauth_consumer_secret'])) update_site_option( 'g_oauth_consumer_secret', $_POST['g_oauth_consumer_secret'] );
		if( isset($_POST['g_server_uri'])) update_site_option( 'g_server_uri', $_POST['g_server_uri'] );
		
		// Ohloh
		update_site_option( 'o_oauth_enabled', isset( $_POST['o_oauth_enabled'] ) );
		if( isset($_POST['o_oauth_consumer_key']) ) update_site_option( 'o_oauth_consumer_key', $_POST['o_oauth_consumer_key'] );
		if( isset($_POST['o_oauth_consumer_secret'])) update_site_option( 'o_oauth_consumer_secret', $_POST['o_oauth_consumer_secret'] );
		if( isset($_POST['o_server_uri'])) update_site_option( 'o_server_uri', $_POST['o_server_uri'] );
		
		// Twitter
		update_site_option( 't_oauth_enabled', isset( $_POST['t_oauth_enabled'] ) );
		if( isset($_POST['t_oauth_consumer_key']) ) update_site_option( 't_oauth_consumer_key', $_POST['t_oauth_consumer_key'] );
		if( isset($_POST['t_oauth_consumer_secret'])) update_site_option( 't_oauth_consumer_secret', $_POST['t_oauth_consumer_secret'] );
		if( isset($_POST['t_server_uri'])) update_site_option( 't_server_uri', $_POST['t_server_uri'] );
		
		// Yahoo
		update_site_option( 'y_oauth_enabled', isset( $_POST['y_oauth_enabled'] ) );
		if( isset($_POST['y_oauth_consumer_key']) ) update_site_option( 'y_oauth_consumer_key', $_POST['y_oauth_consumer_key'] );
		if( isset($_POST['y_oauth_consumer_secret'])) update_site_option( 'y_oauth_consumer_secret', $_POST['y_oauth_consumer_secret'] );
		if( isset($_POST['y_server_uri'])) update_site_option( 'y_server_uri', $_POST['y_server_uri'] );

		// Windows Live
		update_site_option( 'wl_oauth_enabled', isset( $_POST['wl_oauth_enabled'] ) );
		if( isset($_POST['wl_oauth_consumer_key']) ) update_site_option( 'wl_oauth_consumer_key', $_POST['wl_oauth_consumer_key'] );
		if( isset($_POST['wl_oauth_consumer_secret'])) update_site_option( 'wl_oauth_consumer_secret', $_POST['wl_oauth_consumer_secret'] );
		if( isset($_POST['wl_server_uri'])) update_site_option( 'wl_server_uri', $_POST['wl_server_uri'] );
		
		// MySpace
		update_site_option( 'm_oauth_enabled', isset( $_POST['m_oauth_enabled'] ) );
		if( isset($_POST['m_oauth_consumer_key']) ) update_site_option( 'm_oauth_consumer_key', $_POST['m_oauth_consumer_key'] );
		if( isset($_POST['m_oauth_consumer_secret'])) update_site_option( 'm_oauth_consumer_secret', $_POST['m_oauth_consumer_secret'] );
		if( isset($_POST['m_server_uri'])) update_site_option( 'm_server_uri', $_POST['m_server_uri'] );
		
		?>
			<div id="message" class="updated fade">
				<p style="line-height: 150%">Saved BC oAuth settings successfully</p>
			</div>
		<?php 
		
	}
	?>

	<div class="wrap">

		<h2><?php _e( 'BC oAuth Settings', 'bettercodes' ) ?></h2>

		<?php if ( isset( $_POST['bp-admin'] ) ) : ?>
			<div id="message" class="updated fade">
				<p><?php _e( 'Settings Saved', 'bettercodes' ) ?></p>
			</div>
		<?php endif; ?>

		<form action="" method="post" id="">

			<?php do_action( 'bc_oauth_admin_screen' ) ?>

			<h2>Show notice if user e-mail is empty</h2>
			<div class="widefat">
				<div class="">
					<p>
						<span>Some social networks like LinkedIn, Twitter and Ohloh doesen't publish the users email over their api's. You can throw a message for the user to fill in his email if its empty. <br /> (This requires the buddypress Plugin to show the message)<br /></span>
						<input type="checkbox" name="bc_oauth_empty_email_error_enabled" <?php if( get_site_option('bc_oauth_empty_email_error_enabled') ) echo "checked"; ?> /> <label>Show message if users email is empty.</label> 
						<br /><br />
					</p>
				</div>
			</div>

			<h2>OpenID</h2>
			<div class="widefat">
				<div class="">
					<p>
						<input type="checkbox" name="oid_oauth_enabled" <?php if(get_site_option('oid_oauth_enabled')) echo "checked"; ?> /> <label>Activate login with OpenID</label>
						<br />
						<label>Set the OpenId storage folder path. The folder must be writable by the webserver. For security reasons it is a good idea to put this folder outside of the webdirectory.</label>
						<input type="text" name="oid_storage_path" value="<?php if( !get_site_option('oid_storage_path') ) : echo BC_OAUTH_PLUGIN_DIR . '/OpenIdStorage'; else: echo get_site_option('oid_storage_path'); endif; ?>" />
						<br /><br />
						<?php echo bc_oauth_register_stats('bc_oauth_openid_registered') ?> new user over OpenID so far.
					</p>
				</div>
			</div>


			<h2>Facebook</h2>
			<div class="widefat">
				<div class="">
					<p>
						<input type="checkbox" name="f_oauth_enabled" <?php if(get_site_option('f_oauth_enabled')) echo "checked"; ?> /> <label>Activate login with Facebook</label>
						<br /><br />
						<label>Facebook App-ID</label><input type="text" name="f_oauth_consumer_key" value="<?php if(get_site_option('f_oauth_consumer_key')) echo get_site_option('f_oauth_consumer_key'); ?>" /><br />
						<label>Facebook App-Secret_key</label> <input type="text" name="f_oauth_consumer_secret" value="<?php if(get_site_option('f_oauth_consumer_secret')) echo get_site_option('f_oauth_consumer_secret'); ?>" /><br />
						<label>Facebook oAuth Server URL</label> <input type="text" name="f_server_uri" value="<?php if(get_site_option('f_server_uri')): echo get_site_option('f_server_uri'); else: ?>https://graph.facebook.com<?php endif; ?>" /> (Don't change this, unless you know what you're doing.)<br /><br />
						<?php echo bc_oauth_register_stats('bc_oauth_facebook_id') ?> new user over facebook so far.
					</p>
				</div>
			</div>

			<h2>LinkedIn</h2>
			<div class="widefat">
				<div class="">
					<p>
						<input type="checkbox" name="l_oauth_enabled" <?php if(get_site_option('l_oauth_enabled')) echo "checked"; ?> /> <label>Activate login with LinkedIn</label>
						<br /><br />
						<label>LinkedIn Api Key</label><input type="text" name="l_oauth_consumer_key" value="<?php if(get_site_option('l_oauth_consumer_key')) echo get_site_option('l_oauth_consumer_key'); ?>" /><br />
						<label>LinkedIn Api Secret</label> <input type="text" name="l_oauth_consumer_secret" value="<?php if(get_site_option('l_oauth_consumer_secret')) echo get_site_option('l_oauth_consumer_secret'); ?>" /><br />
						<label>LinkedIn oAuth Server URL</label> <input type="text" name="l_server_uri" value="<?php if( get_site_option('l_server_uri') ): echo get_site_option('l_server_uri'); else: ?>https://api.linkedin.com<?php endif; ?>" /> (Don't change this, unless you know what you're doing.)<br /><br />
						<?php echo bc_oauth_register_stats('bc_oauth_linkedin_id') ?> new user over LinkedIn so far.
					</p>
				</div>
			</div>
			
			<h2>Google Contacts</h2>
			<div class="widefat">
				<div class="">
					<p>
						<input type="checkbox" name="g_oauth_enabled" <?php if(get_site_option('g_oauth_enabled')) echo "checked"; ?> /> <label>Activate login with GMail</label>
						<br /><br />
						<label>Google Consumer Key</label><input type="text" name="g_oauth_consumer_key" value="<?php if(get_site_option('g_oauth_consumer_key')) echo get_site_option('g_oauth_consumer_key'); ?>" /><br />
						<label>Google Consumer Secret</label> <input type="text" name="g_oauth_consumer_secret" value="<?php if(get_site_option('g_oauth_consumer_secret')) echo get_site_option('g_oauth_consumer_secret'); ?>" /><br />
						<label>Google oAuth Server URL</label> <input type="text" name="g_server_uri" value="<?php if( get_site_option('g_server_uri') ): echo get_site_option('g_server_uri'); else: ?>https://www.google.com<?php endif; ?>" /> (Don't change this, unless you know what you're doing.)<br /><br />
						<?php echo bc_oauth_register_stats('bc_oauth_google_id') ?> new user over GMail so far.
					</p>
				</div>
			</div>
			
			<h2>Ohloh</h2>
			<div class="widefat">
				<div class="">
					<p>
						<input type="checkbox" name="o_oauth_enabled" <?php if(get_site_option('o_oauth_enabled')) echo "checked"; ?> /> <label>Activate login with Ohloh</label>
						<br /><br />
						<label>Ohloh Consumer Key</label><input type="text" name="o_oauth_consumer_key" value="<?php if(get_site_option('o_oauth_consumer_key')) echo get_site_option('o_oauth_consumer_key'); ?>" /><br />
						<label>Ohloh Consumer Secret</label> <input type="text" name="o_oauth_consumer_secret" value="<?php if(get_site_option('o_oauth_consumer_secret')) echo get_site_option('o_oauth_consumer_secret'); ?>" /><br />
						<label>Ohloh oAuth Server URL</label> <input type="text" name="o_server_uri" value="<?php if( get_site_option('o_server_uri') ): echo get_site_option('o_server_uri'); else: ?>https://www.ohloh.net<?php endif; ?>" /> (Don't change this, unless you know what you're doing.)<br /><br />
						<?php echo bc_oauth_register_stats('bc_oauth_ohloh_id') ?> new user over Ohloh so far.
					</p>
				</div>
			</div>
			
			<h2>Twitter</h2>
			<div class="widefat">
				<div class="">
					<p>
						<input type="checkbox" name="t_oauth_enabled" <?php if(get_site_option('t_oauth_enabled')) echo "checked"; ?> /> <label>Activate login with Twitter</label>
						<br /><br />
						<label>twitter Consumer Key</label><input type="text" name="t_oauth_consumer_key" value="<?php if(get_site_option('t_oauth_consumer_key')) echo get_site_option('t_oauth_consumer_key'); ?>" /><br />
						<label>twitter Consumer Secret</label> <input type="text" name="t_oauth_consumer_secret" value="<?php if(get_site_option('t_oauth_consumer_secret')) echo get_site_option('t_oauth_consumer_secret'); ?>" /><br />
						<label>twitter oAuth Server URL</label> <input type="text" name="t_server_uri" value="<?php if( get_site_option('t_server_uri') ): echo get_site_option('t_server_uri'); else: ?>https://api.twitter.com<?php endif; ?>" /> (Don't change this, unless you know what you're doing.)<br /><br />
						<?php echo bc_oauth_register_stats('bc_oauth_twitter_id') ?> new user over Twitter so far.
					</p>
				</div>
			</div>
			
			<h2>Yahoo</h2>
			<div class="widefat">
				<div class="">
					<p>
						<input type="checkbox" name="y_oauth_enabled" <?php if(get_site_option('y_oauth_enabled')) echo "checked"; ?> /> <label>Activate login with Yahoo</label>
						<br /><br />
						<label>Consumer Key</label><input type="text" name="y_oauth_consumer_key" value="<?php if(get_site_option('y_oauth_consumer_key')) echo get_site_option('y_oauth_consumer_key'); ?>" /><br />
						<label>Consumer Secret</label> <input type="text" name="y_oauth_consumer_secret" value="<?php if(get_site_option('y_oauth_consumer_secret')) echo get_site_option('y_oauth_consumer_secret'); ?>" /><br />
						<label>Yahoo oAuth Server URL</label> <input type="text" name="y_server_uri" value="<?php if( get_site_option('y_server_uri') ): echo get_site_option('y_server_uri'); else: ?>https://api.login.yahoo.com<?php endif; ?>" /> (Don't change this, unless you know what you're doing.)<br /><br />
						<p>
							Sign up on Yahoo and create a project on http://developer.apps.yahoo.com/projects/. Make sure you
							select "read" access to public profile information.
						</p>
						<?php echo bc_oauth_register_stats('bc_oauth_yahoo_id') ?> new user over Yahoo so far.
					</p>
				</div>
			</div>
			
			<h2>Windows Live</h2>
			<div class="widefat">
				<div class="">
					<p>
						<input type="checkbox" name="wl_oauth_enabled" <?php if(get_site_option('wl_oauth_enabled')) echo "checked"; ?> /> <label>Activate login with Windows Live ID</label>
						<br /><br />
						<label>Client ID</label><input type="text" name="wl_oauth_consumer_key" value="<?php if(get_site_option('wl_oauth_consumer_key')) echo get_site_option('wl_oauth_consumer_key'); ?>" /><br />
						<label>Secret Key</label> <input type="text" name="wl_oauth_consumer_secret" value="<?php if(get_site_option('wl_oauth_consumer_secret')) echo get_site_option('wl_oauth_consumer_secret'); ?>" /><br />
						<label>Windows Live oAuth Server URL</label> <input type="text" name="wl_server_uri" value="<?php if( get_site_option('wl_server_uri') ): echo get_site_option('wl_server_uri'); else: ?>https://consent.live.com<?php endif; ?>" /> (Don't change this, unless you know what you're doing.)<br /><br />
						<p>
							How to register as an Windows Live App? Check <a href="http://msdn.microsoft.com/en-us/library/ff751474.aspx" target="_blank">MSDN website</a>.
						</p>
						<?php echo bc_oauth_register_stats('bc_oauth_windowslive_id') ?> new user over Windows Live so far.
					</p>
				</div>
			</div>
			
			<h2>MySpace</h2>
			<div class="widefat">
				<div class="">
					<p>
						<input type="checkbox" name="m_oauth_enabled" <?php if(get_site_option('m_oauth_enabled')) echo "checked"; ?> /> <label>Activate login with MySpace ID</label>
						<br /><br />
						<label>Consumer Key</label><input type="text" name="m_oauth_consumer_key" value="<?php if(get_site_option('m_oauth_consumer_key')) echo get_site_option('m_oauth_consumer_key'); ?>" /><br />
						<label>Consumer Secret</label> <input type="text" name="m_oauth_consumer_secret" value="<?php if(get_site_option('m_oauth_consumer_secret')) echo get_site_option('m_oauth_consumer_secret'); ?>" /><br />
						<label>MySpace oAuth Server URL</label> <input type="text" name="m_server_uri" value="<?php if( get_site_option('m_server_uri') ): echo get_site_option('m_server_uri'); else: ?>http://api.myspace.com<?php endif; ?>" /> (Don't change this, unless you know what you're doing.)<br /><br />
						<p>
							How to register as an MySpace App? Check <a href="http://developer.myspace.com/" target="_blank">http://developer.myspace.com/</a>.<br />
							You need to create an MySpaceID Application.
						</p>
						<?php echo bc_oauth_register_stats('bc_oauth_myspace_id') ?> new user over MySpace so far.
					</p>
				</div>
			</div>
			
			<p class="submit">
				<input class="button-primary" type="submit" name="bc-oauth-admin-submit" id="bc-oauth-admin-submit" value="<?php _e( 'Save Settings', 'bettercodes' ) ?>"/>
			</p>

			<?php wp_nonce_field( 'bc-oauth-admin' ) ?>

		</form>

	</div>

<?php
}
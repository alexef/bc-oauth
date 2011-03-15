<?php
class BC_OAuth_Facebook {

	private $facebook_nick;
	private $facebook_email;
	private $facebook_fullname;

	private $facebook_login_link =  '/wp-load.php?action=bc_oauth&service=facebook';
	private $facebook_login_callback_link = '/wp-load.php?action=bc_oauth&service=facebook&finish';

	private $facebook_enabled;
	private $facebook_app_id;
	private $facebook_app_key;
	private $facebook_api_url;
	

	function __construct(){

		$this->facebook_app_id = get_site_option('f_oauth_consumer_key');
		$this->facebook_app_key = get_site_option('f_oauth_consumer_secret');
		$this->facebook_enabled = get_site_option('f_oauth_enabled');
		$this->facebook_api_url = get_site_option('f_server_uri');


	}

	function enabled(){
		if( $this->facebook_app_id && $this->facebook_app_key && $this->facebook_enabled )
			return true;

		return false;
	}

	function auth(){

		if( !$this->enabled() )
			return false;

		define("FACEBOOK_APP_ID",  $this->facebook_app_id); 
		define("FACEBOOK_APP_SECRET", $this->facebook_app_key);

		define("FACEBOOK_OAUTH_HOST", $this->facebook_api_url);

		define("FACEBOOK_AUTHORIZE_URL", FACEBOOK_OAUTH_HOST . "/oauth/authorize");
		define("FACEBOOK_ACCESS_TOKEN_URL", FACEBOOK_OAUTH_HOST . "/oauth/access_token");


		define('OAUTH_TMP_DIR', function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : realpath($_ENV["TMP"]));


		//  Init the OAuthStore
		$options = array(
			'consumer_key' => FACEBOOK_APP_ID,
			'consumer_secret' => FACEBOOK_APP_SECRET,
			'request_token_uri' => FACEBOOK_ACCESS_TOKEN_URL,
			'access_token_uri' => FACEBOOK_AUTHORIZE_URL
		);
		// Note: do not use "Session" storage in production. Prefer a database
		// storage, such as MySQL.
		OAuthStore::instance("Session", $options);

		try	{
			//  STEP 1:  If we do not have an OAuth token yet, go get one
			if ( !isset($_GET["code"]) && !isset($_GET['finish'])) {

				$getAuthTokenParams = array('client_id' => FACEBOOK_APP_ID,
					'redirect_uri' => get_site_url() . $this->facebook_login_callback_link,
					'type' => 'web_server',
					'display' => 'popup',
					'scope' => 'email');

				header("Location: " . FACEBOOK_AUTHORIZE_URL . '?' . http_build_query($getAuthTokenParams, '', '&'));
			}

			if ( isset($_GET["code"]) && isset($_GET['finish'])) {
				//  STEP 2:  Get an access token
				try {
							
					$accessTokenParams = array(
						'client_id' => FACEBOOK_APP_ID,
						'redirect_uri' => get_site_url() . $this->facebook_login_callback_link,
						'client_secret' => FACEBOOK_APP_SECRET,
						'code' => $_GET["code"]
					);

					$request = new OAuthRequester( FACEBOOK_ACCESS_TOKEN_URL . '?' . http_build_query($accessTokenParams, '', '&'), 'GET' );
					$result = $request->doRequest( 0, array(CURLOPT_SSL_VERIFYPEER => false) );

				} catch (OAuthException2 $e) {
					//var_dump($e);
					// Something wrong with the oauth_token.
					// Could be:
					// 1. Was already ok
					// 2. We were not authorized
					return;
				}

				if( isset($result['body']) )
					parse_str($result['body'], $accessToken);


					if( $accessToken['access_token'] ){

						unset($accessTokenParams['code']);
						$accessTokenParams['access_token'] = $accessToken['access_token'];

						// make the docs requestrequest.
						$request = new OAuthRequester( FACEBOOK_OAUTH_HOST . '/me', 'GET', $accessTokenParams);
						$result = $request->doRequest(0, array(CURLOPT_SSL_VERIFYPEER => false) );
						
						$user = json_decode($result['body']);
						
						if( isset($user->id) && isset($user->email) && isset($user->first_name) && isset($user->last_name) ){
							$create_user = new BC_OAuth_User();
							if( !$create_user->check_user(false, array('bc_oauth_facebook_id' => $user->id) ) && !$create_user->check_user($user->email) ){
								$create_user->bc_oauth_create_user ($user->last_name, $user->email, false, $user->first_name, $user->last_name, false, false, false, "{$user->first_name} {$user->last_name}", array('bc_oauth_facebook_id' => $user->id) );
							} else {
								$create_user->login_user();
							}
	
							if( is_user_logged_in() ){
								
								$tracknew = '';
								if($create_user->is_new_user)
									$tracknew = "bc_oauth=facebook";
								
								$create_user->finish_login($tracknew);
								
							} else {
								echo "There was a problem while logging you in.";
							}
						} else {
							echo "There was a problem fetching your profile from Facebook.";
						}

					} else {
						echo "There was a problem while logging in.";
					}
			}
			
			/**
			 * Close window if the user denied the login
			 */
			if( isset($_GET['error_reason']) && $_GET['error_reason'] == 'user_denied' ){
				?>
					<script type="text/javascript">
					<!--
					window.close();
					//-->
					</script>
				<?php 
			}
			
		} catch(OAuthException2 $e) {
			echo "OAuthException:  " . $e->getMessage();
			//var_dump($e);
		}

	}


	
}

?>

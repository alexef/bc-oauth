<?php
class BC_OAuth_Google {

	private $consumer_key;
	private $consumer_secret;
	private $oauth_host;
	private $oauth_enabled;

	private $call_back;
	
	function  __construct() {

		$this->oauth_enabled = get_site_option('g_oauth_enabled');
		$this->consumer_key = get_site_option('g_oauth_consumer_key');
		$this->consumer_secret = get_site_option('g_oauth_consumer_secret');
		$this->oauth_host = get_site_option('g_server_uri');
		
		$this->call_back = site_url() . '/wp-load.php?action=bc_oauth&service=google';

	}
	
	/**
	 * Check if this login type is enabled
	 * @return bol
	 */
	function enabled(){
		if( $this->consumer_key && $this->consumer_secret && $this->oauth_enabled )
			return true;

		return false;
	}

	function auth(){

		define("CONSUMER_KEY", $this->consumer_key);
		define("CONSUMER_SECRET", $this->consumer_secret);

		define("OAUTH_HOST", $this->oauth_host);
		define("REQUEST_TOKEN_URL", OAUTH_HOST . "/accounts/OAuthGetRequestToken");
		define("AUTHORIZE_URL", OAUTH_HOST . "/accounts/OAuthAuthorizeToken");
		define("ACCESS_TOKEN_URL", OAUTH_HOST . "/accounts/OAuthGetAccessToken");

		define('OAUTH_TMP_DIR', function_exists('sys_get_temp_dir') ? sys_get_temp_dir() : realpath($_ENV["TMP"]));

		//  Init the OAuthStore
		$options = array(
			'consumer_key' => CONSUMER_KEY,
			'consumer_secret' => CONSUMER_SECRET,
			'server_uri' => OAUTH_HOST,
			'request_token_uri' => REQUEST_TOKEN_URL,
			'authorize_uri' => AUTHORIZE_URL,
			'access_token_uri' => ACCESS_TOKEN_URL
		);
		// Note: do not use "Session" storage in production. Prefer a database
		// storage, such as MySQL.
		OAuthStore::instance("Session", $options);

		try {
			//  STEP 1:  If we do not have an OAuth token yet, go get one
			if (empty($_GET["oauth_token"])){
				$getAuthTokenParams = array(
					'scope' => 'http://www.google.com/m8/feeds/contacts/default/full',
					'xoauth_displayname' => 'bettercodes.org',
					'oauth_callback' => $this->call_back
					);

				// get a request token
				$tokenResultParams = OAuthRequester::requestRequestToken(CONSUMER_KEY, 0, $getAuthTokenParams, 'POST', array(), array(CURLOPT_SSL_VERIFYPEER => 0 ) );

				//  redirect to the google authorization page, they will redirect back
				header("Location: " . AUTHORIZE_URL . "?oauth_token=" . $tokenResultParams['token']);
			} else {
				//  STEP 2:  Get an access token
				$oauthToken = $_GET["oauth_token"];

				// echo "oauth_verifier = '" . $oauthVerifier . "'<br/>";
				$tokenResultParams = $_GET;

				try {
					OAuthRequester::requestAccessToken(CONSUMER_KEY, $oauthToken, 0, 'POST', $_GET, array(CURLOPT_SSL_VERIFYPEER => 0 ));
				} catch (OAuthException2 $e) {
					//var_dump($e);
					// Something wrong with the oauth_token.
					// Could be:
					// 1. Was already ok
					// 2. We were not authorized
					return;
				}


				// make the docs requestrequest.
				$request = new OAuthRequester("http://www.google.com/m8/feeds/contacts/default/full", 'GET', $tokenResultParams);
				$result = $request->doRequest( 0, array(CURLOPT_SSL_VERIFYPEER => 0) );
				if ($result['code'] == 200) {
					$xml = simplexml_load_string($result['body']);
					//var_dump($xml);
					
					if($xml->author->name && $xml->author->email){
						
						$name = (string)$xml->author->name;
						$email = (string)$xml->author->email;
						
						$create_user = new BC_OAuth_User();
						if( !$create_user->check_user($email) ){
							$create_user->bc_oauth_create_user($name, $email, false, false, false, false, false, false, $name, array('bc_oauth_google_id' => $email) );
						} else {
							$create_user->login_user();
						}
	
						if( is_user_logged_in() ){
							$tracknew = '';
							if($create_user->is_new_user)
								$tracknew = "bc_oauth=google";
							$create_user->finish_login($tracknew);
							return true;
						} else {
							echo "There was a problem while logging in. Please close this window and try it again.";
							return false;
						}
					}
					echo "There was a problem while logging in. Please close this window and try it again.";
					return false;					
					
				} else {
					echo 'Error';
					return false;
				}
			}
		}
		catch(OAuthException2 $e) {
			echo "OAuthException:  " . $e->getMessage();
			//var_dump($e);
		}

		
	}


}

?>

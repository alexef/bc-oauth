<?php

class BC_OAuth_LinkedIn {

	private $consumer_key;
	private $consumer_secret;
	private $oauth_host;
	private $oauth_enabled;

	private $login_callback_link = '/wp-load.php?action=bc_oauth&service=linkedin';
	

	function __construct() {
		$this->consumer_key = get_site_option('l_oauth_consumer_key');
		$this->consumer_secret = get_site_option('l_oauth_consumer_secret');
		$this->oauth_enabled = get_site_option('l_oauth_enabled');
		$this->oauth_host = get_site_option('l_server_uri');
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

		define("CONSUMER_KEY", $this->consumer_key); //
		define("CONSUMER_SECRET", $this->consumer_secret); //

		define("OAUTH_HOST", $this->oauth_host);
		define("REQUEST_TOKEN_URL", OAUTH_HOST . "/uas/oauth/requestToken");
		define("AUTHORIZE_URL", "https://www.linkedin.com/uas/oauth/authenticate");
		define("ACCESS_TOKEN_URL", OAUTH_HOST . "/uas/oauth/accessToken");

		// important, you need to add the ~ at the end not url encoded!!!
		define("USER_PROFILE_INFORMATIONS", OAUTH_HOST . "/v1/people/~:(id,first-name,last-name,picture-url)"); // http://developer.linkedin.com/docs/DOC-1002

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
			if (empty($_GET["oauth_token"])) {
				
				
				$getAuthTokenParams = array( 'oauth_callback' => get_site_url() . $this->login_callback_link );

				// get a request token
				$tokenResultParams = OAuthRequester::requestRequestToken(CONSUMER_KEY, 0, $getAuthTokenParams, 'POST', array(), array(CURLOPT_SSL_VERIFYPEER => 0 ) );

				//  redirect to the LinkedIn authorization page, they will redirect back
				header("Location: " . $tokenResultParams['authorize_uri'] . "?oauth_token=" . $tokenResultParams['token']);
			}

			//  STEP 2:  Get an access token
			if( !empty($_GET["oauth_token"]) ){
				
				$oauthToken = $_GET["oauth_token"];

				// echo "oauth_verifier = '" . $oauthVerifier . "'<br/>";
				$tokenResultParams['oauth_token'] = $_GET["oauth_token"];

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
				
				
				$request = new OAuthRequester(USER_PROFILE_INFORMATIONS, 'GET', $tokenResultParams);
				$result = $request->doRequest(0, array(CURLOPT_SSL_VERIFYPEER => 0 ));
				if ($result['code'] == 200) {
					$xml = simplexml_load_string($result['body']);
						
					if( $xml->{'id'} && $xml->{'first-name'} && $xml->{'last-name'} ){
						
						$id = (string) $xml->{'id'};
						$firstname = (string) $xml->{'first-name'};
						$lastname = (string) $xml->{'last-name'};
						
						$create_user = new BC_OAuth_User();
						if( !$create_user->check_user( false, array('bc_oauth_linkedin_id' => $id) ) ){
							$create_user->bc_oauth_create_user($lastname, '', false, $firstname, $lastname, false, false, false, "{$firstname} {$lastname}", array('bc_oauth_linkedin_id' => $id) );
						} else {
							$create_user->login_user();
						}
	
						if( is_user_logged_in() ){
							$tracknew = '';
							if($create_user->is_new_user)
								$tracknew = "bc_oauth=linkedin";
							$create_user->finish_login($tracknew);
							return true;
						} else {
							echo "There was a problem while logging in. Please close this window and try it again.";
							return false;
						}
					} else {
						echo "There was a problem while logging in. Please close this window and try it again.";
						return false;
					}	
					
					
				} else {
					echo 'Error';
				}
				
	
			} // end step 2
			


		} catch(OAuthException2 $e) {
			echo "OAuthException:  " . $e->getMessage();
			var_dump($e);
		}


		
	}

}
?>

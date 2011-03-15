<?php
class BC_OAuth_Ohloh {

	private $consumer_key;
	private $consumer_secret;
	private $oauth_host;
	private $oauth_enabled;
	
	private $call_back;

	function  __construct() {
		
		$this->oauth_enabled = get_site_option('o_oauth_enabled');
		$this->consumer_key = get_site_option('o_oauth_consumer_key');
		$this->consumer_secret = get_site_option('o_oauth_consumer_secret');
		$this->oauth_host = get_site_option('o_server_uri');
		
		$this->call_back = urlencode( site_url() . '/wp-load.php?action=bc_oauth&service=ohloh' );

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
		define("REQUEST_TOKEN_URL", OAUTH_HOST . "/oauth/request_token");
		define("AUTHORIZE_URL", OAUTH_HOST . "/oauth/authorize");
		define("ACCESS_TOKEN_URL", OAUTH_HOST . "/oauth/access_token");

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
			if ( ( !isset($_GET["oauth_token"]) || empty($_GET["oauth_token"]) ) && !stristr($_GET['service'], 'ohloh?') ) {
				
				$getAuthTokenParams = array();

				// get a request token
				$tokenResultParams = OAuthRequester::requestRequestToken(CONSUMER_KEY, 0, $getAuthTokenParams, 'POST', array(), array( CURLOPT_SSL_VERIFYPEER => 0 ) );
				
				//  redirect to the google authorization page, they will redirect back
				header("Location: {$tokenResultParams['authorize_uri']}?oauth_callback={$this->call_back}&oauth_token={$tokenResultParams['token']}");
				
			} else {
				
				// Ohloh fix
				if( stristr($_GET['service'], 'ohloh?') ){
					$_GET["oauth_token"] = str_replace('ohloh?oauth_token=', '', $_GET['service']);
					$_GET['service'] = str_replace('ohloh?', '', $_GET['service']);
				}
				
				//  STEP 2:  Get an access token
				$oauthToken = $_GET["oauth_token"];
				
				$tokenResultParams['oauth_token'] = $_GET["oauth_token"];

				try {
					OAuthRequester::requestAccessToken(CONSUMER_KEY, $oauthToken, 0, 'POST', array(), array( CURLOPT_SSL_VERIFYPEER => 0 ) );
				} catch (OAuthException2 $e) {
					//var_dump($e);
					// Something wrong with the oauth_token.
					// Could be:
					// 1. Was already ok
					// 2. We were not authorized
					return;
				}

				// make the docs requestrequest.
				$request = new OAuthRequester("https://www.ohloh.net/accounts/me.xml", 'GET', $tokenResultParams);
				$result = $request->doRequest(0, array( CURLOPT_SSL_VERIFYPEER => 0 ));
				if ($result['code'] == 200) {
					$xml = simplexml_load_string($result['body']);
					
					if( (string) $xml->{'status'} == 'success' && $xml->{'result'}->{'account'}->{'id'} && $xml->{'result'}->{'account'}->{'name'} ){
						$id = (string) $xml->{'result'}->{'account'}->{'id'};
						$name = (string) $xml->{'result'}->{'account'}->{'name'};

						$create_user = new BC_OAuth_User();
						if( !$create_user->check_user( false, array('bc_oauth_ohloh_id' => $id) ) ){
							$create_user->bc_oauth_create_user($name, false, false, false, false, false, false, false, $name, array('bc_oauth_ohloh_id' => $id) );
						} else {
							$create_user->login_user();
						}
	
						if( is_user_logged_in() ){
							$tracknew = '';
							if($create_user->is_new_user)
								$tracknew = "bc_oauth=ohloh";
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
			}
		} catch(OAuthException2 $e) {
			echo "OAuthException:  " . $e->getMessage();
			//var_dump($e);
		}

		
	}


}

?>

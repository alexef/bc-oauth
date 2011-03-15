<?php
class BC_OAuth_Yahoo {

	private $consumer_key;
	private $consumer_secret;
	private $oauth_host;
	private $oauth_enabled;
	
	private $call_back;

	function __construct(){
		
		$this->oauth_enabled = get_site_option('y_oauth_enabled');
		$this->consumer_key = get_site_option('y_oauth_consumer_key');
		$this->consumer_secret = get_site_option('y_oauth_consumer_secret');
		$this->oauth_host = get_site_option('y_server_uri');
		
		$this->call_back = site_url() . '/wp-load.php?action=bc_oauth&service=yahoo';
		
	}

	function auth(){
	
		define("CONSUMER_KEY", $this->consumer_key); // 
		define("CONSUMER_SECRET", $this->consumer_secret); // 
		
		define("OAUTH_HOST", $this->oauth_host);
		define("REQUEST_TOKEN_URL", OAUTH_HOST . "/oauth/v2/get_request_token");
		define("AUTHORIZE_URL", OAUTH_HOST . "/oauth/v2/request_auth");
		define("ACCESS_TOKEN_URL", OAUTH_HOST . "/oauth/v2/get_token");
		
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
			if ( empty($_GET["oauth_token"]) ) {
				$getAuthTokenParams = array(
					'oauth_callback' => $this->call_back);
		
				// get a request token
				$tokenResultParams = OAuthRequester::requestRequestToken(CONSUMER_KEY, 0, $getAuthTokenParams, 'POST', array(), array( CURLOPT_SSL_VERIFYPEER => 0 ));
				
				//  redirect to the twitter authorization page, they will redirect back
				header("Location: " . $tokenResultParams['authorize_uri'] . "?oauth_callback=" . urlencode($this->call_back) . "&oauth_token=" . $tokenResultParams['token']);
			} else {

				//  STEP 2:  Get an access token
				$oauthToken = $_GET["oauth_token"];

				// echo "oauth_verifier = '" . $oauthVerifier . "'<br/>";
				$tokenResultParams['oauth_token'] = $_GET["oauth_token"];
				$tokenResultParams['format'] = 'json';
				
				try {
				   OAuthRequester::requestAccessToken(CONSUMER_KEY, $oauthToken, 0, 'POST', $_GET, array( CURLOPT_SSL_VERIFYPEER => 0 ) );
				} catch (OAuthException2 $e) {
					//var_dump($e);
				    // Something wrong with the oauth_token.
				    // Could be:
				    // 1. Was already ok
				    // 2. We were not authorized
				    return;
				}
				
				// make the request.
				$request = new OAuthRequester("http://query.yahooapis.com/v1/yql", 'GET', array('q' => 'SELECT * FROM social.profile WHERE guid=me', 'format' => 'json') );
				$result = $request->doRequest(0);
				if ($result['code'] == 200) {
					
					$user = json_decode($result['body']);
					
					if( count($user->query->results->profile) ){
						
						$id = $user->query->results->profile->guid;
						$firstname = $user->query->results->profile->givenName;
						$lastname = $user->query->results->profile->familyName;
						$nickname = $user->query->results->profile->nickname;
						$email = $user->query->results->profile->emails->handle;
						
						$create_user = new BC_OAuth_User();
						if( !$create_user->check_user($email) ){
							$create_user->bc_oauth_create_user($lastname, $email, false, $firstname, $lastname, false, $nickname, false, "{$firstname} {$lastname}", array('bc_oauth_yahoo_id' => $id) );
						} else {
							$create_user->login_user();
						}
	
						if( is_user_logged_in() ){
							$tracknew = '';
							if($create_user->is_new_user)
								$tracknew = "bc_oauth=yahoo";
							$create_user->finish_login($tracknew);
							return true;
						} else {
							echo "There was a problem while logging in. Please close this window and try it again.";
							return false;
						}
					} else {
						echo "Error fetching your profile from Yahoo.";
					}
					
					
				} else {
					echo 'Error connecting to Yahoo.';
				}
			}
		} catch(OAuthException2 $e) {
			//echo "OAuthException:  " . $e->getMessage();
		}
				
		
	}


}
?>

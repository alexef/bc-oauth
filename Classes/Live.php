<?php
class BC_OAuth_Windows_Live {

	private $consumer_key;
	private $consumer_secret;
	private $oauth_host;
	private $oauth_enabled;
	
	private $call_back;

	function __construct(){
		
		$this->oauth_enabled = get_site_option('wl_oauth_enabled');
		$this->consumer_key = get_site_option('wl_oauth_consumer_key');
		$this->consumer_secret = get_site_option('wl_oauth_consumer_secret');
		$this->oauth_host = get_site_option('wl_server_uri');
		
		$this->call_back = site_url() . '/wp-load.php?action=bc_oauth&service=live';
		
	}

	function auth(){
	
		define("CONSUMER_KEY", $this->consumer_key); // 
		define("CONSUMER_SECRET", $this->consumer_secret); // 
		
		define("OAUTH_HOST", $this->oauth_host);
		define("REQUEST_TOKEN_URL", OAUTH_HOST . "/Connect.aspx");
		define("AUTHORIZE_URL", OAUTH_HOST . "/AccessToken.aspx");
		define("ACCESS_TOKEN_URL", OAUTH_HOST . "/RefreshToken.aspx");
		
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
			
			$getAuthTokenParams = array(
				'wrap_client_id' => CONSUMER_KEY,
				'wrap_callback' => $this->call_back,
				'wrap_scope' => 'WL_Contacts.View, WL_Profiles.View,Messenger.SignIn'
			);
			
			//  STEP 1:  If we do not have an OAuth token yet, go get one
			if ( empty($_GET["wrap_verification_code"]) ) {
				//  redirect to the Windows Live authorization page, they will redirect back
				header("Location: " . REQUEST_TOKEN_URL . '?' . http_build_query($getAuthTokenParams));
			} else {
				//  STEP 2:  Get an access token
				$getAuthTokenParams['wrap_client_secret'] = CONSUMER_SECRET;
				$getAuthTokenParams['wrap_verification_code'] = $_GET["wrap_verification_code"];
				$getAuthTokenParams['idtype'] = 'CID';
				
				$accessToken = $this->windowsLiveRequest(AUTHORIZE_URL, $getAuthTokenParams);
				//$test = OAuthRequester::requestRequestToken(CONSUMER_KEY, 0, $getAuthTokenParams, 'POST' );
				parse_str($accessToken, $accessToken);
				
				$refreshAccessToken = $this->windowsLiveRequest(ACCESS_TOKEN_URL, array_merge($getAuthTokenParams, $accessToken) );
				parse_str($refreshAccessToken, $refreshAccessToken);
				
				$user = $this->windowsLiveRequest("http://apis.live.net/V4.1/cid-{$accessToken['uid']}/Profiles", false, true, array("Accept: application/json", "Content-Type: application/json", "Authorization: WRAP access_token={$refreshAccessToken['wrap_access_token']}") );
				
				$user = json_decode($user);
				
				if ( isset($user->Entries[0]->Cid) && isset($user->Entries[0]->Emails[0]->Address) ) {
						
					$id = $user->Entries[0]->Cid;
					$firstname = $user->Entries[0]->FirstName;
					$lastname = $user->Entries[0]->LastName;
					$email = $user->Entries[0]->Emails[0]->Address;
					
					$create_user = new BC_OAuth_User();
					if( !$create_user->check_user($email) ){
						$create_user->bc_oauth_create_user($lastname, $email, false, $firstname, $lastname, false, false, false, "{$firstname} {$lastname}", array('bc_oauth_windowslive_id' => $id) );
					} else {
						$create_user->login_user();
					}

					if( is_user_logged_in() ){
						$tracknew = '';
						if($create_user->is_new_user)
							$tracknew = "bc_oauth=windowslive";						
						$create_user->finish_login($tracknew);
						return true;
					} else {
						echo "There was a problem while logging in. Please close this window and try it again.";
						return false;
					}
					
					
				} else {
					echo 'Error connecting to Windows Live.';
				}
			}
		} catch(OAuthException2 $e) {
			//echo "OAuthException:  " . $e->getMessage();
		}
				
		
	}
	
	function windowsLiveRequest($url, $params = false, $method_get = false, $header = false){
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		if(!$method_get)
			curl_setopt($ch, CURLOPT_POST, true);
		
		if($params)
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			
		if($header)
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			
		
		$result = curl_exec($ch);
		
		curl_close($ch);
		
		return $result;
		
	}


}
?>

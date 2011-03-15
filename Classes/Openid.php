<?php
/**
 * Friendly topken and inspiered by:
 * http://www.drweb.de/magazin/openid-demystified-teil-1/
 */
class BC_OAuth_Openid {

	private $oauth_enabled;
	
	private $openid_nick;
	private $openid_email;
	private $openid_fullname;

	private $openid_login_link =  '/wp-load.php?action=bc_oauth&service=openid';
	private $openid_login_callback_link = '/wp-load.php?action=bc_oauth&service=openid&finish';

	private $openid_storage_folder;
	
	function __construct(){
		
		$this->oauth_enabled = get_site_option('oid_oauth_enabled');
		$this->openid_storage_folder = get_site_option('oid_storage_path');
		
	}
	
	/**
	 * Check if this login type is enabled
	 * @return bol
	 */
	function enabled(){
		if( $this->oauth_enabled )
			return true;

		return false;
	}

	function auth($openid){
		
		$store = new Auth_OpenID_FileStore( $this->openid_storage_folder );
		$consumer = new Auth_OpenID_Consumer($store);
		$auth = $consumer->begin($openid);
		if (!$auth) {
			echo "<h1>Please insert your OpenID!</h1>";
		}
		$sreg = Auth_OpenID_SRegRequest::build(array('email', 'fullname', 'dob', 'language'), array('nickname'));
		if (!$sreg) {
			echo "<h1>We couldn't retrieve your userdata!</h1>";
		}
		$auth->addExtension($sreg);
		
		$url = $auth->redirectURL( get_site_url() . $this->openid_login_link, get_site_url() . $this->openid_login_callback_link );
		header('Location: ' . $url);

	}

	function callback(){
		$store = new Auth_OpenID_FileStore($this->openid_storage_folder);
		$consumer = new Auth_OpenID_Consumer($store);
		$response = $consumer->complete( get_site_url() . $this->openid_login_callback_link );
		if ($response->status == Auth_OpenID_SUCCESS) {
			$_SESSION['OPENID_AUTH'] = true;
			$sreg = new Auth_OpenID_SRegResponse();
			$obj = $sreg->fromSuccessResponse($response);
			$data = $obj->contents();
			
			//var_dump($data);

			if( !isset($data['email']) || !isset($data['fullname']) ){
				$error = new WP_Error('bc_auth_email_username_empty', __("To login to this site you need to pass your name and email adress from your OpenID Profile. Please allow this in your profile and try it again."));
				echo $error->get_error_message();
				bc_oauth_show_openid_login_form();
				exit;
			}

			$user = new BC_OAuth_User();

			if( !$user->check_user($data['email']) )
				$user->bc_oauth_create_user($data['nickname'], $data['email'], false, false, false, false, $data['nickname'], $data['fullname'], $data['fullname'], array( 'bc_oauth_openid_registered' => 1 )  );
			$user->login_user();
			$tracknew = '';
			if($user->is_new_user)
				$tracknew = "bc_oauth=openid";
			$user->finish_login($tracknew);
			exit;

			
		} else {
			$_SESSION['OPENID_AUTH'] = false;
			echo "Authentication failed. Try again or register manually.";
			bc_oauth_show_openid_login_form();
			exit;
		}
	}

	function getOpenidLoginLink(){
		return site_url() . $this->openid_login_link;
	}


}
?>

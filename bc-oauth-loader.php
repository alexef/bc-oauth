<?php
/*
Plugin Name: BC oAuth
Plugin URI: http://bettercodes.org
Description: This Plugin grives Wordpress and Buddypress oAuth functionality
Author: Niklas Guenther http://bettercodes.org/members/guenther/
Version: 0.1
Author URI: http://bettercodes.org/members/guenther/
Site Wide Only: true
Network: true
Revision Date: December 06 2010
Requires at least: WP 3.0.4
Tested up to: WP 3.0.4
*/

define( 'BC_OAUTH_VERSION', '0.1' );
//define( 'BC_OAUTH_DB_VERSION', '1' );
define( 'BC_OAUTH_PLUGIN_DIR', WP_PLUGIN_DIR . '/bc-oauth' );
define( 'BC_OAUTH_PLUGIN_URL', plugins_url( $path = '/bc-oauth' ) );


/**
 * include oauth-php libraries
 * 
 */
include_once "lib/oauth-php/library/OAuthDiscovery.php";
include_once "lib/oauth-php/library/OAuthException2.php";
include_once "lib/oauth-php/library/OAuthRequest.php";
include_once "lib/oauth-php/library/OAuthRequestLogger.php";
include_once "lib/oauth-php/library/OAuthRequestSigner.php";
include_once "lib/oauth-php/library/OAuthRequestVerifier.php";
include_once "lib/oauth-php/library/OAuthRequester.php";
include_once "lib/oauth-php/library/OAuthServer.php";
include_once "lib/oauth-php/library/OAuthSession.php";
include_once "lib/oauth-php/library/OAuthStore.php";

include_once "lib/oauth-php/library/signature_method/OAuthSignatureMethod.class.php";
include_once "lib/oauth-php/library/signature_method/OAuthSignatureMethod_HMAC_SHA1.php";
include_once "lib/oauth-php/library/signature_method/OAuthSignatureMethod_MD5.php";
include_once "lib/oauth-php/library/signature_method/OAuthSignatureMethod_PLAINTEXT.php";
include_once "lib/oauth-php/library/signature_method/OAuthSignatureMethod_RSA_SHA1.php";


/**
 * Include OpenID library
 * 
 */
ini_set('include_path', BC_OAUTH_PLUGIN_DIR . '/lib/php-openid' . PATH_SEPARATOR . ini_get('include_path'));
// fixes error Define Auth_OpenID_RAND_SOURCE as null to continue with an insecure random number generator
define('Auth_OpenID_RAND_SOURCE', null);
require_once "lib/php-openid/Auth/OpenID/Consumer.php";
require_once "lib/php-openid/Auth/OpenID/FileStore.php";
require_once "lib/php-openid/Auth/OpenID/PAPE.php";
require_once "lib/php-openid/Auth/OpenID/SReg.php";

/**
 * include base classes for the plugin
 *
 */
include_once "Classes/Facebook.php";
include_once "Classes/Google.php";
include_once "Classes/Openid.php";
include_once "Classes/Twitter.php";
include_once "Classes/LinkedIn.php";
include_once "Classes/Ohloh.php";
include_once "Classes/Yahoo.php";
include_once "Classes/Live.php";
include_once "Classes/MySpace.php";
include_once "Classes/User.php";

include_once 'bc_oauth_templatetags.php';

// on ajax request the register functions from WP are not included
if( !function_exists('wp_create_user') )
	require_once( ABSPATH . WPINC . '/registration.php');

/**
 * Include JS
 */
//wp_enqueue_script( 'bc-oauth-jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js', false , false, true);
//wp_enqueue_script( 'bc-oauth-jquery-tools', 'http://cdn.jquerytools.org/1.2.5/jquery.tools.min.js', false , false, true);			
//wp_enqueue_script( 'bc-oauth-general', BC_OAUTH_PLUGIN_URL . '/js/bc-oauth-general.js', false , false, true);

	
/**
 * Add the admin settings option
 *  
 */
function bc_oauth_add_admin_menu() {
	if ( !is_super_admin() )
		return false;

	require_once dirname(__FILE__) . '/bc-oauth-admin.php';
	add_options_page('BC oAuth', 'BC oAuth Settings', 8, 'bc-oauth', 'bc_oauth_admin_settings');

}
add_action( 'admin_menu', 'bc_oauth_add_admin_menu' );

/**
 * Add css style if user is not logged in and should see oAuth
 * 
 */
function bc_oauth_add_css() {
	if( is_user_logged_in() )
		return;

	wp_enqueue_style( 'bc-oauth-css', BC_OAUTH_PLUGIN_URL . '/css/oauth.css' );
	
}
add_action('wp', 'bc_oauth_add_css');

/**
 * Show css on wp-login.php
 * 
 */
function bc_oauth_add_admin_css(){
	echo "<link rel='stylesheet' href='" . esc_url( BC_OAUTH_PLUGIN_URL . '/css/oauth.css' ) . "' type='text/css' />";
}
add_action('login_head', 'bc_oauth_add_admin_css');


function bc_oauth_init(){
	session_start();

	if( isset($_GET['service']) ){

		/**
		 * OpenID
		 */
		if( $_GET['service'] == 'openid' ){
			$openid = new BC_OAuth_Openid();

			if( isset($_POST['openid_identifier']) )
				$openid->auth($_POST['openid_identifier']);

			if( isset($_GET['finish']) )
				$openid->callback();

			bc_oauth_show_openid_login_form();
		}

		/**
		 * Twitter
		 */ 
		if( $_GET['service'] == 'twitter' ){
			$twitter = new BC_OAuth_Twitter();
			$twitter->auth();
		}

		/**
		 * Facebbok
		 */
		if( $_GET['service'] == 'facebook' ){
			$facebook = new BC_OAuth_Facebook();
			$facebook->auth();

		}

		/**
		 * LinkedIN
		 */
		if( $_GET['service'] == 'linkedin' ){
			$linkedin = new BC_OAuth_LinkedIn();
			$linkedin->auth();

		}

		/**
		 * Google
		 */
		if( $_GET['service'] == 'google' ){
			$linkedin = new BC_OAuth_Google();
			$linkedin->auth();

		}

		/**
		 * Ohloh
		 * Ohloh returns alsways url?... so it crashes the $_GET variable
		 * Workaround is:  stristr($_GET['service'], 'ohloh?')
		 */
		if( $_GET['service'] == 'ohloh' || stristr($_GET['service'], 'ohloh?') ){
			$ohloh = new BC_OAuth_Ohloh();
			$ohloh->auth();
		}
		
		/**
		 * Yahoo
		 */
		if( $_GET['service'] == 'yahoo' ){
			$yahoo = new BC_OAuth_Yahoo();
			$yahoo->auth();
		}
		
		/**
		 * Windows Live
		 */
		if( $_GET['service'] == 'live' ){
			$live = new BC_OAuth_Windows_Live();
			$live->auth();
		}
		
		/**
		 * MySpace
		 */
		if( $_GET['service'] == 'myspace' ){
			$myspace = new BC_OAuth_MySpace();
			$myspace->auth();
		}

	}

}
add_action('wp_ajax_bc_oauth', 'bc_oauth_init');
add_action('init', 'bc_oauth_init');


?>

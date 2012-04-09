=== Plugin Name ===
Contributors: 
Donate link: http://bettercodes.org/donate
Tags: oauth, social, BuddyPress, bettercodes
Requires at least: 3.1
Tested up to: 3.1
Stable tag: 0.0

This Plugin gives Wordpress, Buddypress and bettercodes - with oAuth a Single-Sign-On - login and singup functionality.

== Description ==

You can see a live demo on http://bettercodes.org/

This is a first alpha version!

*   This version is for testing only
*   Strongly not recomendent for productive invironment
   

== Installation ==

1. Upload `bc-oauth` to your `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to the confic section and activate the networks you want to add. (You need to get your own API-Keys for every network.)

== Frequently Asked Questions ==

= Do i have to singup for API-key =

Yes you need your own API-Key for every single social-network.

= How to register as an Windows Live App? =

Please go to: http://msdn.microsoft.com/en-us/library/ff751474.aspx

= Where to singup for an Yahoo Api-Key =

Go to https://developer.apps.yahoo.com/projects and add new Project.
Please notice that you need to enable the permission "Read/Write Public and Private" in the Profiles section. The e-mail adress to singup is part of this section. 

== Screenshots ==

1. The Wordpress login screnn with the activated plugin
2. Plugin with the latest version of buddypress activated
3. Plugin with the bettercodes.org Plugin activated

== Changelog ==

= 0.2 =
* Bugfixing release, Thanks to alexef and Veraxus for debugging and reporting. http://wordpress.org/support/topic/plugin-bc-oauth-bc-auth-blank-page?replies=8 
* Making the Plugin compatible to Wordpress 3.1
* Changing filename to naming convention
* Changed init action from wp_ajax to init

= 0.1 =
* First Unstable-Version of the Plugin (for testing only)

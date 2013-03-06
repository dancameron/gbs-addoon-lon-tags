<?php
/*
Plugin Name: Group Buying Addon - Local Offer Network Tags
Version: 1
Plugin URI: http://groupbuyingsite.com/marketplace
Description: Adds transactional and registration tags
Author: Sprout Venture
Author URI: http://sproutventure.com/wordpress
Plugin Author: Dan Cameron
Text Domain: group-buying
*/

// Load after all other plugins since we need to be compatible with groupbuyingsite
add_action('after_setup_theme', 'gb_load_advanced_lon_tags');
function gb_load_advanced_lon_tags() {
	if (class_exists('Group_Buying_Controller') ) {
		require_once('LONTags.class.php');
		Group_Buying_LON_Addon::init();
	}
}
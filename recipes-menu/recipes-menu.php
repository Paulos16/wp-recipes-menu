<?php
/**
 * Plugin Name: Recipes Menu
 * Plugin URI: http://receiptproject.byethost13.com/
 * Description: Extension plugin for Cooked letting you create custom menus with selected recipes.
 * Version: 0.1.0
 * Author: s16996@pjwstk.edu.pl
 * Author URI: http://users.pja.edu.pl/~s16996/index.html
 */

if (!defined('ABSPATH')) exit;

if(!function_exists('add_action')){
	echo 'You are not allowed to access this page directly.';
	exit;
}

$_tmp_plugins = get_option('active_plugins');

if(!in_array('cooked/cooked.php', $_tmp_plugins) || !in_array('cooked-addon/cooked-pro.php', $_tmp_plugins)) {
	return;
}

if(defined('RECIPES_MENU_VERSION')) {
	return;
}

define('RECIPES_MENU_FILE', __FILE__);

include_once(dirname(__FILE__).'/init.php');
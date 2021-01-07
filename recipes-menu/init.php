<?php

// We need the ABSPATH
if (!defined('ABSPATH')) exit;

define('RECIPES_MENU_VERSION', '0.1.0');
define('RECIPES_MENU_DIR', dirname(RECIPES_MENU_FILE));
define('RECIPES_MENU_SLUG', 'recipes-menu');

register_activation_hook(RECIPES_MENU_FILE, 'recipes_menu_activation');
register_deactivation_hook(RECIPES_MENU_FILE, 'recipes_menu_deactivation');

function recipes_menu_activation() {

	global $wpdb;

    init_db();
    
	add_option('recipes_menu_version', RECIPES_MENU_VERSION);
    add_option('recipes_menu_post_id', create_recipes_menu_page());
	add_option('recipes_menu_options', array());
}

function recipes_menu_deactivation() {
    delete_recipes_menu_page();
    delete_db();
    return;
}

function recipes_menu_update_check() {

    global $wpdb;
    
    $sql = array();
    $current_version = get_option('recipes_menu_version');
    $version = (int) str_replace('.', '', $current_version);

    // No update required
    if($current_version == RECIPES_MENU_VERSION){
        return true;
    }

    // Is it first run ?
    if(empty($current_version)){

        // Reinstall
        recipes_menu_activation();

        // Trick the following if conditions to not run
        $version = (int) str_replace('.', '', RECIPES_MENU_VERSION);

    }

    // Save the new Version
    update_option('recipes_menu_version', RECIPES_MENU_VERSION);

}

function init_db() {
    global $wpdb;

    $table_name = $wpdb->prefix . "user_recipes";

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int NOT NULL AUTO_INCREMENT,
        user_id bigint NOT NULL,
        post_id bigint NOT NULL,
        INDEX idx_user (user_id),
        INDEX idx_post (post_id),
        FOREIGN KEY (user_id)
            REFERENCES wp_users(ID)
            ON DELETE CASCADE,
        FOREIGN KEY (post_id)
            REFERENCES wp_posts(ID)
            ON DELETE CASCADE,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

function delete_db() {
    global $wpdb;
    $table = $wpdb->prefix . "user_recpies";
    $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
    return;
}

function create_recipes_menu_page() {
    global $current_user;

    $post = array (
        "post_content"   => '[USER_RECIPES_MENU]',
        "post_title"     => 'Jadłospis',
        "post_author"    => $current_user->ID,
        "post_status"    => 'publish',
        "post_type"      => "page"
    );

    $post_ID = wp_insert_post($post);
    
    return $post_ID;
}

function delete_recipes_menu_page() {
    $post_id = intval(get_option('recipes_menu_post_id'));
    if ($post_id > 0) {
        wp_delete_post($post_id);
    }
    return;
}

function parse_shortcode($atrs) {
    $actor = read_cookies();
    $data = get_user_recipes_menu($actor);
    $recipes = array();
    
    foreach($data as $obj) {
        $recipes[$obj->post_title] = $obj;
    }
    
    ksort($recipes);
    
    $html = '<h3>Recipes in your menu.</h3>';
    if ($recipes) {
        $html .= '<ul>';
        
        foreach ($recipes as $recipe) {
            $html .= sprintf("<li><a href='%s'>%s</a></li>", get_permalink($obj->post_ID), $title);
        }

        $html .= '</ul>';
    }
    else {
        $html .= "<p>Recipes you add to your menu will appear here.</p>";
    }

    return $html;
}

function get_user_recipes_menu($actor) {
    global $wpdb;
    
    if ($actor) {
        $table = $wpdb->prefix . 'user_recipes';
        $sql = $wpdb->prepare("
            SELECT t.post_ID, p.post_title
            FROM $table t
            INNER JOIN $wpdb->posts p
            ON t.post_ID = p.ID
            WHERE t.user = %s", $actor
        );
        $rows = $wpdb->get_results($sql);
        return $rows;
    }
    
    return null;
}

add_action('plugins_loaded', 'pagelayer_load_plugin');

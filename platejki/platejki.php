<?php
/**
 * @package Hello_Dolly
 * @version 1.6
 */
/*
Plugin Name: Platejki
Description: Plat module
Author: RVM
Version: 0.2
Author URI: http://gps.ck.ua/
*/

global $plat_db_version;
$plat_db_version = "1.0";

function register_mysettings() {
	//register our settings
/*	register_setting( 'baw-settings-group', 'new_option_name');
	register_setting( 'baw-settings-group', 'some_other_option');
	register_setting( 'baw-settings-group', 'option_etc' );
	
	update_option('new_option_name', 'yopt'); */
}

function plat_install() {
   global $wpdb;
   global $plat_db_version;
   
   register_mysettings();

   $table_name = $wpdb->prefix . "plat_data";
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  time bigint(11) DEFAULT '0' NOT NULL,
	  name tinytext NOT NULL,
	  text text NOT NULL,
	  url VARCHAR(55) NOT NULL,
	  UNIQUE KEY id (id)
	);";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);

      $rows_affected = $wpdb->insert( $table_name, array( 'time' => current_time('mysql'), 'name' => $welcome_name, 'text' => $welcome_text ) );
 
      add_option("plat_db_version", $plat_db_version);

   }
}

// action function for above hook
function plat_add_pages() {
    add_menu_page('Platejki', 'Platejki main', 8, __FILE__, 'plat_toplevel_page');

    add_submenu_page(__FILE__, 'Platejki chk', 'Platejki chk', 8, 'sub-page', 'plat_chk_page');

}

function plat_toplevel_page() {
?>
<div class="wrap">
<h2>Platejki</h2>

<form method="post" action="<?php echo __FILE__; ?>">
    <?php settings_fields( 'baw-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
        <th scope="row">Название страницы</th>
        <td><input type="text" name="new_option_name" value="<?php echo get_option('new_option_name'); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">Some Other Option</th>
        <td><input type="text" name="some_other_option" value="<?php echo get_option('some_other_option'); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Options, Etc.</th>
        <td><input type="text" name="option_etc" value="<?php echo get_option('option_etc'); ?>" /></td>
        </tr>
    </table>
    
    <p class="submit">
    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
    </p>

</form>
</div>
<?php 

$cont = print_r($_POST, true);

echo $cont;

echo "</br>" . WP_PLUGIN_URL;

echo "</br>" . plugins_url('plat.png', __FILE__);

}

function plat_mess() {
	$chosen = print_r($_GET, true);
	$chosen = "yopt";
	echo "<p id='plat_mess'>$chosen</p>";
}


function plat_chk_page() {
    echo "<h2>Test Sublevel</h2>";
}

add_action('admin_notices', 'plat_mess' );
add_action('admin_menu', 'plat_add_pages');
register_activation_hook(__FILE__,'plat_install');

?>

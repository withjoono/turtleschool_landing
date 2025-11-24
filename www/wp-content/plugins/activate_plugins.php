<?
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-includes/default-constants.php';
 
$activate_plugins = array("ninjafirewall","loginlockdown","two-factor","disable-xml-rpc-api");
$WP_PLUGIN_DIR2 = WP_PLUGIN_DIR .'/';
 
for($i = 0; $i < count($activate_plugins); $i++){
    if ( ! file_exists( $WP_PLUGIN_DIR2 .$activate_plugins[$i] .'/activated' ) ) {
        activate_plugin($activate_plugins[$i] .'/' .$activate_plugins[$i] .'.php');
        touch( $WP_PLUGIN_DIR2 .$activate_plugins[$i] .'/activated');
    }
}
?>

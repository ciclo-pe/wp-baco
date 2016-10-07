<?php
/*
* Plugin Name: Baco
* Plugin URI:  https://github.com/ciclo-pe/wp-baco
* Description: A simple backup plugin.
* Version:     0.0.0
* Author:      Estudio Cíclope
* Author URI:  http://ciclo.pe
* License:     GPL2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Domain Path: /languages
* Text Domain: baco
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


require 'includes/baco.php';


register_activation_hook( __FILE__, array( 'WP_Plugin_Baco', 'activate' ));
register_deactivation_hook( __FILE__, array( 'WP_Plugin_Baco', 'deactivate' ));


$wp_plugin_baco = new WP_Plugin_Baco( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );

if ( is_admin() ) {
  require 'admin/baco-admin.php';
  $wp_plugin_baco_admin = new WP_Plugin_Baco_Admin( $wp_plugin_baco );
}

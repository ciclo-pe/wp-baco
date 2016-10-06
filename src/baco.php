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


require 'includes/baco-db.php';
require 'includes/baco-fs.php';


class WP_Plugin_Baco {

  private $_prefix = 'baco';

  //
  // Constructor
  //
  public function __construct() {

    $this->_db = new WP_Plugin_Baco_Db( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
  }

  public static function activate() {
    if ( version_compare( PHP_VERSION, '5.4.0' ) < 0 ) {
      deactivate_plugins( basename( __FILE__ ) );
      wp_die('<p>Baco needs PHP 5.4.0 or newer and you are running ' . PHP_VERSION .'.</p>');
    }
  }

  public static function deactivate() {
    //...
  }

  //
  // Create snapshot including both files and sql dump.
  //
  public function snapshot() {
    $fname = tempnam( sys_get_temp_dir(), $this->_prefix ) . '.tar';
    $tar = new PharData( $fname );
    $path = ABSPATH . 'wp-content';
    $dir = new \RecursiveDirectoryIterator( $path, \FilesystemIterator::FOLLOW_SYMLINKS );

    $filter = new \RecursiveCallbackFilterIterator($dir, function ( $current ) use ( $path ) {
      // Skip hidden files and directories.
      if ( $current->getFilename()[0] === '.' ) {
        return false;
      }

      $rel = substr( $current->getPathname(), strlen( $path ) + 1 );

      if ( preg_match( '/^plugins\/baco\//', $rel ) > 0 ) {
        return false;
      }

      return true;
    });

    $iterator = new \RecursiveIteratorIterator( $filter );

    $tar->buildFromIterator( $iterator, $path );
    $tar->addFromString( 'dump.sql', $this->_db->dump() );
    $bz2 = $tar->compress(Phar::GZ);
    unlink( $fname );
    return $fname . '.gz';
  }

  //
  // Restore files and database from a given archived snapshot.
  //
  public function restore( $snapshot ) {
    // 1. Create unique temporary dir where to extract snapshot and do work.
    $tmpdir = WP_Plugin_Baco_Fs::tmpdir();

    // 2. Extract snapshot in tmp dir.
    $tar = new PharData( $snapshot );
    $tar->extractTo( $tmpdir );

    // 3. Import SQL
    $dumpfile = $tmpdir . DIRECTORY_SEPARATOR . 'dump.sql';
    $this->restore_db( $dumpfile );
    unlink( $dumpfile );

    // 4. Replace files
    return $this->restore_files( $tmpdir );
  }

  //
  // Restore database from SQL dump file.
  //
  public function restore_db( $dumpfile ) {
    $this->_db->restore( $dumpfile, get_option( 'siteurl' ) );
  }

  //
  // Restore `wp-content` files from given path.
  //
  public function restore_files( $source ) {
    $path = ABSPATH . 'wp-content';
    $name = array_shift( explode( DIRECTORY_SEPARATOR, plugin_basename( __FILE__ ) ) );
    $exclude = array( '/^plugins\/' . $name . '/' );
    $rollback_dir = WP_Plugin_Baco_Fs::tmpdir();

    // 1. Copy all from path to rollback excluding plugins/baco
    if ( ! WP_Plugin_Baco_Fs::cp( $path, $rollback_dir, $exclude ) ) {
      return false;
    }

    // 2. Delete all from path excluding plugins/baco
    if ( ! WP_Plugin_Baco_Fs::rimraf( $path, $exclude ) ) {
      return false;
    }

    // 3. Copy all from source to path
    return WP_Plugin_Baco_Fs::cp( $source, $path );
  }
}


register_activation_hook( __FILE__, array( 'WP_Plugin_Baco', 'activate' ));
register_deactivation_hook( __FILE__, array( 'WP_Plugin_Baco', 'deactivate' ));

$wp_plugin_baco = new WP_Plugin_Baco();

if ( is_admin() ) {
  require 'admin/baco-admin.php';
  $wp_plugin_baco_admin = new WP_Plugin_Baco_Admin( $wp_plugin_baco );
}

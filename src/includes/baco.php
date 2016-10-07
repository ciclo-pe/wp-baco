<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


require 'baco-db.php';
require 'baco-fs.php';


class WP_Plugin_Baco {

  private $_prefix = 'baco';
  private $_content_path = null;
  private $_db = null;

  /**
   * Constructor
   *
   * @param $db_host
   * @param $db_user
   * @param $db_pass
   * @param $db_name
   */
  public function __construct( $db_host, $db_user, $db_pass, $db_name ) {

    $this->_content_path = ABSPATH . 'wp-content';
    $this->_db = new WP_Plugin_Baco_Db( $db_host, $db_user, $db_pass, $db_name );
  }

  /**
   * Plugin activation hook
   *
   * @return void
   */
  public static function activate() {
    if ( version_compare( PHP_VERSION, '5.4.0' ) < 0 ) {
      deactivate_plugins( basename( __FILE__ ) );
      wp_die('<p>Baco needs PHP 5.4.0 or newer and you are running ' . PHP_VERSION .'.</p>');
    }
  }

  /**
   * Plugin deactivation hook
   *
   * @return void
   */
  public static function deactivate() {
    //...
  }

  /**
   * Dump MySQL database to SQL script.
   *
   * This method simply delegates to WP_Plugin_Baco_Db::dump().
   *
   * @param $tables string|array List of tables to include in the dump.
   *
   * @throws Exception if can not connect.
   * @return string The actual SQL dump.
   */
  public function dump( $tables = '*' ) {
    return $this->_db->dump( $tables );
  }

  /**
   * Create snapshot including both files and sql dump.
   *
   * @return string The archive path.
   */
  public function snapshot() {
    $fname = tempnam( sys_get_temp_dir(), $this->_prefix ) . '.tar';
    $tar = new PharData( $fname );
    $path = $this->_content_path;
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

  /**
   * Restore files and database from a given archived snapshot.
   *
   * @param $snapshot
   * @param $siteurl
   */
  public function restore( $snapshot, $siteurl ) {
    // 1. Create unique temporary dir where to extract snapshot and do work.
    $tmpdir = WP_Plugin_Baco_Fs::tmpdir();

    // 2. Extract snapshot in tmp dir.
    $tar = new PharData( $snapshot );
    $tar->extractTo( $tmpdir );

    // 3. Import SQL
    $dumpfile = $tmpdir . DIRECTORY_SEPARATOR . 'dump.sql';
    $this->restore_db( $dumpfile, $siteurl );
    unlink( $dumpfile );

    // 4. Replace files
    return $this->restore_files( $tmpdir );
  }

  /**
   * Restore database from SQL dump file.
   *
   * @param $dumpfile
   * @param $siteurl
   */
  public function restore_db( $dumpfile, $siteurl ) {
    return $this->_db->restore( $dumpfile, $siteurl );
  }

  /**
   * Restore `wp-content` files from given path.
   *
   * @param $source
   */
  public function restore_files( $source ) {
    $path = $this->_content_path;
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

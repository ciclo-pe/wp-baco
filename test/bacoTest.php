<?php
use PHPUnit\Framework\TestCase;


define( 'ABSPATH',  __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR );


require 'src/includes/baco.php';


class WP_Plugin_BacoTest extends TestCase {

  private function _dbLink() {
    return mysqli_connect(
      $_SERVER['DB_PORT_3306_TCP_ADDR'],
      'root',
      $_SERVER['DB_ENV_MYSQL_ROOT_PASSWORD'],
      'baco_test'
    );
  }

  public function setUp() {
    $this->_baco = new WP_Plugin_Baco(
      $_SERVER['DB_PORT_3306_TCP_ADDR'],
      'root',
      $_SERVER['DB_ENV_MYSQL_ROOT_PASSWORD'],
      'baco_test'
    );

    $link = mysqli_connect(
      $_SERVER['DB_PORT_3306_TCP_ADDR'],
      'root',
      $_SERVER['DB_ENV_MYSQL_ROOT_PASSWORD']
    );

    $sql = "CREATE DATABASE IF NOT EXISTS baco_test;";

    if ( ! mysqli_query( $link, $sql ) ) {
      var_dump( mysqli_error( $link ) );
      exit( 1 );
    }

    mysqli_close( $link );
  }

  public function test_restore_db_should_throw_when_bad_conn() {
    $dumpfile = ABSPATH . 'dump.sql';
    $siteurl = 'http://foo.com';
    $plugin = new WP_Plugin_Baco(
      $_SERVER['DB_PORT_3306_TCP_ADDR'],
      'rootsy____###',
      $_SERVER['DB_ENV_MYSQL_ROOT_PASSWORD'],
      'baco_test'
    );

    $this->expectException(Exception::class);
    $plugin->restore_db( $dumpfile, $siteurl );
  }

  //public function test_restore_db_should_throw_when_dumpfile_not_exists() {}

  public function test_restore_db_should_restore_dump() {
    $dumpfile = ABSPATH . 'dump.sql';
    $siteurl = 'http://foo.com';

    $this->_baco->restore_db( $dumpfile, $siteurl );

    $link = $this->_dbLink();
    $rs = mysqli_query( $link, "SELECT option_value FROM wp_options WHERE option_name = 'siteurl'" );
    $row = mysqli_fetch_assoc( $rs );

    $this->assertEquals( $row['option_value'], $siteurl );
  }

  public function test_dump() {
    $this->assertStringStartsWith( 'DROP TABLE IF EXISTS `wp_commentmeta`;', $this->_baco->dump() );
  }

  public function test_snapshot_should_exclude_baco_plugin_files_by_default() {
    $archive = $this->_baco->snapshot();

    $this->assertStringEndsWith( '.tar.gz', $archive );
    $this->assertFileExists( $archive );

    // Extract archive so we can examine it.
    $tmpdir = WP_Plugin_Baco_Fs::tmpdir();
    $tar = new PharData( $archive );
    $tar->extractTo( $tmpdir );

    // Assert that baco plugin files have actually been excluded
    $this->assertFalse( is_dir( $tmpdir . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'wp-baco' ) );

    // Assert that everything else has been included
    $this->assertFileExists( $tmpdir . DIRECTORY_SEPARATOR . 'advanced-cache.php' );
    $this->assertFileExists( $tmpdir . DIRECTORY_SEPARATOR . 'dump.sql' );
    $this->assertFileExists( $tmpdir . DIRECTORY_SEPARATOR . 'index.php' );
    $this->assertFileExists( $tmpdir . DIRECTORY_SEPARATOR . 'wp-cache-config.php' );
    $this->assertTrue( is_dir( $tmpdir . DIRECTORY_SEPARATOR . 'cache' ) );
    $this->assertTrue( is_dir( $tmpdir . DIRECTORY_SEPARATOR . 'languages' ) );
    $this->assertTrue( is_dir( $tmpdir . DIRECTORY_SEPARATOR . 'plugins' ) );
    $this->assertTrue( is_dir( $tmpdir . DIRECTORY_SEPARATOR . 'themes' ) );
    $this->assertTrue( is_dir( $tmpdir . DIRECTORY_SEPARATOR . 'uploads' ) );

    $this->assertTrue( is_dir( $tmpdir . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'twentysixteen' ) );
    $this->assertFileExists( $tmpdir . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'twentysixteen' . DIRECTORY_SEPARATOR . 'style.css' );
  }

  public function test_snapshot_should_handle_excludes() {
    $archive = $this->_baco->snapshot( array(
      'exclude' => array( 'cache', 'plugins/google-analytics-for-wordpress' )
    ) );

    $this->assertStringEndsWith( '.tar.gz', $archive );
    $this->assertFileExists( $archive );

    // Extract archive so we can examine it.
    $tmpdir = WP_Plugin_Baco_Fs::tmpdir();
    $tar = new PharData( $archive );
    $tar->extractTo( $tmpdir );

    // Assert that excluded files have actually been excluded
    $this->assertFalse( is_dir( $tmpdir . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'wp-baco' ) );
    $this->assertFalse( is_dir( $tmpdir . DIRECTORY_SEPARATOR . 'cache' ) );
    $this->assertFalse( is_dir( $tmpdir . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . 'google-analytics-for-wordpress' ) );

    // Assert that everything else has been included
    $this->assertFileExists( $tmpdir . DIRECTORY_SEPARATOR . 'advanced-cache.php' );
    $this->assertFileExists( $tmpdir . DIRECTORY_SEPARATOR . 'dump.sql' );
    $this->assertFileExists( $tmpdir . DIRECTORY_SEPARATOR . 'index.php' );
    $this->assertFileExists( $tmpdir . DIRECTORY_SEPARATOR . 'wp-cache-config.php' );
    $this->assertTrue( is_dir( $tmpdir . DIRECTORY_SEPARATOR . 'languages' ) );
    $this->assertTrue( is_dir( $tmpdir . DIRECTORY_SEPARATOR . 'plugins' ) );
    $this->assertTrue( is_dir( $tmpdir . DIRECTORY_SEPARATOR . 'themes' ) );
    $this->assertTrue( is_dir( $tmpdir . DIRECTORY_SEPARATOR . 'uploads' ) );

    $this->assertTrue( is_dir( $tmpdir . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'twentysixteen' ) );
    $this->assertFileExists( $tmpdir . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'twentysixteen' . DIRECTORY_SEPARATOR . 'style.css' );
  }

  //public function test_restore_files() {
    //var_dump( $this->_baco );
  //}
}

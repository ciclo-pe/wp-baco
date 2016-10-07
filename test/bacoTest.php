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

  //public function test_snapshot() {
    //var_dump( $this->_baco );
  //}

  //public function test_restore_files() {
    //var_dump( $this->_baco );
  //}
}

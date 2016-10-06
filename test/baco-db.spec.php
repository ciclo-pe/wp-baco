<?php
use PHPUnit\Framework\TestCase;


define( 'ABSPATH', substr( __DIR__, 0, strrpos( __DIR__, DIRECTORY_SEPARATOR ) ) );


require 'src/includes/baco-db.php';


class WP_Plugin_Baco_DbTest extends TestCase {

  public function setUp() {
    print_r('setting up...');

    $this->db_name = 'baco_test';

    $this->db = new WP_Plugin_Baco_Db(
      $_SERVER['DB_PORT_3306_TCP_ADDR'],
      'root',
      $_SERVER['DB_ENV_MYSQL_ROOT_PASSWORD'],
      'baco_test',
      $_SERVER['DB_PORT_3306_TCP_PORT']
    );
  }

  public function test_foo() {

    var_dump($this->db->restore());
  }

  public function test_bar() {

    print_r('bar');
  }

  public function tearDown() {
    print_r('tearing down...');
  }

}

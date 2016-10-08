<?php
use PHPUnit\Framework\TestCase;


require 'src/includes/baco-fs.php';


class WP_Baco_FsTest extends TestCase {

  public function test_static_methods() {
    $this->assertEquals( method_exists( 'WP_Baco_Fs', 'tmpdir' ), true );
    $this->assertEquals( method_exists( 'WP_Baco_Fs', 'cp' ), true );
    $this->assertEquals( method_exists( 'WP_Baco_Fs', 'rimraf' ), true );
  }


  public function test_tmpdir() {
    $tmpdir = WP_Baco_Fs::tmpdir();

    $this->assertTrue( is_string( $tmpdir ) );
    $this->assertTrue( is_dir( $tmpdir ) );
    $this->assertTrue( is_writable( $tmpdir ) );

    $this->assertTrue( rmdir( $tmpdir ) );
  }

  /*
  public function test_cp() {
    $src = WP_Baco_Fs::tmpdir();
    $dest = WP_Baco_Fs::tmpdir();

    var_dump($src, $dest);

    $this->assertTrue( rmdir( $src ) );
    $this->assertTrue( rmdir( $dest ) );
  }
  */

  //public function test_rimraf() {
  //  //...
  //}

}

<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


class WP_Plugin_Baco_Db {

  private $_host;
  private $_user;
  private $_pass;
  private $_name;
  private $_port;

  public function __construct( $host, $user, $pass, $name, $port=3306 ) {
    $this->_host = $host;
    $this->_user = $user;
    $this->_pass = $pass;
    $this->_name = $name;
    $this->_port = $port;
  }

  /**
   * Connect to MySQL database.
   *
   * @throws Exception if can not connect.
   * @return resource Link representing MySQL connection.
   */
  private function _connect() {
    $link = @mysqli_connect(
      $this->_host,
      $this->_user,
      $this->_pass,
      $this->_name,
      $this->_port
    );

    if ( ! $link ) {
      throw new Exception( mysqli_connect_error() );
    }

    return $link;
  }

  /**
   * Dump MySQL tables.
   *
   * @param $tables string|array List of tables to include in the dump.
   *
   * @throws Exception if can not connect.
   * @return string The actual SQL dump.
   */
  public function dump( $tables = '*' ) {
    $link = $this->_connect();

    if ( $tables == '*' ) {
      $tables = array();
      $result = mysqli_query( $link, 'SHOW TABLES' );
      while ( $row = mysqli_fetch_row( $result ) ) {
        $tables[] = $row[0];
      }
    }
    else {
      $tables = is_array( $tables ) ? $tables : explode( ',', $tables );
    }

    foreach ( $tables as $table ) {
      $result = mysqli_query( $link, 'SELECT * FROM ' . $table );
      $num_fields = mysqli_num_fields( $result );

      $return .= 'DROP TABLE IF EXISTS `' . $table . '`;';
      $row2 = mysqli_fetch_row( mysqli_query( $link, 'SHOW CREATE TABLE ' . $table ) );
      $return .= "\n\n" . $row2[1] . ";\n\n";

      for ( $i = 0; $i < $num_fields; $i++ ) {
        while ( $row = mysqli_fetch_row( $result ) ) {
          $return .= 'INSERT INTO `' . $table . '` VALUES(';
          for ( $j = 0; $j < $num_fields; $j++ ) {
            $row[$j] = addslashes( $row[$j] );
            $row[$j] = ereg_replace( "\n", "\\n", $row[$j] );
            if ( isset( $row[$j] ) ) {
              $return .= '"' . $row[$j] . '"';
            }
            else {
              $return .= '""';
            }
            if ( $j < ( $num_fields - 1 ) ) {
              $return .= ',';
            }
          }
          $return .= ");\n";
        }
      }
      $return .= "\n\n\n";
    }

    mysqli_close( $link );

    return $return;
  }

  /**
   * Restore SQL dump.
   *
   * @param $dumpfile Path to file containing the SQL dump.
   * @param $siteurl WordPress site URL.
   *
   * @throws Exception if can not connect.
   * @return void
   */
  public function restore( $dumpfile, $siteurl ) {
    $link = $this->_connect();

    // TODO: Throw if dumpfile doesn't exist or not readable!

    $sql = file_get_contents( $dumpfile );

    if ( ! mysqli_multi_query( $link, $sql ) ) {
      mysqli_close( $link );
      throw new Exception( 'Error importing SQL dump. ' . mysqli_error( $link ) );
    }

    // TODO: if statement above only checks for mysql error un first statement.
    // Have to check for errors after iterating results?
    // See: http://php.net/manual/en/mysqli.multi-query.php#106126

    //if ( $mysql_error = mysqli_error( $link ) ) {
    //  echo $mysql_error;
    //  return false;
    //}

    // go through results and free them up, otherwise we can not execute new
    // queries...
    do {
      if ( $rs = mysqli_store_result( $link ) ) {
        // do something with result?
        mysqli_free_result( $rs );
      }
    } while ( mysqli_next_result( $link ) );

    mysqli_close( $link );

    return $this->_update_siteurl( $siteurl );
  }


  private function _update_siteurl( $siteurl ) {
    $link = $this->_connect();

    // Get siteurl after db import so we now what to replace when replacing urls.
    $sql = "SELECT option_value FROM wp_options WHERE option_name = 'siteurl'";
    if ( ! $rs = mysqli_query( $link, $sql ) ) {
      echo 'Error getting new siteurl option';
      var_dump( mysqli_error( $link ) );
      return false;
    }

    $obj = $rs->fetch_object();
    $search = $obj->option_value;

    mysqli_free_result( $rs );

    if ( $search === $siteurl ) {
      return true;
    }

    $sql_tmpl = "UPDATE `%s` SET `%s` = REPLACE(`%s`, '%s', '%s')";

    # Update urls in options...
    $sql = sprintf( $sql_tmpl, 'wp_options', 'option_value', 'option_value', $search, $siteurl );
    $sql .= " WHERE `option_name` IN ('siteurl', 'home')";
    if ( ! mysqli_query( $link, $sql ) ) {
      echo 'Error updating siteurl and home options';
      var_dump( mysqli_error( $link ) );
      return false;
    }

    // Update urls in content
    $cols = array( 'post_content', 'post_excerpt', 'guid' );
    foreach ( $cols as $col ) {
      $sql = sprintf( $sql_tmpl, 'wp_posts', $col, $col, $search, $siteurl );
      if ( ! mysqli_query( $link, $sql ) ) {
        echo 'Error updating siteurl in content';
        var_dump( mysqli_error( $link ) );
        return false;
      }
    }

    mysqli_close( $link );
    return true;
  }

}

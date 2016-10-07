<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


foreach ( get_option( 'cron' ) as $key => $value ) {
  if ($key == 'version') {
    echo '<p>' . $key . ': ' . $value . '</p>';
  }
  else {
    echo '<h2>' . date( 'F j Y g:ia', $key) . '</h2>';

    foreach ( $value as $name => $obj ) {
      echo '<h3>' . $name . '</h3>';

      foreach ( $obj as $v ) {
        echo 'schedule: ' . $v['schedule'] . ' (' . ( $v['interval'] / 60 / 60 ) . 'h' . ')<br />';
        echo 'args: ';
        print_r( $v['args'] );
        echo '<br />';
      }
    }
  }
}
?>

<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>

<table class="wp-list-table widefat fixed striped">
  <tr>
    <th>upload_max_filesize</th>
    <td><?php echo ini_get('upload_max_filesize'); ?></td>
  </tr>
  <tr>
    <th>post_max_size</th>
    <td><?php echo ini_get('post_max_size'); ?></td>
  </tr>
  <tr>
    <th>memory_limit</th>
    <td><?php echo ini_get('memory_limit'); ?></td>
  </tr>
  <tr>
    <th>max_execution_time</th>
    <td><?php echo ini_get('max_execution_time'); ?></td>
  </tr>
  <tr>
    <th>max_input_time</th>
    <td><?php echo ini_get('max_input_time'); ?></td>
  </tr>
</table>

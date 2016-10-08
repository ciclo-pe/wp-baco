<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

function bytes_to_human( $bytes ) {
  return number_format( $bytes / 1024 / 1024, 2 ) . 'Mb';
}
?>

<!--pre>
<?php var_dump( $doctor['notifications'] ); ?>
</pre-->

<?php if ( sizeof( $doctor['notifications'] ) > 0 ) : ?>
  <h2>Notifications</h2>
  <?php foreach ( $doctor['notifications'] as $notification ) : ?>
    <div class="notification notice <?php echo $notification['type']; ?>">
      <p class="dashicons-before dashicons-warning">
        <?php echo $notification['message']; ?>
      </p>
      <p><?php echo sizeof( $notification['files'] ); ?> offending files</p>
      <ul>
      <?php
      $max = sizeof( $notification['files'] );
      if ( $max > 10 ) {
        $max = 10;
      }
      for ( $i = 0; $i < $max; $i++ ) {
        echo '<li>' . $notification['files'][$i] . '</li>';
      }
      if ( $max != sizeof( $notification['files'] ) ) {
        echo '<li>...</li>';
      }
      ?>
      </ul>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<h2>PHP [<?php echo $doctor['php']['version']; ?>]</h2>

<table class="wp-list-table widefat fixed striped">
  <?php foreach ( $doctor['php']['config'] as $k => $v ) : ?>
  <tr>
    <th><?php echo $k; ?></th>
    <td><?php echo $v; ?></td>
  </tr>
  <?php endforeach; ?>
</table>

<h2>Files [wp-content]</h2>

<div class="postbox">
  <div class="inside">
    <ul>
      <li>
        <?php echo $doctor['files']['count']; ?> files
      </li>
      <li>
        <?php echo bytes_to_human( $doctor['files']['total_size'] ); ?>
      </li>
    </ul>
  </div>
</div>

<h2>MySQL [<?php echo $doctor['mysql']['version']; ?>]</h2>

<div class="postbox">
  <div class="inside">
    <ul>
      <li>
        <?php echo sizeof( $doctor['mysql']['stats']['tables'] ); ?> tables
      </li>
      <li>
        <?php echo bytes_to_human( $doctor['mysql']['stats']['total_size'] ); ?>
      </li>
    </ul>
  </div>
</div>

<table class="wp-list-table widefat fixed striped">
  <tr>
    <th>Name</th>
    <th>Rows</th>
    <th>Data</th>
    <th>Index</th>
    <th>Total</th>
  </tr>
  <?php foreach ( $doctor['mysql']['stats']['tables'] as $table ) : ?>
  <tr>
    <th>
      <?php echo $table['Name']; ?><br />
      <span style="font-size:0.7em;">
        <?php echo $table['Engine']; ?> /
        <?php echo $table['Collation']; ?>
      </span>
    </th>
    <td><?php echo $table['Rows']; ?></td>
    <td><?php echo bytes_to_human( $table['Data_length'] ); ?></td>
    <td><?php echo bytes_to_human( $table['Index_length'] ); ?></td>
    <td><?php echo bytes_to_human( $table['Data_length'] + $table['Index_length'] ); ?></td>
  </tr>
  <?php endforeach; ?>
</table>

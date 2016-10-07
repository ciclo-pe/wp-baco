<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' ); ?>

<form method="post" action="options.php">
  <?php @settings_fields( 'wp-baco' ); ?>
  <?php @do_settings_sections( 'baco-admin' ); ?>
  <?php @submit_button(); ?>
</form>

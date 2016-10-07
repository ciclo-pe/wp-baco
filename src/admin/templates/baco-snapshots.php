<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>

<form id="baco-snapshots-form" method="POST" action="<?php echo admin_url( 'admin.php' ); ?>">

  <input type="hidden" name="action" value="" />
  <input type="hidden" name="id" value="" />

  <p>
    <button type="button" class="button button-primary button-hero dashicons-before dashicons-download" data-action="create">
      Create
    </button>
    <button type="button" class="button button-secondary button-hero dashicons-before dashicons-upload" data-action="restore">
      Restore
    </button>
  </p>

</form>

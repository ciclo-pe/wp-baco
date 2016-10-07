<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>

<form id="baco-snapshots-form" method="POST" action="<?php echo admin_url( 'admin.php' ); ?>">

  <input type="hidden" name="action" value="" />
  <input type="hidden" name="id" value="" />

  <p>
    <button type="button" class="button-primary dashicons-before dashicons-download" data-action="create_download">
      Create and download snapshot
    </button>
    <button type="button" class="button-primary dashicons-before dashicons-upload" data-action="restore_upload">
      Upload and restore
    </button>
  </p>

</form>


<pre>
upload_max_filesize: <?php echo ini_get('upload_max_filesize'); ?>

post_max_size: <?php echo ini_get('post_max_size'); ?>

memory_limit: <?php echo ini_get('memory_limit'); ?>
</pre>

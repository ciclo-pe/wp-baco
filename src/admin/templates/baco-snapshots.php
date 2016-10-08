<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
?>

<form id="baco-snapshots-form" method="POST" action="<?php echo admin_url( 'admin.php' ); ?>">

  <input type="hidden" name="action" value="" />
  <input type="hidden" name="id" value="" />

  <h2>Create snapshot</h2>
  <p>
    Create a compressed tar archive containing a database dump as well
    as all files in the <code>wp-content</code> directory, except those
    explicitly excluded in the <a href="?page=baco-admin&tab=options">plugin options</a>.
  </p>
  <p>
    <button type="button" class="button button-primary button-hero dashicons-before dashicons-clock" data-action="create">
      Create
    </button>
  </p>

  <h2>Restore snapshot</h2>
  <p>
    Upload and restore a compressed snapshot archive. Here you can upload and
    restore backups created using this plugin. Both database and files will be
    overritten, and could potentially go wrong, so handle with extra care.
  </p>
  <p>
    <button type="button" class="button button-secondary button-hero dashicons-before dashicons-backup" data-action="restore">
      Restore
    </button>
  </p>

</form>

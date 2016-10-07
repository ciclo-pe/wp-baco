<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


class WP_Plugin_Baco_Admin {

  private $_plugin = null;

  public function __construct( WP_Plugin_Baco $wp_plugin_baco ) {
    $this->_plugin = $wp_plugin_baco;

    add_action( 'admin_menu', array( $this, 'add_menu' ) );
    add_action( 'admin_init', array( $this, 'admin_init' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    add_action( 'admin_action_baco_create_download', array( $this, 'create_download' ) );
    add_action( 'admin_action_baco_restore_upload', array( $this, 'restore_upload' ) );
  }

  public function add_menu() {
    add_management_page(
      'Baco Admin',
      'Baco',
      'manage_options',
      'baco-admin',
      array( $this, 'admin_page' )
    );
  }

  public function admin_init() {
    register_setting('baco-group', 'baco_setting_aws_key');
    register_setting('baco-group', 'baco_setting_aws_secret');
    register_setting('baco-group', 'baco_setting_aws_bucket');
    register_setting('baco-group', 'baco_setting_aws_region');
  }

  public function enqueue_scripts( $hook ) {
    if ( 'tools_page_baco-admin' != $hook ) { return; }
    wp_enqueue_script( 'baco_admin', plugin_dir_url( __FILE__ ) . 'js/baco-admin.js' );
    wp_enqueue_style( 'baco_admin', plugin_dir_url( __FILE__ ) . 'css/baco-admin.css' );
  }

  public function admin_page() {
    if (!current_user_can( 'manage_options' )) {
      wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    }

    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'snapshots';
    ?>
    <div class="wrap">
      <h1>Baco</h1>
      <?php settings_errors(); ?>
      <h2 class="nav-tab-wrapper">
        <a href="?page=baco-admin&tab=snapshots"
          class="nav-tab <?php echo $active_tab == 'snapshots' ? 'nav-tab-active' : ''; ?>"
        ><span class="dashicons dashicons-backup"></span> Snapshots</a>
        <!--a href="?page=baco-admin&tab=options"
          class="nav-tab <?php echo $active_tab == 'options' ? 'nav-tab-active' : ''; ?>"
        ><span class="dashicons dashicons-admin-generic"></span>Options</a>
        <a href="?page=baco-admin&tab=cron"
          class="nav-tab <?php echo $active_tab == 'cron' ? 'nav-tab-active' : ''; ?>"
        ><span class="dashicons dashicons-hammer"></span>Cron</a-->
      </h2>
      <?php
      if ( $active_tab == 'snapshots' ) {
        include 'templates/baco-backup-list.php';
      }
      else if ( $active_tab == 'options' ) {
        include 'templates/baco-options.php';
      }
      else {
        include 'templates/baco-cron.php';
      }
      ?>
    </div>
    <?php
  }

  public function create_download() {
    $fname = $this->_plugin->snapshot();

    header( 'Content-type: application/x-gzip' );
    header( 'Content-Disposition: attachment; filename="snapshot-' . time() . '.tar.gz"' );
    readfile( $fname );
  }

  public function restore_upload() {
    $archive = $_FILES['archive'];

    if ( $archive['error'] ) {
      echo json_encode( array(
        'ok' => false,
        'archive' => $archive,
        'error' => 'Error receiving file'
      ) );
      return;
    }
    else if ( $archive['type'] != 'application/x-gzip' ) {
      echo json_encode( array(
        'ok' => false,
        'archive' => $archive,
        'error' => 'File was expected to be a gzip archive'
      ) );
      return;
    }

    $tmpdir = WP_Plugin_Baco_Fs::tmpdir();
    $path = $tmpdir . DIRECTORY_SEPARATOR . $archive['name'];

    if ( ! rename( $archive['tmp_name'], $path ) ) {
      echo json_encode( array(
        'ok' => false,
        'archive' => $archive,
        'error' => 'Could not move temporary file'
      ) );
      return;
    }

    if ( ! $this->_plugin->restore( $path, get_option( 'siteurl' ) ) ) {
      echo json_encode( array(
        'ok' => false,
        'archive' => $archive,
        'error' => 'Could not restore backup archive'
      ) );
      return;
    }

    echo json_encode( array(
      'ok' => true,
      'archive' => $archive
    ) );
  }

}

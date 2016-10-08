<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


class WP_Baco_Admin {

  private $_plugin = null;

  public function __construct( WP_Baco $wp_plugin_baco ) {
    $this->_plugin = $wp_plugin_baco;

    add_action( 'admin_menu', array( $this, 'add_menu' ) );
    add_action( 'admin_init', array( $this, 'admin_init' ) );
    add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    add_action( 'admin_action_baco_create', array( $this, 'create' ) );
    add_action( 'admin_action_baco_restore', array( $this, 'restore' ) );
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
    register_setting('wp-baco', 'baco_exclude');

    add_settings_section(
      'baco_settings_section_default',       // id
      'Snapshot Options',                       // title
      null, //array( $this, 'settings_section_cb' ), // callback
      'baco-admin'                           // page
    );

    add_settings_field(
      'baco_settings_field_exclude',               // id
      'Exclude',                                   // title
      array( $this, 'settings_field_exclude_cb' ), // callback
      'baco-admin',                                // page
      'baco_settings_section_default',             // section
      array(                                       // args
        //'label_for' => '',
        //'class' => ''
      )
    );
  }

  //public function settings_section_cb() {
  //  echo '<p>Baco Section....</p>';
  //}

  public function settings_field_exclude_cb() {
    $setting = get_option( 'baco_exclude' );
    ?>
    <textarea rows="5" name="baco_exclude" id="baco_exclude"><?= isset( $setting ) ? esc_attr( $setting ) : ''; ?></textarea>
    <?php
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

    $options = array();

    $exclude = trim( get_option( 'baco_exclude', '' ) );

    if ( $exclude ) {
      $options['exclude'] = explode( "\n", $exclude );
    }

    $doctor = $this->_plugin->doctor( $options );

    $active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'snapshots';
    ?>
    <script>window.WP_Baco = { doctor: <?php echo json_encode( $doctor ); ?> }</script>
    <div class="wrap">
      <h1>Baco</h1>
      <?php settings_errors(); ?>
      <h2 class="nav-tab-wrapper">
        <a href="?page=baco-admin&tab=snapshots"
          class="nav-tab <?php echo $active_tab == 'snapshots' ? 'nav-tab-active' : ''; ?>"
        ><span class="dashicons dashicons-backup"></span> Snapshots</a>
        <a href="?page=baco-admin&tab=options"
          class="nav-tab <?php echo $active_tab == 'options' ? 'nav-tab-active' : ''; ?>"
        ><span class="dashicons dashicons-admin-generic"></span>Options</a>
        <a href="?page=baco-admin&tab=doctor"
          class="nav-tab <?php echo $active_tab == 'doctor' ? 'nav-tab-active' : ''; ?>"
        ><span class="dashicons dashicons-sos"></span>Doctor</a>
      </h2>
      <?php
      if ( $active_tab == 'snapshots' ) {
        include 'templates/baco-snapshots.php';
      }
      else if ( $active_tab == 'options' ) {
        include 'templates/baco-options.php';
      }
      else {
        include 'templates/baco-doctor.php';
      }
      ?>
    </div>
    <?php
  }

  public function create() {
    $options = array();

    $exclude = trim( get_option( 'baco_exclude', '' ) );

    if ( $exclude ) {
      $options['exclude'] = explode( "\n", $exclude );
    }

    $fname = $this->_plugin->snapshot( $options );

    header( 'Content-type: application/x-gzip' );
    header( 'Content-Disposition: attachment; filename="snapshot-' . time() . '.tar.gz"' );
    readfile( $fname );
  }

  public function restore() {
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

    $tmpdir = WP_Baco_Fs::tmpdir();
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

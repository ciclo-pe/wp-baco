<?php
if ( ! class_exists( 'WP_Plugin_Baco_Fs' ) ) {

  class WP_Plugin_Baco_Fs {

    //
    // Create unique temporary dir
    //
    public static function tmpdir() {
      $tmpdir = tempnam( sys_get_temp_dir(), 'baco' );

      // `tempnam()` creates a file with the "unique" name, so before we create
      // a directory with that name we need to delete the empty file.
      if ( file_exists( $tmpdir ) ) {
        unlink( $tmpdir );
      }

      mkdir( $tmpdir );

      if ( ! is_dir( $tmpdir ) ) {
        return false;
      }

      return $tmpdir;
    }

    //
    // Copy recursively the contents of `src` into `dest`.
    //
    // TODO: Handle symbolic links
    //
    public static function cp( $src, $dest, array $exclude=array(), $base=null ) {
      $src = rtrim( $src );
      $dest = rtrim( $dest );
      $rel = ( $base ) ? substr( $src, strlen( $base ) + 1 ) : '';

      if ( ! $base ) {
        $base = $src;
      }

      $ds = DIRECTORY_SEPARATOR;

      foreach ( $exclude as $pattern ) {
        if ( preg_match( $pattern, $rel ) ) {
          return true;
        }
      }

      if ( is_file( $src ) ) {
        return copy( $src, $dest );
      }
      else if ( is_dir( $src ) ) {
        if ( file_exists( $dest ) && ! is_dir( $dest ) ) {
          print_r( $dest . ' exists and is not a dir!' );
          return false;
        }
        else if ( !file_exists( $dest ) && ! mkdir( $dest ) ) {
          print_r( 'Could not create directory ' . $dest );
          return false;
        }
        $files = array_diff( scandir( $src ), array( '.', '..' ) );
        foreach ( $files as $file ) {
          if ( ! WP_Plugin_Baco_Fs::cp( $src . $ds . $file, $dest . $ds . $file, $exclude, $base ) ) {
            return false;
          }
        }
        return true;
      }
      else if ( is_link( $src )) {
        print_r( 'Link!!!' );
      }

      return false;
    }

    //
    // Remove files in dir recursively.
    //
    public static function rimraf( $path, array $exclude=array(), $base='' ) {
      $path = rtrim( $path );
      $rel = ( $base ) ? substr( $path, strlen( $base ) + 1 ) : '';

      if ( ! $base ) {
        $base = $path;
      }

      $ds = DIRECTORY_SEPARATOR;

      foreach ( $exclude as $pattern ) {
        if ( preg_match( $pattern, $rel ) ) {
          return 'has_excluded';
        }
      }

      if ( is_file( $path ) || is_link( $path ) ) {
        return unlink( $path );
      }
      else if ( is_dir( $path ) ) {
        $files = array_diff( scandir( $path ), array( '.', '..' ) );
        $has_excluded = false;
        foreach ( $files as $file ) {
          $res = WP_Plugin_Baco_Fs::rimraf( $path . $ds . $file, $exclude, $base );
          if ( ! $res ) {
            return false;
          }
          else if ( $res == 'has_excluded' ) {
            $has_excluded = true;
          }
        }

        if ( ! $has_excluded ) {
          return rmdir( $path );
        }

        return true;
      }

      return false;
    }
  }

}

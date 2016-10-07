<?php defined( 'ABSPATH' ) or die( 'No script kiddies please!' ); ?>

<form method="post" action="options.php">
  <?php @settings_fields('baco-group'); ?>
  <?php @do_settings_fields('baco-group'); ?>

  <table class="form-table">
    <tr valign="top">
      <th scope="row"><label for="baco_setting_aws_key">AWS Access Key</label></th>
      <td>
        <input type="text" class="regular-text" name="baco_setting_aws_key"
          id="baco_setting_aws_key" value="<?php echo $key; ?>" />
      </td>
    </tr>
    <tr valign="top">
      <th scope="row"><label for="baco_setting_aws_secret">AWS Secret</label></th>
      <td>
        <input type="text" class="regular-text" name="baco_setting_aws_secret"
          id="baco_setting_aws_secret" value="<?php echo $secret; ?>" />
      </td>
    </tr>
    <tr valign="top">
      <th scope="row"><label for="baco_setting_aws_bucket">AWS Bucket</label></th>
      <td>
        <input type="text" class="regular-text" name="baco_setting_aws_bucket"
          id="baco_setting_aws_bucket" value="<?php echo $bucket; ?>" />
      </td>
    </tr>
    <tr valign="top">
      <th scope="row"><label for="baco_setting_aws_region">AWS Region</label></th>
      <td>
        <input type="text" class="regular-text" name="baco_setting_aws_region"
          id="baco_setting_aws_region" value="<?php echo $region; ?>" />
      </td>
    </tr>
  </table>
  <?php @submit_button(); ?>
</form>

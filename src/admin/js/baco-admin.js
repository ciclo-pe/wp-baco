jQuery(function ($) {

  'use strict';

  var $form = $('#baco-snapshots-form');

  var phpSizeToBytes = function (str) {

    var matches = /^(\d+)(.)$/.exec(str);

    if (!matches || matches.length < 3) {
      return parseInt(str, 10);
    }

    if (matches[2] === 'M') {
      return parseInt(matches[1], 10) * 1024 * 1024;
    }
    else if (matches[2] === 'K') {
      return parseInt(matches[1], 10) * 1024;
    }
    else {
      return parseInt(matches[1], 10);
    }
  };

  var checkUploadFile = function (file) {

    var nameParts = file.name.split('.');

    if (file.type !== 'application/x-gzip' || nameParts.length < 3 || nameParts.pop() !== 'gz' || nameParts.pop() !== 'tar') {
      alert('File must be a gzip compressed tar archive');
      return false;
    }

    var sizeLimits = ['upload_max_filesize', 'post_max_size', 'memory_limit'];
    for (var i = 0; i < sizeLimits.length; i++) {
      if (file.size > phpSizeToBytes(WP_Baco.doctor.php.config[sizeLimits[i]])) {
        alert('File exceeds PHP\'s ' + sizeLimits[i]);
        return false;
      }
    }

    return true;
  };

  var post = function (progress, cb) {

    if (arguments.length === 1) {
      cb = progress;
      progress = function () {};
    }

    var formData = new FormData(document.getElementById('baco-snapshots-form'));

    $.ajax({
      xhr: function () {

        var xhr = new XMLHttpRequest();

        xhr.upload.addEventListener('progress', function (e) {

          if (e.lengthComputable) {
            var percentComplete = e.loaded / e.total;
            progress(percentComplete);
          }
        }, false);

        xhr.addEventListener('progress', function (e) {

          if (e.lengthComputable) {
            var percentComplete = e.loaded / e.total;
            progress(percentComplete);
          }
        }, false);

        return xhr;
      },
      type: 'POST',
      url: $form.attr('action'),
      dataType: 'json',
      data: formData,
      processData: false,
      contentType: false,
      cache: false,
      error: function (xhr, textStatus, errorThrown) {

        cb(textStatus);
      },
      success: function (resp) {

        if (!resp) {
          return cb('Error sending request');
        }
        else if (resp.error) {
          return cb(resp.error);
        }

        cb(null, resp);
      }
    });
  };

  $form.find('button').click(function (e) {

    var $btn = $(e.target);
    var action = $btn.data('action');
    var id = $btn.data('id');

    $form.find('[name="action"]').val('baco_' + action);
    $form.find('[name="id"]').val(id);

    var originalBtnText = $btn.text();
    var resetUI = function (err, resp) {

      if (err) {
        alert('ERROR: ' + err);
      }
      else if (resp) {
        alert(resp);
      }

      $btn.text(originalBtnText);
      $form.find('button').removeAttr('disabled');
    };

    if (action === 'create') {
      resetUI();
      return $form.submit();
    }

    var $input = $('<input type="file" name="archive" style="display:none;">');

    $input.on('change', function (e) {

      // check file is tar.gz file!
      if (!checkUploadFile(this.files[0])) {
        return false;
      }

      $btn.text('Uploading...');
      var $progress = $('<span>');
      $btn.append($progress);

      $form.find('button').attr('disabled', true);

      post(function (percentComplete) {

        if (percentComplete >= 1) {
          $btn.html('Restoring...');
        }
        else {
          $progress.text(' (' + (percentComplete * 100).toFixed(0) + '%)');
        }
      }, function (err) {

        $progress.remove();

        if (err) {
          return resetUI(err);
        }
        resetUI(null, 'Backup restored successfully');
        window.location.reload();
      });
    });

    $form.attr('enctype', 'multipart/form-data').append($input);
    $input.click();

    return false;
  });

});

jQuery(document).ready(function ($) {
  const storage_settings = window.storage_settings;
  const escape = window.SHAREDFUNCTIONS.escapeHTML;

  $(document).on('click', '.dt-storage-upload', function (e) {
    const element = $(e.target);
    display_storage_upload_modal(
      $(element).data('storage_upload_post_type'),
      $(element).data('storage_upload_post_id'),
      $(element).data('storage_upload_meta_key'),
      $(element).data('storage_upload_key_prefix'),
    );
  });

  /**
   * Utility Functions
   */

  function display_storage_upload_modal(
    post_type,
    post_id,
    meta_key,
    key_prefix,
  ) {
    const modal_html = `
      <div class="reveal medium" id="dt_storage_upload_modal" data-reveal data-reset-on-close>
        <style>
          #dt_storage_upload_modal
          {
              text-align: center;
          }

          .box
          {
              font-size: 1.25rem; /* 20 */
              background-color: #c8dadf;
              position: relative;
              padding: 100px 20px;
          }
          .box.has-advanced-upload
          {
              outline: 2px dashed #92b0b3;
              outline-offset: -10px;

              -webkit-transition: outline-offset .15s ease-in-out, background-color .15s linear;
              transition: outline-offset .15s ease-in-out, background-color .15s linear;
          }
          .box.is-dragover
          {
              outline-offset: -20px;
              outline-color: #c8dadf;
              background-color: #fff;
          }
          .box__dragndrop,
          .box__icon
          {
              display: none;
          }
          .box.has-advanced-upload .box__dragndrop
          {
              display: inline;
          }
          .box.has-advanced-upload .box__icon
          {
              width: 100%;
              height: 80px;
              fill: #92b0b3;
              display: block;
              margin-bottom: 40px;
          }

          .box.is-uploading .box__input,
          .box.is-success .box__input,
          .box.is-error .box__input
          {
              visibility: hidden;
          }

          .box__uploading,
          .box__success,
          .box__error
          {
              display: none;
          }
          .box.is-uploading .box__uploading,
          .box.is-success .box__success,
          .box.is-error .box__error
          {
              display: block;
              position: absolute;
              top: 50%;
              right: 0;
              left: 0;

              -webkit-transform: translateY( -50% );
              transform: translateY( -50% );
          }
          .box__uploading
          {
              font-style: italic;
          }
          .box__success
          {
              -webkit-animation: appear-from-inside .25s ease-in-out;
              animation: appear-from-inside .25s ease-in-out;
          }
          @-webkit-keyframes appear-from-inside
          {
              from { -webkit-transform: translateY( -50% ) scale( 0 ); }
              75% { -webkit-transform: translateY( -50% ) scale( 1.1 ); }
              to { -webkit-transform: translateY( -50% ) scale( 1 ); }
          }
          @keyframes appear-from-inside
          {
              from { transform: translateY( -50% ) scale( 0 ); }
              75% { transform: translateY( -50% ) scale( 1.1 ); }
              to { transform: translateY( -50% ) scale( 1 ); }
          }

          .box__restart
          {
              font-weight: 700;
          }
          .box__restart:focus,
          .box__restart:hover
          {
              color: #39bfd3;
          }

          .js .box__file
          {
              width: 0.1px;
              height: 0.1px;
              opacity: 0;
              overflow: hidden;
              position: absolute;
              z-index: -1;
          }
          .js .box__file + label
          {
              max-width: 80%;
              text-overflow: ellipsis;
              white-space: nowrap;
              cursor: pointer;
              display: inline-block;
              overflow: hidden;
          }
          .js .box__file + label:hover strong,
          .box__file:focus + label strong,
          .box__file.has-focus + label strong
          {
              color: #39bfd3;
          }
          .js .box__file:focus + label,
          .js .box__file.has-focus + label
          {
              outline: 1px dotted #000;
              outline: -webkit-focus-ring-color auto 5px;
          }
          .js .box__file + label *
          {
              /* pointer-events: none; */ /* in case of FastClick lib use */
          }

          .no-js .box__file + label
          {
              display: none;
          }

          .box__button_upload
          {
              display: none;
          }
        </style>
        <h3>${escape(storage_settings?.translations?.modals?.upload?.title)}</h3>

        <form class="box" method="POST" action="${storage_settings?.rest_url + 'dt-posts/v2/' + post_type + '/' + post_id + '/storage_upload'}" enctype="multipart/form-data">
            <div class="box__input">
                <svg class="box__icon" xmlns="http://www.w3.org/2000/svg" width="50" height="43" viewBox="0 0 50 43">
                  <path d="M48.4 26.5c-.9 0-1.7.7-1.7 1.7v11.6h-43.3v-11.6c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v13.2c0 .9.7 1.7 1.7 1.7h46.7c.9 0 1.7-.7 1.7-1.7v-13.2c0-1-.7-1.7-1.7-1.7zm-24.5 6.1c.3.3.8.5 1.2.5.4 0 .9-.2 1.2-.5l10-11.6c.7-.7.7-1.7 0-2.4s-1.7-.7-2.4 0l-7.1 8.3v-25.3c0-.9-.7-1.7-1.7-1.7s-1.7.7-1.7 1.7v25.3l-7.1-8.3c-.7-.7-1.7-.7-2.4 0s-.7 1.7 0 2.4l10 11.6z"/>
                </svg>
                <input class="box__file" type="file" name="storage_upload_files[]" id="storage_upload_file" data-multiple-caption="{count} files selected" accept="${storage_settings['accepted_file_types'].toString()}" />
                <label for="storage_upload_file"><strong>${escape(storage_settings?.translations?.modals?.upload?.choose_file)}</strong><span class="box__dragndrop"> ${escape(storage_settings?.translations?.modals?.upload?.or_drag_it)}</span>.</label>
            </div>
            <div class="box__uploading"><span class="loading-spinner active"></span></div>
            <div class="box__success">${escape(storage_settings?.translations?.modals?.upload?.success)}</div>
            <div class="box__error">${escape(storage_settings?.translations?.modals?.upload?.error)} <span></span>.</div>

            <br>
            <a class="button box__button_upload">${escape(storage_settings?.translations?.modals?.upload?.but_upload)}</a>
        </form>

        <button class="close-button" data-close aria-label="${escape(storage_settings?.translations?.modals?.upload?.but_close)}" type="button">
            <span aria-hidden="true">&times;</span>
        </button>
      </div>`;

    // Ensure to remove previous stale modal html, before appending generated code.
    $(document.body).find('[id=dt_storage_upload_modal]').remove();
    $(document.body).append(modal_html);

    // Activate upload widgets.
    activate_storage_upload_modal_widgets(
      post_type,
      post_id,
      meta_key,
      key_prefix,
    );

    // Reload reveal foundation object, in order to detect recently added upload modal element.
    $(document).foundation();

    // Open upload modal.
    $('#dt_storage_upload_modal').foundation('open');
  }

  function activate_storage_upload_modal_widgets(
    post_type,
    post_id,
    meta_key,
    key_prefix,
  ) {
    // Determine feature detection for drag & drop upload capabilities.
    const is_advanced_upload = (function () {
      const div = document.createElement('div');
      return (
        ('draggable' in div || ('ondragstart' in div && 'ondrop' in div)) &&
        'FormData' in window &&
        'FileReader' in window
      );
    })();

    // Determine if selected file type is accepted.
    const is_file_type_accepted = function (
      file_type,
      accepted_file_types = [],
    ) {
      const idx = accepted_file_types.findIndex((accepted_type) => {
        if (
          accepted_type.endsWith('/*') &&
          file_type.startsWith(
            accepted_type.substring(0, accepted_type.indexOf('/*')),
          )
        ) {
          return true;
        } else if (accepted_type === file_type) {
          return true;
        }
        return false;
      }, file_type);

      return idx > -1;
    };

    // Activate upload form.
    $('.box').each(function () {
      let $form = $(this),
        $input = $form.find('input[type="file"]'),
        $label = $form.find('label'),
        $error_msg = $form.find('.box__error span'),
        $upload_button = $form.find('.box__button_upload'),
        $restart = $form.find('.box__restart'),
        dropped_files = false,
        show_files = function (files) {
          $label.text(
            files.length > 1
              ? ($input.attr('data-multiple-caption') || '').replace(
                  '{count}',
                  files.length,
                )
              : files[0].name,
          );

          // Display upload button.
          $upload_button.fadeIn('slow');
        };

      // Display selected files.
      $input.on('change', function (e) {
        show_files(e.target.files);
      });

      // Drag & Drop files, if the feature is available.
      if (is_advanced_upload) {
        $form
          .addClass('has-advanced-upload') // letting the CSS part to know drag&drop is supported by the browser
          .on(
            'drag dragstart dragend dragover dragenter dragleave drop',
            function (e) {
              e.preventDefault();
              e.stopPropagation();
            },
          )
          .on('dragover dragenter', function () {
            if (
              !$form.hasClass('is-uploading') &&
              !$form.hasClass('is-success') &&
              !$form.hasClass('is-error')
            ) {
              $form.addClass('is-dragover');
            }
          })
          .on('dragleave dragend drop', function () {
            if (
              !$form.hasClass('is-uploading') &&
              !$form.hasClass('is-success') &&
              !$form.hasClass('is-error')
            ) {
              $form.removeClass('is-dragover');
            }
          })
          .on('drop', function (e) {
            if (
              !$form.hasClass('is-uploading') &&
              !$form.hasClass('is-success') &&
              !$form.hasClass('is-error')
            ) {
              // Enforce only single file uploads.
              const initial_drop = e.originalEvent.dataTransfer.files; // the files that were dropped
              if (initial_drop) {
                // Only proceed with first dropped file, if multiple selections detected.
                if (initial_drop?.length > 1) {
                  dropped_files = [];
                  dropped_files.push(initial_drop[0]);
                } else {
                  dropped_files = initial_drop;
                }

                // Final sanity check to ensure dropped file's type is accepted!
                if (
                  is_file_type_accepted(
                    dropped_files[0]?.type,
                    storage_settings['accepted_file_types'],
                  )
                ) {
                  show_files(dropped_files);
                }
              }
            }
          });
      }

      // Handle upload button clicks.
      $upload_button.on('click', function (e) {
        $upload_button.attr('disabled', true).fadeOut('slow');
        $form.trigger('submit');
      });

      // Handle upload form submissions.
      $form.on('submit', function (e) {
        // Prevent duplicate submissions, if the current one is in progress
        if ($form.hasClass('is-uploading')) return false;

        // Switch to uploading state.
        $form.addClass('is-uploading').removeClass('is-error');

        // Proceed with selected file upload.
        if (is_advanced_upload) {
          // ajax file upload for modern browsers
          e.preventDefault();

          // Gather selected form file data, accordingly, based on selection approach.
          let ajax_data = null;
          if (dropped_files) {
            ajax_data = new FormData();
            $.each(dropped_files, function (i, file) {
              ajax_data.append($input.attr('name'), file);
            });
          } else {
            ajax_data = new FormData($form.get(0));
          }

          // Capture additional processing settings.
          ajax_data.append('meta_key', meta_key);
          ajax_data.append('key_prefix', key_prefix);

          // Push selected fields across to backend endpoint.
          $.ajax({
            url: $form.attr('action'),
            type: $form.attr('method'),
            data: ajax_data,
            dataType: 'json',
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: (xhr) => {
              xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
            },
            complete: function () {
              $form.removeClass('is-uploading');
            },
            success: function (response) {
              console.log(response);
              if (response && response?.uploaded === true) {
                $form.addClass('is-success').fadeIn('slow', function () {
                  window.location.reload();
                });
              } else {
                $form.addClass('is-error');
                $error_msg.text(
                  escape(
                    storage_settings?.translations?.modals?.upload?.error_msg,
                  ),
                );
              }
            },
            error: function (err) {
              console.log(err);
              $form.addClass('is-error');
              $error_msg.text(
                escape(
                  storage_settings?.translations?.modals?.upload?.error_msg,
                ),
              );
            },
          });
        }
      });

      // Handle form restart states.
      $restart.on('click', function (e) {
        e.preventDefault();
        $form.removeClass('is-error is-success');
        $input.trigger('click');
      });

      // Firefox focus bug fix for file input.
      $input
        .on('focus', function () {
          $input.addClass('has-focus');
        })
        .on('blur', function () {
          $input.removeClass('has-focus');
        });
    });
  }
});

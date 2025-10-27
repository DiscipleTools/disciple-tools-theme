/**
 * Home Screen Admin JavaScript
 */

jQuery(document).ready(function ($) {
  'use strict';

  console.log('Home Screen Admin JavaScript loaded');

  // Initialize admin functionality
  initializeAdmin();

  /**
   * Initialize admin functionality
   */
  function initializeAdmin() {
    console.log('Initializing home screen admin...');

    // Set up form validation
    setupFormValidation();

    // Set up event handlers
    setupEventHandlers();
  }

  /**
   * Set up form validation
   */
  function setupFormValidation() {
    // Only validate the main settings form, not the add/edit forms
    $(
      'form:not(.app-form):not(.video-form):not(.app-edit-form):not(.video-edit-form)',
    ).on('submit', function (e) {
      const title = $('#home_screen_title').val().trim();

      if (!title) {
        e.preventDefault();
        alert('Please enter a home screen title.');
        $('#home_screen_title').focus();
        return false;
      }
    });

    // Remove required attributes from hidden forms to prevent validation issues
    $('.add-app-form, .add-video-form')
      .find('input[required]')
      .removeAttr('required');

    // Disable hidden forms to prevent them from interfering with main form submission
    $('.add-app-form, .add-video-form')
      .find('input, textarea, select')
      .prop('disabled', true);
  }

  /**
   * Set up event handlers
   */
  function setupEventHandlers() {
    // Handle settings changes
    $('#enable_training_videos').on('change', function () {
      const isEnabled = $(this).is(':checked');
      console.log('Training videos enabled:', isEnabled);

      // You can add additional logic here for when training videos are toggled
    });

    // Handle form submission feedback
    $('form').on('submit', function () {
      // Show loading state
      const $submitBtn = $(this).find('input[type="submit"]');
      const originalText = $submitBtn.val();

      $submitBtn.val('Saving...').prop('disabled', true);

      // Reset after a delay (in case of errors)
      setTimeout(function () {
        $submitBtn.val(originalText).prop('disabled', false);
      }, 3000);
    });

    // Handle edit app buttons
    $(document).on('click', '.edit-app', function (e) {
      e.preventDefault();
      const appId = $(this).data('app-id');
      console.log('Edit app clicked:', appId);
      showEditAppForm(appId);
    });

    // Handle edit video buttons
    $(document).on('click', '.edit-video', function (e) {
      e.preventDefault();
      const videoId = $(this).data('video-id');
      console.log('Edit video clicked:', videoId);
      showEditVideoForm(videoId);
    });

    // Handle cancel edit buttons
    $(document).on('click', '.cancel-edit', function (e) {
      e.preventDefault();
      hideEditForms();
    });

    // Handle add new app button
    $(document).on('click', '.add-new-app-btn', function (e) {
      e.preventDefault();
      $('.add-app-form').slideDown();
      $('.add-new-app-btn').hide();
      // Enable and restore required attributes when form is shown
      $('.add-app-form')
        .find('input, textarea, select')
        .prop('disabled', false);
      $('.add-app-form input[type="text"]').attr('required', 'required');
    });

    // Handle add new video button
    $(document).on('click', '.add-new-video-btn', function (e) {
      e.preventDefault();
      $('.add-video-form').slideDown();
      $('.add-new-video-btn').hide();
      // Enable and restore required attributes when form is shown
      $('.add-video-form')
        .find('input, textarea, select')
        .prop('disabled', false);
      $(
        '.add-video-form input[type="text"], .add-video-form input[type="url"]',
      ).attr('required', 'required');
    });

    // Handle cancel add form buttons
    $(document).on('click', '.cancel-add-form', function (e) {
      e.preventDefault();
      $('.add-app-form, .add-video-form').slideUp();
      $('.add-new-app-btn, .add-new-video-btn').show();
      // Disable and remove required attributes when forms are hidden
      $('.add-app-form, .add-video-form')
        .find('input, textarea, select')
        .prop('disabled', true);
      $('.add-app-form, .add-video-form')
        .find('input[required]')
        .removeAttr('required');
    });
  }

  /**
   * Show edit app form
   */
  function showEditAppForm(appId) {
    // Hide any existing edit forms
    hideEditForms();

    // Find the app row and get the data
    const $appRow = $(`.edit-app[data-app-id="${appId}"]`).closest('tr');
    const appData = {
      id: appId,
      title: $appRow.find('td:first strong').text(),
      description: $appRow.find('td:nth-child(2)').text(),
      url: $appRow.find('td:nth-child(3)').text(),
      icon: $appRow.find('td:first small').text(),
      color: '#667eea', // Default color, could be stored in data attribute
      enabled: $appRow.find('.status-enabled').length > 0,
    };

    // Create edit form
    const editForm = createAppEditForm(appData);

    // Insert after the app row
    $appRow.after(editForm);

    // Scroll to the form
    $('html, body').animate(
      {
        scrollTop: editForm.offset().top - 100,
      },
      500,
    );
  }

  /**
   * Show edit video form
   */
  function showEditVideoForm(videoId) {
    // Hide any existing edit forms
    hideEditForms();

    // Find the video row and get the data
    const $videoRow = $(`.edit-video[data-video-id="${videoId}"]`).closest(
      'tr',
    );
    const videoData = {
      id: videoId,
      title: $videoRow.find('td:first strong').text(),
      description: $videoRow.find('td:nth-child(2)').text(),
      duration: $videoRow.find('td:nth-child(3)').text(),
      category: $videoRow.find('td:nth-child(4)').text().toLowerCase(),
      enabled: $videoRow.find('.status-enabled').length > 0,
    };

    // Create edit form
    const editForm = createVideoEditForm(videoData);

    // Insert after the video row
    $videoRow.after(editForm);

    // Scroll to the form
    $('html, body').animate(
      {
        scrollTop: editForm.offset().top - 100,
      },
      500,
    );
  }

  /**
   * Create app edit form
   */
  function createAppEditForm(appData) {
    return $(`
            <tr class="edit-form-row" style="background-color: #f0f8ff; border: 2px solid #667eea;">
                <td colspan="5">
                    <div class="edit-form-container" style="padding: 20px;">
                        <h4 style="margin-top: 0; color: #667eea;">Edit App: ${appData.title}</h4>
                        <form method="post" class="app-edit-form">
                            <input type="hidden" name="dt_home_app_nonce" value="${$('input[name="dt_home_app_nonce"]').val()}">
                            <input type="hidden" name="dt_home_app_action" value="update">
                            <input type="hidden" name="app_id" value="${appData.id}">
                            <table class="form-table" style="margin-bottom: 15px;">
                                <tr>
                                    <th scope="row">Title</th>
                                    <td><input type="text" name="app_title" value="${appData.title}" required class="regular-text" /></td>
                                </tr>
                                <tr>
                                    <th scope="row">Description</th>
                                    <td><textarea name="app_description" rows="3" class="large-text">${appData.description}</textarea></td>
                                </tr>
                                <tr>
                                    <th scope="row">URL</th>
                                    <td><input type="url" name="app_url" value="${appData.url}" class="regular-text" /></td>
                                </tr>
                                <tr>
                                    <th scope="row">Icon</th>
                                    <td><input type="text" name="app_icon" value="${appData.icon}" class="regular-text" /></td>
                                </tr>
                                <tr>
                                    <th scope="row">Color</th>
                                    <td><input type="color" name="app_color" value="${appData.color}" /></td>
                                </tr>
                                <tr>
                                    <th scope="row">Enabled</th>
                                    <td><input type="checkbox" name="app_enabled" ${appData.enabled ? 'checked' : ''} /></td>
                                </tr>
                            </table>
                            <p class="submit">
                                <input type="submit" class="button button-primary" value="Update App" />
                                <button type="button" class="button cancel-edit">Cancel</button>
                            </p>
                        </form>
                    </div>
                </td>
            </tr>
        `);
  }

  /**
   * Create video edit form
   */
  function createVideoEditForm(videoData) {
    return $(`
            <tr class="edit-form-row" style="background-color: #f0f8ff; border: 2px solid #667eea;">
                <td colspan="6">
                    <div class="edit-form-container" style="padding: 20px;">
                        <h4 style="margin-top: 0; color: #667eea;">Edit Video: ${videoData.title}</h4>
                        <form method="post" class="video-edit-form">
                            <input type="hidden" name="dt_home_video_nonce" value="${$('input[name="dt_home_video_nonce"]').val()}">
                            <input type="hidden" name="dt_home_video_action" value="update">
                            <input type="hidden" name="video_id" value="${videoData.id}">
                            <table class="form-table" style="margin-bottom: 15px;">
                                <tr>
                                    <th scope="row">Title</th>
                                    <td><input type="text" name="video_title" value="${videoData.title}" required class="regular-text" /></td>
                                </tr>
                                <tr>
                                    <th scope="row">Description</th>
                                    <td><textarea name="video_description" rows="3" class="large-text">${videoData.description}</textarea></td>
                                </tr>
                                <tr>
                                    <th scope="row">Video URL</th>
                                    <td><input type="url" name="video_url" value="" class="regular-text" placeholder="https://youtube.com/watch?v=..." /></td>
                                </tr>
                                <tr>
                                    <th scope="row">Duration</th>
                                    <td><input type="text" name="video_duration" value="${videoData.duration}" class="regular-text" placeholder="5:30" /></td>
                                </tr>
                                <tr>
                                    <th scope="row">Category</th>
                                    <td>
                                        <select name="video_category" class="regular-text">
                                            <option value="general" ${videoData.category === 'general' ? 'selected' : ''}>General</option>
                                            <option value="basics" ${videoData.category === 'basics' ? 'selected' : ''}>Basics</option>
                                            <option value="advanced" ${videoData.category === 'advanced' ? 'selected' : ''}>Advanced</option>
                                            <option value="tutorial" ${videoData.category === 'tutorial' ? 'selected' : ''}>Tutorial</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Enabled</th>
                                    <td><input type="checkbox" name="video_enabled" ${videoData.enabled ? 'checked' : ''} /></td>
                                </tr>
                            </table>
                            <p class="submit">
                                <input type="submit" class="button button-primary" value="Update Video" />
                                <button type="button" class="button cancel-edit">Cancel</button>
                            </p>
                        </form>
                    </div>
                </td>
            </tr>
        `);
  }

  /**
   * Hide edit forms
   */
  function hideEditForms() {
    $('.edit-form-row').remove();
  }

  /**
   * Show admin notice
   */
  function showNotice(message, type = 'info') {
    const noticeClass = type === 'error' ? 'notice-error' : 'notice-success';
    const notice = $(`
            <div class="notice ${noticeClass} is-dismissible">
                <p>${message}</p>
                <button type="button" class="notice-dismiss">
                    <span class="screen-reader-text">Dismiss this notice.</span>
                </button>
            </div>
        `);

    $('.wrap h2').after(notice);

    // Auto-dismiss after 5 seconds
    setTimeout(function () {
      notice.fadeOut();
    }, 5000);
  }

  // Make functions available globally
  window.DT_Home_Admin = {
    showNotice: showNotice,
  };
});

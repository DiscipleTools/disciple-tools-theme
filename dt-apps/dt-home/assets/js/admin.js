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

    // Apply theme-aware icon colors
    applyAdminIconColors();

    // Set up form validation
    setupFormValidation();

    // Set up event handlers
    setupEventHandlers();

    // Refresh data on page load if we detect a successful operation
    // Check for success notices in the DOM - this must complete before allowing interactions
    if ($('.notice.notice-success').length > 0) {
      console.log('Success notice detected, refreshing data...');
      refreshHomeAdminData()
        .then(function () {
          console.log('Data refresh completed successfully');
        })
        .fail(function () {
          console.error('Data refresh failed, but continuing...');
        });
    }
  }

  /**
   * Apply theme-aware icon colors to admin table icons
   * Uses system preference (prefers-color-scheme) to determine default colors
   * Custom colors override defaults, same logic as frontend
   */
  function applyAdminIconColors() {
    // Detect system preference for dark/light mode
    const prefersDark =
      window.matchMedia &&
      window.matchMedia('(prefers-color-scheme: dark)').matches;
    const defaultColor = prefersDark ? '#ffffff' : '#0a0a0a'; // White for dark, black for light

    // Find all admin app icons
    const adminIcons = document.querySelectorAll('.admin-app-icon');

    adminIcons.forEach(function (icon) {
      const hasCustomColor =
        icon.getAttribute('data-has-custom-color') === 'true';

      if (!hasCustomColor) {
        // Apply theme-aware default color
        icon.style.setProperty('color', defaultColor, 'important');
      } else {
        // Use custom color if specified
        const customColor = icon.getAttribute('data-custom-color');
        if (customColor) {
          icon.style.setProperty('color', customColor, 'important');
        }
      }
    });

    // Listen for system preference changes
    if (window.matchMedia) {
      const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
      mediaQuery.addEventListener('change', function (e) {
        const newDefaultColor = e.matches ? '#ffffff' : '#0a0a0a';
        adminIcons.forEach(function (icon) {
          const hasCustomColor =
            icon.getAttribute('data-has-custom-color') === 'true';
          if (!hasCustomColor) {
            icon.style.setProperty('color', newDefaultColor, 'important');
          }
        });
      });
    }
  }

  // Track if a refresh is in progress
  let refreshInProgress = false;
  let refreshPromise = null;

  /**
   * Refresh dtHomeAdmin apps and videos data from server
   * This function updates the global dtHomeAdmin object with fresh data from the server
   * Returns a jQuery promise that resolves when refresh is complete
   */
  function refreshHomeAdminData() {
    if (
      typeof dtHomeAdmin === 'undefined' ||
      !dtHomeAdmin.ajax_url ||
      !dtHomeAdmin.nonce
    ) {
      console.error('dtHomeAdmin data not available for refresh');
      return $.Deferred().reject('dtHomeAdmin not available').promise();
    }

    // If a refresh is already in progress, return the existing promise
    if (refreshInProgress && refreshPromise) {
      console.log('Refresh already in progress, returning existing promise');
      return refreshPromise;
    }

    refreshInProgress = true;
    console.log('Starting data refresh...');

    refreshPromise = $.ajax({
      url: dtHomeAdmin.ajax_url,
      type: 'GET',
      data: {
        action: 'dt_home_refresh_data',
        nonce: dtHomeAdmin.nonce,
      },
    })
      .then(function (response) {
        refreshInProgress = false;
        if (response.success && response.data) {
          // Update the global dtHomeAdmin object with fresh data
          if (response.data.apps) {
            dtHomeAdmin.apps = response.data.apps;
            console.log(
              'Apps data refreshed:',
              dtHomeAdmin.apps.length,
              'apps',
            );
          }
          if (response.data.videos) {
            dtHomeAdmin.videos = response.data.videos;
            console.log(
              'Videos data refreshed:',
              dtHomeAdmin.videos.length,
              'videos',
            );
          }

          // Reapply icon colors after data refresh
          setTimeout(function () {
            applyAdminIconColors();
          }, 100);

          return response;
        } else {
          console.error(
            'Failed to refresh data:',
            response.data?.message || 'Unknown error',
          );
          refreshInProgress = false;
          return $.Deferred()
            .reject(response.data?.message || 'Unknown error')
            .promise();
        }
      })
      .fail(function (xhr, status, error) {
        refreshInProgress = false;
        console.error('Error refreshing data:', error);
        return $.Deferred().reject(error).promise();
      });

    return refreshPromise;
  }

  // Make refresh function available globally for manual calls if needed
  window.refreshHomeAdminData = refreshHomeAdminData;

  /**
   * Set up form validation
   */
  function setupFormValidation() {
    // Form validation is now handled in the main form submit handler below

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
    // Handle icon input changes for live preview
    $(document).on('input', 'input[name="app_icon"]', function () {
      updateIconPreview($(this));
    });

    // Custom handler for add form icon button to prevent auto-submission
    // This prevents page refresh when selecting an icon during app creation
    $(document).on(
      'click',
      '.add-app-form-container .change-icon-button, form[name="dt_home_app_form_create"] .change-icon-button',
      function (e) {
        e.preventDefault();
        e.stopPropagation(); // Prevent dt-options.js handler from running

        // Get the icon input field
        const iconInput = $("input[name='" + $(this).data('icon-input') + "']");

        if (iconInput.length === 0) {
          console.error('Icon input not found');
          return;
        }

        // Call icon selector dialog with null parent_form to prevent auto-submission
        // The icon will still be updated in the input field, but form won't submit
        if (typeof display_icon_selector_dialog === 'function') {
          display_icon_selector_dialog(null, iconInput, function (source) {
            // Update icon preview after icon is selected
            if (source === 'save') {
              updateIconPreview(iconInput);
            }
          });
        } else {
          console.error('display_icon_selector_dialog function not found');
        }
      },
    );

    /**
     * Shared function to save settings form
     * This ensures all form fields are properly included in the POST data
     */
    function saveSettingsForm() {
      console.log('saveSettingsForm called');

      const $form = $('#dt-home-settings-form');

      if ($form.length === 0) {
        console.error('Settings form not found');
        alert('Error: Form not found. Please refresh the page.');
        return false;
      }

      // Validate title field
      const title = $('#home_screen_title').val().trim();
      if (!title) {
        alert('Please enter a home screen title.');
        $('#home_screen_title').focus();
        return false;
      }

      // Extract values from general settings fields (they may be outside the form)
      const titleValue = $('#home_screen_title').val() || '';
      const descriptionValue = $('#home_screen_description').val() || '';
      const enableRolesPermissions = $('#enable_roles_permissions').is(
        ':checked',
      );
      const inviteOthers = $('#invite_others').is(':checked');
      const requireLogin = $('#require_login').is(':checked');

      console.log('Form validation passed, submitting form');
      console.log('Extracted form data:', {
        title: titleValue,
        description: descriptionValue,
        enable_roles_permissions: enableRolesPermissions,
        invite_others: inviteOthers,
        require_login: requireLogin,
        dt_home_screen_settings: $form
          .find('input[name="dt_home_screen_settings"]')
          .val(),
        nonce: $form.find('input[name="dt_home_screen_nonce"]').val(),
      });

      // Create or update hidden fields in the form with the extracted values
      // This ensures they're included in the POST request

      // Handle home_screen_title
      let $titleField = $form.find('input[name="home_screen_title"]');
      if ($titleField.length === 0) {
        $titleField = $('<input>').attr({
          type: 'hidden',
          name: 'home_screen_title',
        });
        $form.append($titleField);
      }
      $titleField.val(titleValue);

      // Handle home_screen_description
      let $descriptionField = $form.find(
        'input[name="home_screen_description"]',
      );
      if ($descriptionField.length === 0) {
        $descriptionField = $('<input>').attr({
          type: 'hidden',
          name: 'home_screen_description',
        });
        $form.append($descriptionField);
      }
      $descriptionField.val(descriptionValue);

      // Handle enable_roles_permissions (checkbox)
      // Remove any existing hidden field first
      $form.find('input[name="enable_roles_permissions"]').remove();
      // Only add the field if checkbox is checked (standard HTML form behavior)
      if (enableRolesPermissions) {
        const $checkboxField = $('<input>').attr({
          type: 'hidden',
          name: 'enable_roles_permissions',
          value: '1',
        });
        $form.append($checkboxField);
      }

      // Handle invite_others (checkbox)
      // Remove any existing hidden fields first (both the one we added and any existing ones)
      $form.find('input[name="invite_others"]').remove();
      // Add hidden field with value 0 (default), then override with 1 if checked
      const $inviteOthersField = $('<input>').attr({
        type: 'hidden',
        name: 'invite_others',
        value: inviteOthers ? '1' : '0',
      });
      $form.append($inviteOthersField);

      // Handle require_login (checkbox)
      // Remove any existing hidden fields first
      $form.find('input[name="require_login"]').remove();
      // Add hidden field with value 0 (default), then override with 1 if checked
      const $requireLoginField = $('<input>').attr({
        type: 'hidden',
        name: 'require_login',
        value: requireLogin ? '1' : '0',
      });
      $form.append($requireLoginField);

      console.log('Added hidden fields to form. Form now contains:', {
        title: $form.find('input[name="home_screen_title"]').length,
        description: $form.find('input[name="home_screen_description"]').length,
        enable_roles_permissions: $form.find(
          'input[name="enable_roles_permissions"]',
        ).length,
        invite_others: $form.find('input[name="invite_others"]').length,
        require_login: $form.find('input[name="require_login"]').length,
      });

      // Show loading state on all submit buttons
      $('#save-settings-top, #save-settings-bottom').each(function () {
        const $btn = $(this);
        const originalText = $btn.val();
        $btn.data('original-text', originalText);
        $btn.val('Saving...').addClass('saving');
      });

      // Ensure form has proper action attribute
      if (!$form.attr('action')) {
        $form.attr('action', window.location.href);
      }

      // Use native DOM submit to ensure all form fields are included in POST
      // This bypasses jQuery handlers and directly submits the form
      const formElement = $form[0];
      if (formElement) {
        console.log('Submitting form via native DOM submit');
        try {
          // Call native submit method which will include all form fields
          formElement.submit();
        } catch (error) {
          console.error('Error submitting form:', error);
          // Fallback to jQuery submit
          console.log('Falling back to jQuery submit');
          $form.submit();
        }
      } else {
        console.error('Form element not found');
        alert('Error: Could not submit form. Please refresh the page.');
        return false;
      }

      return true;
    }

    // Handle top Save Settings button click
    $(document).on('click', '#save-settings-top', function (e) {
      e.preventDefault();
      e.stopPropagation();
      console.log('Top Save Settings button clicked');
      saveSettingsForm();
      return false;
    });

    // Handle bottom Save Settings button click
    $(document).on('click', '#save-settings-bottom', function (e) {
      e.preventDefault();
      e.stopPropagation();
      console.log('Bottom Save Settings button clicked');
      saveSettingsForm();
      return false;
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

    // Handle role selection toggle
    $(document).on('change', 'input[name="app_user_roles_type"]', function () {
      const isSpecificRoles = $(this).val() === 'support_specific_roles';
      $('.app-roles-selection').toggle(isSpecificRoles);
    });

    // Handle form submission in modal
    $(document).on('submit', '#dt-app-edit-form', function (e) {
      // Validate form before submission
      const form = this;
      const title = $(form).find('#app-edit-title').val().trim();

      if (!title) {
        e.preventDefault();
        alert('Please enter a title for the app.');
        $(form).find('#app-edit-title').focus();
        return false;
      }

      // Form will submit normally via POST
      // The page will reload after submission, which will close the modal
      // No need to prevent default or close modal manually
    });

    // Handle cancel edit buttons (for modal)
    $(document).on('click', '.cancel-edit-modal', function (e) {
      e.preventDefault();
      closeEditModal();
    });

    // Handle cancel edit buttons (for inline forms - legacy support)
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
   * Show edit app form in modal dialog
   */
  function showEditAppForm(appId) {
    // Close any existing edit modals
    closeEditModal();

    // Check if dtHomeAdmin is available
    if (typeof dtHomeAdmin === 'undefined' || !dtHomeAdmin.apps) {
      console.error('dtHomeAdmin.apps is not available');
      alert('Unable to load app data. Please refresh the page.');
      return;
    }

    // Helper function to find and open the app
    function findAndOpenApp() {
      // Find the app in the apps array
      const appData = dtHomeAdmin.apps.find((app) => app.id === appId);

      if (!appData) {
        // App not found - refresh data and try again
        console.log('App not found in current data, refreshing...');
        refreshHomeAdminData()
          .then(function () {
            // Try again after refresh
            const refreshedAppData = dtHomeAdmin.apps.find(
              (app) => app.id === appId,
            );
            if (refreshedAppData) {
              console.log('App found after refresh');
              populateEditModal(refreshedAppData);
              openEditModal();
            } else {
              console.error('App not found after refresh:', appId);
              alert('App not found. Please refresh the page.');
            }
          })
          .fail(function () {
            console.error('Failed to refresh data');
            alert('Unable to refresh app data. Please refresh the page.');
          });
        return;
      }

      // App found - open the modal
      populateEditModal(appData);
      openEditModal();
    }

    // If a refresh is in progress, wait for it to complete
    if (refreshInProgress && refreshPromise) {
      console.log('Waiting for refresh to complete...');
      refreshPromise
        .then(function () {
          findAndOpenApp();
        })
        .fail(function () {
          // Even if refresh fails, try to find the app with current data
          findAndOpenApp();
        });
    } else {
      // No refresh in progress, proceed immediately
      findAndOpenApp();
    }
  }

  /**
   * Show edit video form in modal dialog
   */
  function showEditVideoForm(videoId) {
    // Close any existing edit modals
    closeEditVideoModal();

    // Check if dtHomeAdmin is available
    if (typeof dtHomeAdmin === 'undefined' || !dtHomeAdmin.videos) {
      console.error('dtHomeAdmin.videos is not available');
      alert('Unable to load video data. Please refresh the page.');
      return;
    }

    // Helper function to find and open the video
    function findAndOpenVideo() {
      // Find the video in the videos array
      const videoData = dtHomeAdmin.videos.find(
        (video) => video.id === videoId,
      );

      if (!videoData) {
        // Video not found - refresh data and try again
        console.log('Video not found in current data, refreshing...');
        refreshHomeAdminData()
          .then(function () {
            // Try again after refresh
            const refreshedVideoData = dtHomeAdmin.videos.find(
              (video) => video.id === videoId,
            );
            if (refreshedVideoData) {
              console.log('Video found after refresh');
              populateEditVideoModal(refreshedVideoData);
              openEditVideoModal();
            } else {
              console.error('Video not found after refresh:', videoId);
              alert('Video not found. Please refresh the page.');
            }
          })
          .fail(function () {
            console.error('Failed to refresh data');
            alert('Unable to refresh video data. Please refresh the page.');
          });
        return;
      }

      // Video found - open the modal
      populateEditVideoModal(videoData);
      openEditVideoModal();
    }

    // If a refresh is in progress, wait for it to complete
    if (refreshInProgress && refreshPromise) {
      console.log('Waiting for refresh to complete...');
      refreshPromise
        .then(function () {
          findAndOpenVideo();
        })
        .fail(function () {
          // Even if refresh fails, try to find the video with current data
          findAndOpenVideo();
        });
    } else {
      // No refresh in progress, proceed immediately
      findAndOpenVideo();
    }
  }

  /**
   * Update icon preview
   */
  function updateIconPreview($input) {
    const iconValue = $input.val();

    // For modal form, find the wrapper specifically in the Icon row
    if (
      $input.closest('#dt-app-edit-form').length > 0 &&
      $input.attr('id') === 'app-edit-icon'
    ) {
      // The icon input has a unique ID, find its sibling wrapper in the same flex container
      const $flexContainer = $input.closest('.icon-field-container');
      if ($flexContainer.length > 0) {
        const $wrapper = $flexContainer.find('.field-icon-wrapper');
        if ($wrapper.length > 0) {
          updateIconWrapper($wrapper, iconValue);
          return;
        }
      }
    }

    // For other forms, look for wrapper in the same table row (for nested table structure)
    let $wrapper = $input.closest('tr').find('.field-icon-wrapper');

    // If not found, look in the parent table row
    if ($wrapper.length === 0) {
      $wrapper = $input
        .closest('table')
        .closest('tr')
        .find('.field-icon-wrapper');
    }

    // If still not found, look in the entire form by name
    if ($wrapper.length === 0) {
      const formName = $input.closest('form').attr('name');
      if (formName) {
        $wrapper = $(`form[name="${formName}"] .field-icon-wrapper`);
      }
    }

    if ($wrapper.length) {
      updateIconWrapper($wrapper, iconValue);
    }
  }

  /**
   * Update icon wrapper content
   */
  function updateIconWrapper($wrapper, iconValue) {
    if (iconValue && iconValue.trim().toLowerCase().startsWith('mdi')) {
      $wrapper.html(
        `<i class="${iconValue} field-icon" style="font-size: 20px; vertical-align: middle;"></i>`,
      );
    } else if (iconValue && iconValue.trim()) {
      $wrapper.html(
        `<img src="${iconValue}" class="field-icon" style="width: 20px; height: 20px; vertical-align: middle;" />`,
      );
    } else {
      $wrapper.html(
        '<i class="mdi mdi-apps field-icon" style="font-size: 20px; vertical-align: middle;"></i>',
      );
    }
  }

  /**
   * Populate edit modal with app data
   */
  function populateEditModal(appData) {
    const userRolesType = appData.user_roles_type || 'support_all_roles';
    const selectedRoles = appData.roles || [];
    const appType = appData.type || 'link';

    // Set app ID in hidden field
    $('#app-edit-id').val(appData.id);

    // Build form HTML
    const formHtml = `
      <table class="form-table" style="width: 100%; box-sizing: border-box;">
        <tr>
          <th scope="row" style="width: 150px;">Type</th>
          <td>
            <select name="app_type" id="app-edit-type" required style="width: 100%; max-width: 100%; box-sizing: border-box;">
              <option value="link" ${appType === 'link' ? 'selected' : ''}>Link</option>
              <option value="app" ${appType === 'app' ? 'selected' : ''}>App</option>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row" style="width: 150px;">Title</th>
          <td><input type="text" name="app_title" id="app-edit-title" value="${escapeHtml(appData.title || '')}" required class="regular-text" style="width: 100%; max-width: 100%; box-sizing: border-box;" /></td>
        </tr>
        <tr>
          <th scope="row" style="width: 150px;">Description</th>
          <td><textarea name="app_description" id="app-edit-description" rows="3" class="large-text" style="width: 100%; max-width: 100%; box-sizing: border-box;">${escapeHtml(appData.description || '')}</textarea></td>
        </tr>
        <tr>
          <th scope="row" style="width: 150px;">URL</th>
          <td><input type="url" name="app_url" id="app-edit-url" value="${escapeHtml(appData.url || '#')}" class="regular-text" style="width: 100%; max-width: 100%; box-sizing: border-box;" /></td>
        </tr>
        <tr>
          <th scope="row" style="width: 150px;">Icon</th>
          <td>
            <div class="icon-field-container" style="display: flex; align-items: center; gap: 8px; width: 100%; box-sizing: border-box;">
              <span class="field-icon-wrapper" style="flex-shrink: 0; width: 40px; text-align: center;">
                ${
                  appData.icon &&
                  appData.icon.trim().toLowerCase().startsWith('mdi')
                    ? `<i class="${escapeHtml(appData.icon)} field-icon" style="font-size: 20px; vertical-align: middle;"></i>`
                    : appData.icon
                      ? `<img src="${escapeHtml(appData.icon)}" class="field-icon" style="width: 20px; height: 20px; vertical-align: middle;" />`
                      : `<i class="mdi mdi-apps field-icon" style="font-size: 20px; vertical-align: middle;"></i>`
                }
              </span>
              <input type="text" name="app_icon" id="app-edit-icon" value="${escapeHtml(appData.icon || 'mdi mdi-apps')}" class="regular-text" style="flex: 1; min-width: 0; box-sizing: border-box;" />
              <button type="button" class="button change-icon-button" data-form="dt-app-edit-form" data-icon-input="app_icon" style="flex-shrink: 0;">Change Icon</button>
            </div>
          </td>
        </tr>
        <tr>
          <th scope="row" style="width: 150px;">Color</th>
          <td><input type="color" name="app_color" id="app-edit-color" value="${escapeHtml(appData.color || '#667eea')}" /></td>
        </tr>
        <tr>
          <th scope="row" style="width: 150px;">Enabled</th>
          <td><input type="checkbox" name="app_enabled" id="app-edit-enabled" ${appData.enabled ? 'checked' : ''} /></td>
        </tr>
        <tr>
          <th scope="row" style="width: 150px;">User Access</th>
          <td>
            <label>
              <input type="radio" name="app_user_roles_type" id="app-edit-roles-all" value="support_all_roles" ${userRolesType === 'support_all_roles' ? 'checked' : ''} />
              All roles have access
            </label>
            <br>
            <label>
              <input type="radio" name="app_user_roles_type" id="app-edit-roles-specific" value="support_specific_roles" ${userRolesType === 'support_specific_roles' ? 'checked' : ''} />
              Limit access by role
            </label>
          </td>
        </tr>
        <tr class="app-roles-selection" style="display: ${userRolesType === 'support_specific_roles' ? 'table-row' : 'none'};">
          <th scope="row" style="width: 150px;">Select Roles</th>
          <td>
            <div class="roles-checkboxes" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; box-sizing: border-box;">
              ${buildRolesCheckboxes(selectedRoles)}
            </div>
          </td>
        </tr>
      </table>
    `;

    // Insert form HTML
    $('#dt-app-edit-form-content').html(formHtml);

    // Ensure all form fields are enabled (in case they were disabled)
    $('#dt-app-edit-form')
      .find('input, textarea, select')
      .prop('disabled', false);

    // Set up icon preview
    setupIconPreview();

    // Set up role selection toggle
    setupRoleSelectionToggle();
  }

  /**
   * Build roles checkboxes HTML
   */
  function buildRolesCheckboxes(selectedRoles = []) {
    if (!dtHomeAdmin || !dtHomeAdmin.roles) {
      return '<p><em>Unable to load roles. Please refresh the page.</em></p>';
    }

    let html = '';
    const roles = dtHomeAdmin.roles;
    const roleKeys = Object.keys(roles).sort();

    roleKeys.forEach((roleKey) => {
      const roleData = roles[roleKey];
      const roleLabel = roleData.label || roleKey;
      const isChecked = selectedRoles.includes(roleKey) ? 'checked' : '';

      html += `
        <label style="display: block; margin-bottom: 5px;">
          <input type="checkbox" name="app_roles[]" value="${escapeHtml(roleKey)}" ${isChecked} />
          ${escapeHtml(roleLabel)}
        </label>
      `;
    });

    return html || '<p><em>No roles available.</em></p>';
  }

  /**
   * Setup icon preview handler
   */
  function setupIconPreview() {
    // Remove existing handler if any
    $('#app-edit-icon').off('input');

    // Add new handler
    $('#app-edit-icon').on('input', function () {
      updateIconPreview($(this));
    });
  }

  /**
   * Setup role selection toggle handler
   */
  function setupRoleSelectionToggle() {
    // Remove existing handlers
    $('input[name="app_user_roles_type"]').off('change');

    // Add new handler
    $('input[name="app_user_roles_type"]').on('change', function () {
      const isSpecificRoles = $(this).val() === 'support_specific_roles';
      $('.app-roles-selection').toggle(isSpecificRoles);
    });
  }

  /**
   * Open edit modal dialog
   */
  function openEditModal() {
    // Get the dialog element - this becomes .ui-dialog-content when initialized
    const $dialog = $('#dt-app-edit-dialog');

    // Initialize dialog if not already initialized
    if (!$dialog.hasClass('ui-dialog-content')) {
      $dialog.dialog({
        modal: true,
        autoOpen: false,
        width: 650,
        maxWidth: $(window).width() * 0.9,
        maxHeight: $(window).height() * 0.8,
        resizable: true,
        draggable: true,
        closeOnEscape: true,
        buttons: [
          {
            text: dtHomeAdmin?.strings?.update_app || 'Update App',
            class: 'button button-primary',
            click: function (e) {
              e.preventDefault();

              // Get the dialog element - jQuery UI dialog transforms #dt-app-edit-dialog
              // into .ui-dialog-content, but the form is still inside it
              const $dialogElement = $('#dt-app-edit-dialog');

              // When jQuery UI initializes a dialog, it:
              // 1. Wraps the original element in a .ui-dialog container
              // 2. The original element becomes .ui-dialog-content
              // 3. The form inside the original element should still be accessible

              // Try multiple ways to find the form
              let $formElement = null;

              // Method 1: Find form inside the dialog element (original element)
              $formElement = $dialogElement.find('form#dt-app-edit-form');

              // Method 2: If not found, try direct children
              if (!$formElement || !$formElement.length) {
                $formElement = $dialogElement.children('form#dt-app-edit-form');
              }

              // Method 3: Try global search
              if (!$formElement || !$formElement.length) {
                $formElement = $('#dt-app-edit-form');
              }

              // Method 4: Try using dialog widget instance
              if (!$formElement || !$formElement.length) {
                try {
                  const dialogInstance = $dialogElement.dialog('instance');
                  if (dialogInstance && dialogInstance.element) {
                    // The element property is the dialog content element
                    $formElement = dialogInstance.element.find(
                      'form#dt-app-edit-form',
                    );
                  }
                } catch (err) {
                  console.error('Error accessing dialog instance:', err);
                }
              }

              // Method 5: Try finding via .ui-dialog-content class
              if (!$formElement || !$formElement.length) {
                $formElement = $('.ui-dialog-content#dt-app-edit-dialog').find(
                  'form#dt-app-edit-form',
                );
              }

              // Method 6: If form tag is missing (jQuery UI might have removed it),
              // we need to reconstruct it or find the form content directly
              if (!$formElement || !$formElement.length) {
                // Try finding by name attribute (more reliable if ID was removed)
                $formElement = $dialogElement.find(
                  'form[name="dt-app-edit-form"]',
                );
              }

              // Method 7: If still not found, try finding any form in the dialog
              if (!$formElement || !$formElement.length) {
                $formElement = $dialogElement.find('form');
              }

              // Method 8: If form tag is completely missing, we need to reconstruct it
              // jQuery UI dialog strips the form tag - this is a known issue
              if (!$formElement || !$formElement.length) {
                const $formContent = $dialogElement.find(
                  '#dt-app-edit-form-content',
                );
                const $hiddenInputs = $dialogElement.find(
                  'input[type="hidden"]',
                );

                if ($formContent.length > 0 || $hiddenInputs.length > 0) {
                  // The form tag is missing - jQuery UI stripped it
                  // Create a form element to submit the data
                  $formElement = $('<form>', {
                    id: 'dt-app-edit-form',
                    name: 'dt-app-edit-form',
                    method: 'post',
                    action: '',
                  });

                  // Add all hidden inputs from the dialog (with values preserved)
                  $hiddenInputs.each(function () {
                    const $input = $(this);
                    $formElement.append($input.clone(true, true));
                  });

                  // Add all form fields from the form content div (with values preserved)
                  if ($formContent.length > 0) {
                    $formContent
                      .find('input, textarea, select')
                      .each(function () {
                        const $field = $(this);
                        const $clonedField = $field.clone(true, true);

                        // For select elements, preserve the selected option
                        if ($field.is('select')) {
                          const selectedValue = $field.val();
                          $clonedField.val(selectedValue);
                        }

                        // For checkboxes, preserve checked state
                        if ($field.is(':checkbox')) {
                          $clonedField.prop('checked', $field.is(':checked'));
                        }

                        // For radio buttons, preserve checked state
                        if ($field.is(':radio')) {
                          $clonedField.prop('checked', $field.is(':checked'));
                        }

                        $formElement.append($clonedField);
                      });
                  }

                  // Temporarily add form to body for submission (hidden)
                  $formElement.css('display', 'none').appendTo('body');

                  console.log(
                    'Form reconstructed. Field count:',
                    $formElement.find('input, textarea, select').length,
                  );
                }
              }

              // Final check if form exists
              if (!$formElement || !$formElement.length) {
                console.error(
                  'Form not found. Dialog element:',
                  $dialogElement.length,
                );
                console.error('Dialog element HTML:', $dialogElement.html());
                console.error('All forms in document:', $('form').length);
                console.error(
                  'Forms with ID dt-app-edit-form:',
                  $('#dt-app-edit-form').length,
                );
                console.error(
                  'Forms with name dt-app-edit-form:',
                  $('form[name="dt-app-edit-form"]').length,
                );
                alert('Error: Form not found. Please refresh the page.');
                return false;
              }

              // Validate before submitting
              const $titleInput = $formElement.find('#app-edit-title');
              if ($titleInput.length === 0) {
                console.error('Form fields not populated yet');
                alert(
                  'Error: Form fields not loaded. Please close and try again.',
                );
                return false;
              }

              const title = $titleInput.val().trim();
              if (!title) {
                alert('Please enter a title for the app.');
                $titleInput.focus();
                return false;
              }

              // Ensure form is properly set up for submission
              // Make sure all form fields are enabled
              $formElement
                .find('input, textarea, select')
                .prop('disabled', false);

              // Submit the form using native DOM submit (this will cause a page reload)
              const formDomElement = $formElement[0];
              if (
                formDomElement &&
                typeof formDomElement.submit === 'function'
              ) {
                // Use native submit which bypasses jQuery handlers
                formDomElement.submit();
              } else {
                // Fallback: trigger submit event
                $formElement.trigger('submit');
              }

              return false;
            },
          },
          {
            text: dtHomeAdmin?.strings?.cancel || 'Cancel',
            class: 'button',
            click: function () {
              $(this).dialog('close');
            },
          },
        ],
        open: function () {
          // Update icon preview when dialog opens
          updateIconPreview($('#app-edit-icon'));

          // Verify form is accessible when dialog opens
          const $formCheck = $('#dt-app-edit-form');
          if ($formCheck.length === 0) {
            console.warn('Form not found when dialog opens');
          }
        },
        close: function () {
          // Clean up when closing
          $('#dt-app-edit-form-content').html('');
        },
      });
    }

    // Open the dialog
    $dialog.dialog('open');

    // After dialog is opened, verify form is accessible
    // Use a small delay to ensure dialog is fully rendered
    setTimeout(function () {
      const $formCheck = $('#dt-app-edit-form');
      if ($formCheck.length === 0) {
        console.warn('Form not accessible after dialog opens');
      } else {
        console.log('Form found after dialog opens:', $formCheck.length);
      }
    }, 100);
  }

  /**
   * Escape HTML to prevent XSS
   */
  function escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;',
    };
    return String(text || '').replace(/[&<>"']/g, function (m) {
      return map[m];
    });
  }

  /**
   * Load roles for edit form
   */
  function loadRolesForEditForm(container, selectedRoles = []) {
    // This would typically make an AJAX call to get roles
    // For now, we'll use a simple approach with the roles from the page
    const rolesContainer = container.find('.roles-checkboxes');

    // Get roles from the add form (they should be available)
    const addFormRoles = $('.add-app-form .roles-checkboxes').html();
    if (addFormRoles) {
      rolesContainer.html(addFormRoles);

      // Enable all checkboxes in the edit form (they might be disabled from the add form)
      rolesContainer.find('input[type="checkbox"]').prop('disabled', false);

      // Check the appropriate roles
      selectedRoles.forEach((role) => {
        rolesContainer.find(`input[value="${role}"]`).prop('checked', true);
      });
    } else {
      rolesContainer.html(
        '<p><em>Unable to load roles. Please refresh the page.</em></p>',
      );
    }
  }

  /**
   * Populate edit video modal with video data
   */
  function populateEditVideoModal(videoData) {
    const videoCategory = videoData.category || 'general';

    // Set video ID in hidden field
    $('#video-edit-id').val(videoData.id);

    // Build form HTML
    const formHtml = `
      <table class="form-table" style="width: 100%; box-sizing: border-box;">
        <tr>
          <th scope="row" style="width: 150px;">Title</th>
          <td><input type="text" name="video_title" id="video-edit-title" value="${escapeHtml(videoData.title || '')}" required class="regular-text" style="width: 100%; max-width: 100%; box-sizing: border-box;" /></td>
        </tr>
        <tr>
          <th scope="row" style="width: 150px;">Description</th>
          <td><textarea name="video_description" id="video-edit-description" rows="3" class="large-text" style="width: 100%; max-width: 100%; box-sizing: border-box;">${escapeHtml(videoData.description || '')}</textarea></td>
        </tr>
        <tr>
          <th scope="row" style="width: 150px;">Video URL</th>
          <td><input type="url" name="video_url" id="video-edit-url" value="${escapeHtml(videoData.video_url || '')}" class="regular-text" placeholder="https://youtube.com/watch?v=..." style="width: 100%; max-width: 100%; box-sizing: border-box;" /></td>
        </tr>
        <tr>
          <th scope="row" style="width: 150px;">Duration</th>
          <td><input type="text" name="video_duration" id="video-edit-duration" value="${escapeHtml(videoData.duration || '')}" class="regular-text" placeholder="5:30" style="width: 100%; max-width: 100%; box-sizing: border-box;" /></td>
        </tr>
        <tr>
          <th scope="row" style="width: 150px;">Category</th>
          <td>
            <select name="video_category" id="video-edit-category" class="regular-text" style="width: 100%; max-width: 100%; box-sizing: border-box;">
              <option value="general" ${videoCategory === 'general' ? 'selected' : ''}>General</option>
              <option value="basics" ${videoCategory === 'basics' ? 'selected' : ''}>Basics</option>
              <option value="advanced" ${videoCategory === 'advanced' ? 'selected' : ''}>Advanced</option>
              <option value="tutorial" ${videoCategory === 'tutorial' ? 'selected' : ''}>Tutorial</option>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row" style="width: 150px;">Enabled</th>
          <td><input type="checkbox" name="video_enabled" id="video-edit-enabled" ${videoData.enabled ? 'checked' : ''} /></td>
        </tr>
      </table>
    `;

    // Insert form HTML
    $('#dt-video-edit-form-content').html(formHtml);
  }

  /**
   * Open edit video modal dialog
   */
  function openEditVideoModal() {
    // Get the dialog element - this becomes .ui-dialog-content when initialized
    const $dialog = $('#dt-video-edit-dialog');

    // Initialize dialog if not already initialized
    if (!$dialog.hasClass('ui-dialog-content')) {
      $dialog.dialog({
        modal: true,
        autoOpen: false,
        width: 650,
        maxWidth: $(window).width() * 0.9,
        maxHeight: $(window).height() * 0.8,
        resizable: true,
        draggable: true,
        closeOnEscape: true,
        buttons: [
          {
            text: dtHomeAdmin?.strings?.update_video || 'Update Video',
            class: 'button button-primary',
            click: function (e) {
              e.preventDefault();

              // Get the dialog element - jQuery UI dialog transforms #dt-video-edit-dialog
              // into .ui-dialog-content, but the form is still inside it
              const $dialogElement = $('#dt-video-edit-dialog');

              // Try multiple ways to find the form
              let $formElement = null;

              // Method 1: Find form inside the dialog element (original element)
              $formElement = $dialogElement.find('form#dt-video-edit-form');

              // Method 2: If not found, try direct children
              if (!$formElement || !$formElement.length) {
                $formElement = $dialogElement.children(
                  'form#dt-video-edit-form',
                );
              }

              // Method 3: Try global search
              if (!$formElement || !$formElement.length) {
                $formElement = $('#dt-video-edit-form');
              }

              // Method 4: Try using dialog widget instance
              if (!$formElement || !$formElement.length) {
                try {
                  const dialogInstance = $dialogElement.dialog('instance');
                  if (dialogInstance && dialogInstance.element) {
                    $formElement = dialogInstance.element.find(
                      'form#dt-video-edit-form',
                    );
                  }
                } catch (err) {
                  console.error('Error accessing dialog instance:', err);
                }
              }

              // Method 5: Try finding via .ui-dialog-content class
              if (!$formElement || !$formElement.length) {
                $formElement = $(
                  '.ui-dialog-content#dt-video-edit-dialog',
                ).find('form#dt-video-edit-form');
              }

              // Method 6: Try finding by name attribute
              if (!$formElement || !$formElement.length) {
                $formElement = $dialogElement.find(
                  'form[name="dt-video-edit-form"]',
                );
              }

              // Method 7: Try finding any form in the dialog
              if (!$formElement || !$formElement.length) {
                $formElement = $dialogElement.find('form');
              }

              // Method 8: If form tag is completely missing, reconstruct it
              // jQuery UI dialog strips the form tag - this is a known issue
              if (!$formElement || !$formElement.length) {
                const $formContent = $dialogElement.find(
                  '#dt-video-edit-form-content',
                );
                const $hiddenInputs = $dialogElement.find(
                  'input[type="hidden"]',
                );

                if ($formContent.length > 0 || $hiddenInputs.length > 0) {
                  // The form tag is missing - jQuery UI stripped it
                  // Create a form element to submit the data
                  $formElement = $('<form>', {
                    id: 'dt-video-edit-form',
                    name: 'dt-video-edit-form',
                    method: 'post',
                    action: '',
                  });

                  // Add all hidden inputs from the dialog (with values preserved)
                  $hiddenInputs.each(function () {
                    const $input = $(this);
                    $formElement.append($input.clone(true, true));
                  });

                  // Add all form fields from the form content div (with values preserved)
                  if ($formContent.length > 0) {
                    $formContent
                      .find('input, textarea, select')
                      .each(function () {
                        const $field = $(this);
                        const $clonedField = $field.clone(true, true);

                        // For select elements, preserve the selected option
                        if ($field.is('select')) {
                          const selectedValue = $field.val();
                          $clonedField.val(selectedValue);
                        }

                        // For checkboxes, preserve checked state
                        if ($field.is(':checkbox')) {
                          $clonedField.prop('checked', $field.is(':checked'));
                        }

                        $formElement.append($clonedField);
                      });
                  }

                  // Temporarily add form to body for submission (hidden)
                  $formElement.css('display', 'none').appendTo('body');
                }
              }

              // Final check if form exists
              if (!$formElement || !$formElement.length) {
                console.error(
                  'Video form not found. Dialog element:',
                  $dialogElement.length,
                );
                alert('Error: Form not found. Please refresh the page.');
                return false;
              }

              // Validate before submitting
              const $titleInput = $formElement.find('#video-edit-title');
              if ($titleInput.length === 0) {
                console.error('Video form fields not populated yet');
                alert(
                  'Error: Form fields not loaded. Please close and try again.',
                );
                return false;
              }

              const title = $titleInput.val().trim();
              if (!title) {
                alert('Please enter a title for the video.');
                $titleInput.focus();
                return false;
              }

              // Ensure form is properly set up for submission
              $formElement
                .find('input, textarea, select')
                .prop('disabled', false);

              // Submit the form using native DOM submit (this will cause a page reload)
              const formDomElement = $formElement[0];
              if (
                formDomElement &&
                typeof formDomElement.submit === 'function'
              ) {
                formDomElement.submit();
              } else {
                $formElement.trigger('submit');
              }

              return false;
            },
          },
          {
            text: dtHomeAdmin?.strings?.cancel || 'Cancel',
            class: 'button',
            click: function () {
              $(this).dialog('close');
            },
          },
        ],
        open: function () {
          // Verify form is accessible when dialog opens
          const $formCheck = $('#dt-video-edit-form');
          if ($formCheck.length === 0) {
            console.warn('Video form not found when dialog opens');
          }
        },
        close: function () {
          // Clean up when closing
          $('#dt-video-edit-form-content').html('');
        },
      });
    }

    // Open the dialog
    $dialog.dialog('open');
  }

  /**
   * Close edit modal
   */
  function closeEditModal() {
    const $dialog = $('#dt-app-edit-dialog');
    if ($dialog.hasClass('ui-dialog-content')) {
      $dialog.dialog('close');
    }
  }

  /**
   * Close edit video modal
   */
  function closeEditVideoModal() {
    const $dialog = $('#dt-video-edit-dialog');
    if ($dialog.hasClass('ui-dialog-content')) {
      $dialog.dialog('close');
    }
  }

  /**
   * Hide edit forms (legacy function for inline forms)
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

  /**
   * SortableTable class for drag & drop reordering
   */
  class SortableTable {
    constructor(selector, config) {
      this.table = document.querySelector(selector);
      this.config = config;
      this.draggedRow = null;
      this.placeholder = null;

      if (this.table) {
        this.init();
      }
    }

    init() {
      this.addDragHandles();
      this.bindEvents();
      console.log('SortableTable: Initialization complete');
    }

    addDragHandles() {
      const tbody = this.table.querySelector('tbody');

      if (!tbody) return;

      // Make rows draggable and ensure drag handles are properly configured
      const rows = tbody.querySelectorAll('tr');

      rows.forEach((row, index) => {
        // Make the entire row draggable
        row.draggable = true;
        row.style.cursor = 'move';

        // The drag handles are already in the HTML, just ensure they have proper styling
        const dragHandle = row.querySelector('.drag-handle');
        if (dragHandle) {
          dragHandle.style.cursor = 'grab';
          dragHandle.addEventListener('mousedown', () => {
            dragHandle.style.cursor = 'grabbing';
          });
          dragHandle.addEventListener('mouseup', () => {
            dragHandle.style.cursor = 'grab';
          });
        }
      });
    }

    bindEvents() {
      const tbody = this.table.querySelector('tbody');
      if (!tbody) return;

      tbody.addEventListener('dragstart', this.handleDragStart.bind(this));
      tbody.addEventListener('dragover', this.handleDragOver.bind(this));
      tbody.addEventListener('dragenter', this.handleDragEnter.bind(this));
      tbody.addEventListener('drop', this.handleDrop.bind(this));
      tbody.addEventListener('dragend', this.handleDragEnd.bind(this));
    }

    handleDragStart(e) {
      // Check if we're dragging a table row
      if (e.target.tagName.toLowerCase() === 'tr') {
        this.draggedRow = e.target;

        // Add visual feedback to the dragged row
        e.target.classList.add('dragging');
        e.target.style.opacity = '0.5';

        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('text/html', e.target.outerHTML);
      } else {
        // Prevent drag if not initiated from a row
        e.preventDefault();
      }
    }

    handleDragOver(e) {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';

      if (!this.draggedRow) return;

      // Remove any existing drop indicators
      this.clearDropIndicators();

      const tbody = this.table.querySelector('tbody');
      const afterElement = this.getDragAfterElement(e.clientY);

      if (afterElement) {
        // Find the previous visible row (not the dragged row)
        let previousVisibleRow = afterElement.previousElementSibling;
        while (
          previousVisibleRow &&
          (previousVisibleRow === this.draggedRow ||
            previousVisibleRow.classList.contains('dragging'))
        ) {
          previousVisibleRow = previousVisibleRow.previousElementSibling;
        }

        if (!previousVisibleRow) {
          // Dropping above first visible row - highlight first row's top border
          afterElement.classList.add('drop-indicator-first');
        } else {
          // Dropping above a non-first row - highlight the row above's bottom border
          previousVisibleRow.classList.add('drop-indicator-bottom');
        }
      } else {
        // Dropping at the end - highlight last row's bottom border
        const lastRow = tbody.querySelector('tr:last-child:not(.dragging)');
        if (lastRow) {
          lastRow.classList.add('drop-indicator-last');
        }
      }
    }

    handleDragEnter(e) {
      e.preventDefault();
    }

    handleDrop(e) {
      e.preventDefault();

      if (!this.draggedRow) return;

      // Clear all drop indicators
      this.clearDropIndicators();

      // Find the target position
      const afterElement = this.getDragAfterElement(e.clientY);
      const tbody = this.table.querySelector('tbody');

      if (!tbody) return;

      // Move the dragged row to the new position
      if (afterElement) {
        tbody.insertBefore(this.draggedRow, afterElement);
      } else {
        tbody.appendChild(this.draggedRow);
      }

      // Reset styles
      this.draggedRow.style.opacity = '';
      this.draggedRow.classList.remove('dragging');

      this.updateOrder();
    }

    handleDragEnd(e) {
      // Clean up
      if (this.draggedRow) {
        this.draggedRow.style.opacity = '';
        this.draggedRow.classList.remove('dragging');
      }

      // Clear all drop indicators
      this.clearDropIndicators();

      this.draggedRow = null;
    }

    getDragAfterElement(y) {
      const tbody = this.table.querySelector('tbody');
      const draggableElements = [
        ...tbody.querySelectorAll('tr:not(.dragging)'),
      ];

      return draggableElements.reduce(
        (closest, child) => {
          if (child === this.draggedRow) return closest;

          const box = child.getBoundingClientRect();
          const offset = y - box.top - box.height / 2;

          if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
          } else {
            return closest;
          }
        },
        { offset: Number.NEGATIVE_INFINITY },
      ).element;
    }

    clearDropIndicators() {
      const tbody = this.table.querySelector('tbody');
      if (tbody) {
        const indicators = tbody.querySelectorAll(
          '.drop-indicator-top, .drop-indicator-bottom, .drop-indicator-first, .drop-indicator-last',
        );
        indicators.forEach((indicator) => {
          indicator.classList.remove(
            'drop-indicator-top',
            'drop-indicator-bottom',
            'drop-indicator-first',
            'drop-indicator-last',
          );
        });
      }
    }

    updateOrder() {
      const tbody = this.table.querySelector('tbody');
      const rows = tbody.querySelectorAll('tr:not(.dragging)');
      const orderedIds = [];

      rows.forEach((row, index) => {
        if (this.config.type === 'apps') {
          // For apps, look for data-app-id attribute
          const appId = row.getAttribute('data-app-id');
          if (appId) {
            orderedIds.push(appId);
          }
        } else if (this.config.type === 'videos') {
          // For videos, look for data-video-id attribute
          const videoId = row.getAttribute('data-video-id');
          if (videoId) {
            orderedIds.push(videoId);
          }
        }
      });

      this.sendOrderUpdate(orderedIds);
    }

    sendOrderUpdate(orderedIds) {
      // Use GET request with query parameters (AJAX)
      const params = new URLSearchParams();
      params.append('ordered_ids', orderedIds.join(','));
      params.append('nonce', $('input[name="dt_home_admin_nonce"]').val());
      const url = `${this.config.endpoint}&${params.toString()}`;

      // Show a loading message
      this.showMessage('Saving order...', 'success');

      fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
          'X-Requested-With': 'XMLHttpRequest',
        },
      })
        .then((response) => {
          if (response.ok) {
            this.showMessage('Order saved!', 'success');
          } else {
            this.showMessage('Failed to save order.', 'error');
          }
        })
        .catch(() => {
          this.showMessage('Failed to save order.', 'error');
        });
    }

    showMessage(text, type) {
      // Create or update message element
      let messageEl = document.querySelector('.sortable-message');
      if (!messageEl) {
        messageEl = document.createElement('div');
        messageEl.className = 'sortable-message';
        messageEl.style.position = 'fixed';
        messageEl.style.bottom = '20px';
        messageEl.style.right = '20px';
        messageEl.style.top = '';
        messageEl.style.padding = '14px 28px';
        messageEl.style.borderRadius = '6px';
        messageEl.style.color = 'white';
        messageEl.style.fontWeight = 'bold';
        messageEl.style.fontSize = '1.25rem';
        messageEl.style.zIndex = '9999';
        document.body.appendChild(messageEl);
      }

      messageEl.textContent = text;
      messageEl.style.backgroundColor =
        type === 'success' ? '#46b450' : '#dc3232';
      messageEl.style.display = 'block';

      // Auto-hide after 3 seconds
      setTimeout(() => {
        messageEl.style.display = 'none';
      }, 3000);
    }
  }

  // Initialize drag and drop functionality
  function initializeDragAndDrop() {
    // Check if we're on the home screen admin page
    const urlParams = new URLSearchParams(window.location.search);
    const page = urlParams.get('page');
    const tab = urlParams.get('tab');

    if (page === 'dt_options' && tab === 'home_screen') {
      // Wait a bit for the DOM to fully load
      setTimeout(() => {
        // Find apps table
        const appsTable = document.querySelector('table[data-type="apps"]');
        if (appsTable) {
          new SortableTable('table[data-type="apps"]', {
            type: 'apps',
            endpoint: ajaxurl + '?action=dt_home_reorder_apps',
          });
        }

        // Find videos table
        const videosTable = document.querySelector('table[data-type="videos"]');
        if (videosTable) {
          new SortableTable('table[data-type="videos"]', {
            type: 'videos',
            endpoint: ajaxurl + '?action=dt_home_reorder_videos',
          });
        }
      }, 100);
    }
  }

  // Initialize drag and drop when document is ready
  initializeDragAndDrop();
});

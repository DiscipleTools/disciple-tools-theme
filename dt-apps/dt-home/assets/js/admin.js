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
    // Handle icon input changes for live preview
    $(document).on('input', 'input[name="app_icon"]', function () {
      updateIconPreview($(this));
    });

    // Let dt-options.js handle all icon button clicks - no custom handling needed
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

    // Handle role selection toggle
    $(document).on('change', 'input[name="app_user_roles_type"]', function () {
      const isSpecificRoles = $(this).val() === 'support_specific_roles';
      $('.app-roles-selection').toggle(isSpecificRoles);
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
      title: $appRow.find('td:nth-child(2) strong').text(), // Title is now in 2nd column
      description: $appRow.find('td:nth-child(3)').text(), // Description is now in 3rd column
      url: $appRow.find('td:nth-child(5)').text(), // URL is now in 5th column
      icon:
        $appRow.find('td:nth-child(4) i').attr('class') ||
        $appRow.find('td:nth-child(4) img').attr('src') ||
        '', // Icon is now in 4th column
      color: $appRow.data('color') || '#667eea',
      enabled: $appRow.find('.status-enabled').length > 0,
      user_roles_type: $appRow.data('user-roles-type') || 'support_all_roles',
      roles: $appRow.data('roles') || [],
    };

    // Create edit form
    const editForm = createAppEditForm(appData);

    // Insert after the app row
    $appRow.after(editForm);

    // Load roles for the edit form
    loadRolesForEditForm(editForm, appData.roles);

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
      title: $videoRow.find('td:nth-child(2) strong').text(), // Title is in 2nd column
      description: $videoRow.find('td:nth-child(3)').text(), // Description is in 3rd column
      duration: $videoRow.find('td:nth-child(4)').text(), // Duration is in 4th column
      category: $videoRow.find('td:nth-child(5)').text().toLowerCase(), // Category is in 5th column
      video_url: $videoRow.data('video-url') || '', // Video URL from data attribute
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
   * Update icon preview
   */
  function updateIconPreview($input) {
    const iconValue = $input.val();

    // Look for wrapper in the same table row (for nested table structure)
    let $wrapper = $input.closest('tr').find('.field-icon-wrapper');

    // If not found, look in the parent table row
    if ($wrapper.length === 0) {
      $wrapper = $input
        .closest('table')
        .closest('tr')
        .find('.field-icon-wrapper');
    }

    // If still not found, look in the entire form
    if ($wrapper.length === 0) {
      const formName = $input.closest('form').attr('name');
      $wrapper = $(`form[name="${formName}"] .field-icon-wrapper`);
    }

    if ($wrapper.length) {
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
  }

  /**
   * Create app edit form
   */
  function createAppEditForm(appData) {
    const userRolesType = appData.user_roles_type || 'support_all_roles';
    const selectedRoles = appData.roles || [];

    return $(`
            <tr class="edit-form-row" style="background-color: #f0f8ff; border: 2px solid #667eea;">
                <td colspan="8">
                    <div class="edit-form-container" style="padding: 20px;">
                        <h4 style="margin-top: 0; color: #667eea;">Edit App: ${appData.title}</h4>
                        <form method="post" class="app-edit-form" name="dt_home_app_form_edit_${appData.id}">
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
                                    <td>
                                        <table>
                                            <tbody>
                                                <tr>
                                                    <td>
                                                        <span class="field-icon-wrapper">
                                                            ${
                                                              appData.icon &&
                                                              appData.icon
                                                                .trim()
                                                                .toLowerCase()
                                                                .startsWith(
                                                                  'mdi',
                                                                )
                                                                ? `<i class="${appData.icon} field-icon" style="font-size: 20px; vertical-align: middle;"></i>`
                                                                : `<img src="${appData.icon}" class="field-icon" style="width: 20px; height: 20px; vertical-align: middle;" />`
                                                            }
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <input type="text" name="app_icon" value="${appData.icon}" class="regular-text" />
                                                    </td>
                                                    <td>
                                                        <button type="button" class="button change-icon-button" data-form="dt_home_app_form_edit_${appData.id}" data-icon-input="app_icon">Change Icon</button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row">Color</th>
                                    <td><input type="color" name="app_color" value="${appData.color}" /></td>
                                </tr>
                                <tr>
                                    <th scope="row">Enabled</th>
                                    <td><input type="checkbox" name="app_enabled" ${appData.enabled ? 'checked' : ''} /></td>
                                </tr>
                                <tr>
                                    <th scope="row">User Access</th>
                                    <td>
                                        <label>
                                            <input type="radio" name="app_user_roles_type" value="support_all_roles" ${userRolesType === 'support_all_roles' ? 'checked' : ''} />
                                            All roles have access
                                        </label>
                                        <br>
                                        <label>
                                            <input type="radio" name="app_user_roles_type" value="support_specific_roles" ${userRolesType === 'support_specific_roles' ? 'checked' : ''} />
                                            Limit access by role
                                        </label>
                                    </td>
                                </tr>
                                <tr class="app-roles-selection" style="display: ${userRolesType === 'support_specific_roles' ? 'table-row' : 'none'};">
                                    <th scope="row">Select Roles</th>
                                    <td>
                                        <div class="roles-checkboxes" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                                            <p><em>Loading roles...</em></p>
                                        </div>
                                    </td>
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
   * Create video edit form
   */
  function createVideoEditForm(videoData) {
    return $(`
            <tr class="edit-form-row" style="background-color: #f0f8ff; border: 2px solid #667eea;">
                <td colspan="7">
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
                                    <td><input type="url" name="video_url" value="${videoData.video_url}" class="regular-text" placeholder="https://youtube.com/watch?v=..." /></td>
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

      const afterElement = this.getDragAfterElement(e.clientY);

      if (afterElement) {
        // Add drop indicator above the target element
        afterElement.classList.add('drop-indicator-top');
      } else {
        // Add drop indicator at the bottom of the table
        const tbody = this.table.querySelector('tbody');
        const lastRow = tbody.querySelector('tr:last-child');
        if (lastRow) {
          lastRow.classList.add('drop-indicator-bottom');
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
          '.drop-indicator-top, .drop-indicator-bottom',
        );
        indicators.forEach((indicator) => {
          indicator.classList.remove(
            'drop-indicator-top',
            'drop-indicator-bottom',
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

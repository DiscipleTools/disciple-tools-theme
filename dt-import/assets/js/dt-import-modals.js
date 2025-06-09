/* global dtImport */
(function ($) {
  'use strict';

  // DT Import Modal Handlers
  class DTImportModals {
    constructor(dtImportInstance) {
      this.dtImport = dtImportInstance;
      this.bindModalEvents();
    }

    bindModalEvents() {
      // Create field modal events
      $(document).on('click', '.create-field-btn', (e) =>
        this.showCreateFieldModal(e),
      );
      $(document).on('click', '.save-field-btn', () =>
        this.createCustomField(),
      );
      $(document).on('click', '.cancel-field-btn', () => this.closeModals());

      // Handle field mapping dropdown selection
      $(document).on('change', '.field-mapping-select', (e) => {
        const $select = $(e.target);
        const fieldKey = $select.val();
        const columnIndex = $select.data('column-index');

        if (fieldKey === 'create_new') {
          // Reset to empty selection and show modal
          $select.val('');
          this.showCreateFieldModal(columnIndex);
        } else {
          // Handle regular field mapping
          this.handleFieldMapping(columnIndex, fieldKey);
        }
      });

      // General modal events
      $(document).on('click', '.modal-close, .modal-overlay', (e) => {
        if (e.target === e.currentTarget) {
          this.closeModals();
        }
      });

      // ESC key to close modals
      $(document).on('keydown', (e) => {
        if (e.key === 'Escape') {
          this.closeModals();
        }
      });
    }

    showCreateFieldModal(columnIndex) {
      const modalHtml = `
                <div class="dt-import-modal">
                    <div class="modal-overlay"></div>
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3>${dtImport.translations.createNewField}</h3>
                            <button type="button" class="modal-close">&times;</button>
                        </div>
                        <div class="modal-body">
                            <form id="create-field-form">
                                <table class="form-table">
                                    <tr>
                                        <th scope="row">
                                            <label for="new-field-name">${dtImport.translations.fieldName} *</label>
                                        </th>
                                        <td>
                                            <input type="text" id="new-field-name" name="field_name" required class="regular-text">
                                            <p class="description">The display name for this field.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="new-field-type">${dtImport.translations.fieldType} *</label>
                                        </th>
                                        <td>
                                            <select id="new-field-type" name="field_type" required>
                                                <option value="">Select field type...</option>
                                                ${this.getFieldTypeOptions()}
                                            </select>
                                            <p class="description">The type of data this field will store.</p>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th scope="row">
                                            <label for="new-field-description">${dtImport.translations.fieldDescription}</label>
                                        </th>
                                        <td>
                                            <textarea id="new-field-description" name="field_description" rows="3" class="large-text"></textarea>
                                            <p class="description">Optional description for this field.</p>
                                        </td>
                                    </tr>
                                    <tr id="field-options-row" style="display: none;">
                                        <th scope="row">
                                            <label for="field-options">Field Options</label>
                                        </th>
                                        <td>
                                            <div id="field-options-container">
                                                <p class="description">Add options for dropdown or multi-select fields:</p>
                                                <div class="field-options-list">
                                                    <!-- Options will be added here -->
                                                </div>
                                                <button type="button" class="button add-option-btn">Add Option</button>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                                <input type="hidden" name="post_type" value="${this.dtImport.selectedPostType}">
                                <input type="hidden" name="column_index" value="${columnIndex}">
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="button cancel-field-btn">Cancel</button>
                            <button type="button" class="button button-primary save-field-btn">${dtImport.translations.createField}</button>
                        </div>
                    </div>
                </div>
            `;

      $('body').append(modalHtml);

      // Handle field type change
      $('#new-field-type').on('change', (e) => {
        const fieldType = $(e.target).val();
        if (['key_select', 'multi_select'].includes(fieldType)) {
          $('#field-options-row').show();
        } else {
          $('#field-options-row').hide();
        }
      });

      // Handle add option button
      $('.add-option-btn').on('click', () => this.addFieldOption());
    }

    getFieldTypeOptions() {
      return Object.entries(dtImport.fieldTypes)
        .map(([key, label]) => {
          return `<option value="${key}">${label}</option>`;
        })
        .join('');
    }

    addFieldOption(key = '', label = '') {
      const optionIndex = $('.field-option-row').length;
      const optionHtml = `
                <div class="field-option-row" style="margin-bottom: 10px;">
                    <input type="text" placeholder="Option key" name="option_keys[]" value="${key}" style="width: 200px; margin-right: 10px;">
                    <input type="text" placeholder="Option label" name="option_labels[]" value="${label}" style="width: 200px; margin-right: 10px;">
                    <button type="button" class="button remove-option-btn">Remove</button>
                </div>
            `;

      $('.field-options-list').append(optionHtml);

      // Handle remove option
      $('.remove-option-btn')
        .last()
        .on('click', function () {
          $(this).parent().remove();
        });
    }

    createCustomField() {
      const formData = new FormData(
        document.getElementById('create-field-form'),
      );
      const fieldData = {
        name: formData.get('field_name'),
        type: formData.get('field_type'),
        description: formData.get('field_description') || '',
        post_type: formData.get('post_type'),
        column_index: formData.get('column_index'),
      };

      // Validate required fields
      if (!fieldData.name || !fieldData.type) {
        this.showModalError(dtImport.translations.fillRequiredFields);
        return;
      }

      // Add field options if applicable
      if (['key_select', 'multi_select'].includes(fieldData.type)) {
        const optionKeys = formData.getAll('option_keys[]');
        const optionLabels = formData.getAll('option_labels[]');

        fieldData.options = {};
        optionKeys.forEach((key, index) => {
          if (key && optionLabels[index]) {
            fieldData.options[key] = optionLabels[index];
          }
        });
      }

      // Show loading state
      $('.save-field-btn')
        .prop('disabled', true)
        .text(dtImport.translations.creating);

      // Create field via DT's existing REST API
      const createFieldData = new FormData();
      createFieldData.append('new_field_name', fieldData.name);
      createFieldData.append('new_field_type', fieldData.type);
      createFieldData.append('post_type', fieldData.post_type);
      createFieldData.append('tile_key', 'other');

      fetch(
        `${dtImport.restUrl.replace('dt-csv-import/v2/', '')}dt-admin-settings/new-field`,
        {
          method: 'POST',
          headers: {
            'X-WP-Nonce': dtImport.nonce,
          },
          body: createFieldData,
        },
      )
        .then((response) => response.json())
        .then((data) => {
          if (data && data.key) {
            const fieldKey = data.key;

            // If this is a select field with options, create the field options
            if (
              ['key_select', 'multi_select'].includes(fieldData.type) &&
              Object.keys(fieldData.options || {}).length > 0
            ) {
              this.createFieldOptions(
                fieldData.post_type,
                fieldKey,
                fieldData.options,
              )
                .then(() => {
                  this.handleFieldCreationSuccess(fieldData, fieldKey);
                })
                .catch((error) => {
                  console.error('Error creating field options:', error);
                  // Field was created but options failed - still show success
                  this.handleFieldCreationSuccess(fieldData, fieldKey);
                });
            } else {
              this.handleFieldCreationSuccess(fieldData, fieldKey);
            }
          } else {
            this.showModalError(dtImport.translations.fieldCreationError);
          }
        })
        .catch((error) => {
          console.error('Field creation error:', error);
          this.showModalError(dtImport.translations.ajaxError);
        })
        .finally(() => {
          $('.save-field-btn')
            .prop('disabled', false)
            .text(dtImport.translations.createField);
        });
    }

    createFieldOptions(postType, fieldKey, fieldOptions) {
      const promises = [];

      Object.entries(fieldOptions).forEach(([optionKey, optionLabel]) => {
        const optionData = new FormData();
        optionData.append('post_type', postType);
        optionData.append('tile_key', 'other');
        optionData.append('field_key', fieldKey);
        optionData.append('field_option_name', optionLabel);
        optionData.append('field_option_description', '');
        optionData.append('field_option_key', optionKey);

        const promise = fetch(
          `${dtImport.restUrl.replace('dt-csv-import/v2/', '')}dt-admin-settings/new-field-option`,
          {
            method: 'POST',
            headers: {
              'X-WP-Nonce': dtImport.nonce,
            },
            body: optionData,
          },
        );

        promises.push(promise);
      });

      return Promise.all(promises);
    }

    handleFieldCreationSuccess(fieldData, fieldKey) {
      // Update the field mapping dropdown
      this.updateFieldMappingDropdown(
        fieldData.column_index,
        fieldKey,
        fieldData.name,
        fieldData.type,
        fieldData.options || {},
      );

      // Show success message
      this.showModalSuccess(dtImport.translations.fieldCreatedSuccess);

      // Close modal
      this.closeModals();
    }

    updateFieldMappingDropdown(
      columnIndex,
      fieldKey,
      fieldName,
      fieldType,
      fieldOptions = {},
    ) {
      const $select = $(
        `.field-mapping-select[data-column-index="${columnIndex}"]`,
      );

      // Add new option before "Create New Field"
      const newOption = `<option value="${fieldKey}" data-field-type="${fieldType}" selected>${fieldName} (${fieldType})</option>`;
      $select.find('option[value="create_new"]').before(newOption);

      // Clear the frontend cache to force refresh of field settings
      this.dtImport.cachedFieldSettings = null;

      // Update cached field settings with the new field
      const fieldSettings = this.dtImport.getFieldSettingsForPostType();
      if (fieldSettings) {
        const fieldConfig = {
          name: fieldName,
          type: fieldType,
        };

        // Add field options for select fields
        if (
          ['key_select', 'multi_select'].includes(fieldType) &&
          Object.keys(fieldOptions).length > 0
        ) {
          fieldConfig.default = {};
          Object.entries(fieldOptions).forEach(([key, label]) => {
            fieldConfig.default[key] = { label: label };
          });
        }

        // Update the cached settings with the new field
        if (this.dtImport.cachedFieldSettings) {
          this.dtImport.cachedFieldSettings[fieldKey] = fieldConfig;
        }
      }

      // Manually update the field mapping without triggering change event to avoid infinite loop
      this.dtImport.fieldMappings[columnIndex] = {
        field_key: fieldKey,
        column_index: columnIndex,
      };

      // Update field specific options and summary
      // For newly created select fields, pass the options directly instead of fetching from server
      if (
        ['key_select', 'multi_select'].includes(fieldType) &&
        Object.keys(fieldOptions).length > 0
      ) {
        const fieldConfig = { name: fieldName, type: fieldType };
        this.dtImport.showInlineValueMappingWithOptions(
          columnIndex,
          fieldKey,
          fieldConfig,
          fieldOptions,
        );
      } else {
        this.dtImport.showFieldSpecificOptions(columnIndex, fieldKey);
      }
      this.dtImport.updateMappingSummary();
    }

    showModalError(message) {
      // Use toast notification instead of inline modal error
      if (this.dtImport && this.dtImport.showError) {
        this.dtImport.showError(message);
      } else {
        // Fallback to inline modal error if toast is not available
        // Remove existing error messages
        $('.modal-error').remove();

        // Add error message to modal
        $('.modal-body').prepend(`
                  <div class="notice notice-error modal-error" style="margin-bottom: 15px;">
                      <p>${this.escapeHtml(message)}</p>
                  </div>
              `);
      }
    }

    showModalSuccess(message) {
      // Use toast notification for success messages
      if (this.dtImport && this.dtImport.showSuccess) {
        this.dtImport.showSuccess(message);
      }
    }

    closeModals() {
      $('.dt-import-modal').remove();
    }

    escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    handleFieldMapping(columnIndex, fieldKey) {
      // Store mapping in the main DTImport instance
      if (fieldKey) {
        this.dtImport.fieldMappings[columnIndex] = {
          field_key: fieldKey,
          column_index: columnIndex,
        };
      } else {
        delete this.dtImport.fieldMappings[columnIndex];
      }

      // Show field-specific options if needed
      this.dtImport.showFieldSpecificOptions(columnIndex, fieldKey);
      this.dtImport.updateMappingSummary();
    }
  }

  // Documentation Modal Handler
  class DTImportDocumentationModal {
    constructor() {
      this.bindEvents();
    }

    bindEvents() {
      // Show documentation modal
      $(document).on('click', '#dt-import-view-docs', (e) => {
        e.preventDefault();
        this.showDocumentationModal();
      });

      // Handle tab switching
      $(document).on('click', '.dt-import-docs-tabs a', (e) => {
        e.preventDefault();
        const targetTab = $(e.target).attr('href').substring(1);
        this.switchTab(targetTab);
      });

      // Close modal events
      $(document).on(
        'click',
        '#dt-import-docs-close, #dt-import-docs-close-btn',
        (e) => {
          e.preventDefault();
          this.closeDocumentationModal();
        },
      );

      // Close on overlay click
      $(document).on('click', '#dt-import-documentation-modal', (e) => {
        if (e.target === e.currentTarget) {
          this.closeDocumentationModal();
        }
      });

      // Close on escape key
      $(document).on('keydown', (e) => {
        if (
          e.key === 'Escape' &&
          $('#dt-import-documentation-modal').is(':visible')
        ) {
          this.closeDocumentationModal();
        }
      });
    }

    showDocumentationModal() {
      const $modal = $('#dt-import-documentation-modal');
      if ($modal.length) {
        $modal.fadeIn(300);
        $('body').addClass('modal-open');

        // Focus management for accessibility
        $modal.find('.dt-import-modal-close').focus();
      }
    }

    closeDocumentationModal() {
      const $modal = $('#dt-import-documentation-modal');
      $modal.fadeOut(300, () => {
        $('body').removeClass('modal-open');
      });
    }

    switchTab(targetTab) {
      // Update tab navigation
      $('.dt-import-docs-tabs a').removeClass('active');
      $(`.dt-import-docs-tabs a[href="#${targetTab}"]`).addClass('active');

      // Show/hide tab content
      $('.dt-import-docs-tab-content').removeClass('active');
      $(`#${targetTab}`).addClass('active');
    }
  }

  // Extend the main DTImport class with modal functionality
  $(document).ready(() => {
    if (window.dtImportInstance) {
      window.dtImportInstance.modals = new DTImportModals(
        window.dtImportInstance,
      );

      // Add modal methods to main instance
      window.dtImportInstance.showCreateFieldModal = (columnIndex) => {
        window.dtImportInstance.modals.showCreateFieldModal(columnIndex);
      };

      window.dtImportInstance.closeModals = () => {
        window.dtImportInstance.modals.closeModals();
      };
    }

    // Initialize documentation modal
    window.dtImportDocumentationModal = new DTImportDocumentationModal();
  });
})(jQuery);

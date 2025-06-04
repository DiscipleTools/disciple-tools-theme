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

      // Create field via REST API
      fetch(`${dtImport.restUrl}${fieldData.post_type}/create-field`, {
        method: 'POST',
        headers: {
          'X-WP-Nonce': dtImport.nonce,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(fieldData),
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // Update the field mapping dropdown
            this.updateFieldMappingDropdown(
              fieldData.column_index,
              data.data.field_key,
              data.data.field_name,
              fieldData.type,
            );

            // Show success message
            this.dtImport.showSuccess(
              dtImport.translations.fieldCreatedSuccess,
            );

            // Close modal
            this.closeModals();
          } else {
            this.showModalError(
              data.message || dtImport.translations.fieldCreationError,
            );
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

    updateFieldMappingDropdown(columnIndex, fieldKey, fieldName, fieldType) {
      const $select = $(
        `.field-mapping-select[data-column-index="${columnIndex}"]`,
      );

      // Add new option before "Create New Field"
      const newOption = `<option value="${fieldKey}" data-field-type="${fieldType}" selected>${fieldName} (${fieldType})</option>`;
      $select.find('option[value="create_new"]').before(newOption);

      // Manually update the field mapping without triggering change event to avoid infinite loop
      this.dtImport.fieldMappings[columnIndex] = {
        field_key: fieldKey,
        column_index: columnIndex,
      };

      // Update field specific options and summary
      this.dtImport.showFieldSpecificOptions(columnIndex, fieldKey);
      this.dtImport.updateMappingSummary();
    }

    showModalError(message) {
      // Remove existing error messages
      $('.modal-error').remove();

      // Add error message to modal
      $('.modal-body').prepend(`
                <div class="notice notice-error modal-error" style="margin-bottom: 15px;">
                    <p>${this.escapeHtml(message)}</p>
                </div>
            `);
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
  });
})(jQuery);

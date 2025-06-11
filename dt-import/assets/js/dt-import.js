/* global dtImport */
(function ($) {
  'use strict';

  // DT Import main class
  class DTImport {
    constructor() {
      this.currentStep = 1;
      this.sessionId = null;
      this.selectedPostType = null;
      this.csvData = null;
      this.fieldMappings = {};
      this.doNotImportColumns = new Set(); // Track columns explicitly set to "Do not import"
      this.isProcessing = false;
      this.cachedFieldSettings = null;

      this.init();
    }

    init() {
      this.bindEvents();
      this.loadPostTypes();
      this.updateStepIndicator();
    }

    bindEvents() {
      // Navigation buttons
      $('.dt-import-next').on('click', () => this.nextStep());
      $('.dt-import-back').on('click', () => this.previousStep());

      // Post type selection
      $(document).on('click', '.post-type-card', (e) => this.selectPostType(e));

      // File upload change event only
      $(document).on('change', '#csv-file-input', (e) =>
        this.handleFileSelect(e),
      );

      // Drag and drop
      $(document).on('dragover', '.file-upload-area', (e) =>
        this.handleDragOver(e),
      );
      $(document).on('drop', '.file-upload-area', (e) =>
        this.handleFileDrop(e),
      );

      // Field mapping selection
      $(document).on('change', '.field-mapping-select', (e) =>
        this.handleFieldMapping(e),
      );

      // Geocoding service selection
      $(document).on('change', '.geocoding-service-checkbox', (e) =>
        this.handleGeocodingServiceChange(e),
      );

      // Duplicate checking selection
      $(document).on('change', '.duplicate-checking-checkbox', (e) =>
        this.handleDuplicateCheckingChange(e),
      );

      // Date format selection
      $(document).on('change', '.date-format-select', (e) =>
        this.handleDateFormatChange(e),
      );

      // Inline value mapping events
      $(document).on('change', '.inline-value-mapping-select', (e) =>
        this.handleInlineValueMappingChange(e),
      );
      $(document).on('click', '.auto-map-inline-btn', (e) =>
        this.handleAutoMapInline(e),
      );
      $(document).on('click', '.clear-inline-btn', (e) =>
        this.handleClearInline(e),
      );

      // Import execution
      $(document).on('click', '.execute-import-btn', () =>
        this.executeImport(),
      );
    }

    // Step 1: Post Type Selection
    loadPostTypes() {
      const postTypesHtml = dtImport.postTypes
        .map(
          (postType) => `
                <div class="post-type-card" data-post-type="${postType.key}">
                    <div class="post-type-icon">
                        <i class="mdi mdi-${this.getPostTypeIcon(postType.key)}"></i>
                    </div>
                    <h3>${postType.label_plural}</h3>
                    <p>${postType.description}</p>
                    <div class="post-type-meta">
                        <span class="post-type-singular">${postType.label_singular}</span>
                    </div>
                </div>
            `,
        )
        .join('');

      $('.post-type-grid').html(postTypesHtml);
    }

    selectPostType(e) {
      const $card = $(e.currentTarget);
      const postType = $card.data('post-type');

      $('.post-type-card').removeClass('selected');
      $card.addClass('selected');

      this.selectedPostType = postType;

      // Clear cached field settings when post type changes
      this.cachedFieldSettings = null;

      // Clear field mappings and do not import tracking when post type changes
      this.fieldMappings = {};
      this.doNotImportColumns.clear();

      $('.dt-import-next').prop('disabled', false);

      // Automatically proceed to step 2
      this.showStep2();
    }

    // Step 2: File Upload
    nextStep() {
      if (this.isProcessing) return;

      switch (this.currentStep) {
        case 1:
          if (!this.selectedPostType) {
            this.showError(dtImport.translations.selectPostType);
            return;
          }
          this.showStep2();
          break;
        case 2:
          if (!this.csvData) {
            this.showError(dtImport.translations.noFileSelected);
            return;
          }
          this.analyzeCSV();
          break;
        case 3:
          this.saveFieldMappings();
          break;
        case 4:
          this.executeImport();
          break;
      }
    }

    previousStep() {
      if (this.isProcessing) return;

      this.currentStep--;
      this.updateStepIndicator();
      this.showCurrentStep();
      this.updateNavigation();
    }

    showCurrentStep() {
      switch (this.currentStep) {
        case 1:
          this.loadPostTypes();
          this.showStep1();
          break;
        case 2:
          this.showStep2();
          break;
        case 3:
          // Need to re-analyze CSV to show step 3
          if (this.sessionId) {
            this.analyzeCSV();
          }
          break;
        case 4:
          this.showStep4();
          break;
      }
    }

    showStep1() {
      const step1Html = `
                <div class="dt-import-initial-content">
                    <h2>Step 1: Select Record Type</h2>
                    <p>Choose the type of records you want to import from your CSV file.</p>
                    
                    <div class="post-type-grid">
                        <!-- Post type cards will be dynamically populated -->
                    </div>
                </div>
            `;

      $('.dt-import-step-content').html(step1Html);
      this.loadPostTypes();

      // Restore selected post type if any
      if (this.selectedPostType) {
        $(
          `.post-type-card[data-post-type="${this.selectedPostType}"]`,
        ).addClass('selected');
      }
    }

    showStep2() {
      this.currentStep = 2;
      this.updateStepIndicator();

      const step2Html = `
                <div class="dt-import-step-content">
                    <h2>${dtImport.translations.uploadCsv}</h2>
                    <p>Upload a CSV file containing ${this.getPostTypeLabel()} data.</p>
                    
                    <div class="file-upload-section">
                        <div class="file-upload-area">
                            <div class="upload-icon">
                                <i class="mdi mdi-cloud-upload"></i>
                            </div>
                            <div class="upload-text">
                                <h3>${dtImport.translations.chooseFile}</h3>
                                <p>${dtImport.translations.dragDropFile}</p>
                            </div>
                        </div>
                        <input type="file" id="csv-file-input" accept=".csv" style="display: none;">
                        
                        <div class="file-info" style="display: none;">
                            <div class="file-details">
                                <h4></h4>
                                <p class="file-size"></p>
                                <p class="file-rows"></p>
                            </div>
                            <div class="file-actions">
                                <button type="button" class="button change-file-btn">Change File</button>
                            </div>
                        </div>
                    </div>

                    <div class="upload-options">
                        <h3>CSV Options</h3>
                        <table class="form-table">
                            <tr>
                                <th><label for="csv-delimiter">Delimiter</label></th>
                                <td>
                                    <select id="csv-delimiter">
                                        <option value=",">${dtImport.translations.comma}</option>
                                        <option value=";">${dtImport.translations.semicolon}</option>
                                        <option value="\t">${dtImport.translations.tab}</option>
                                        <option value="|">${dtImport.translations.pipe}</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="csv-encoding">Encoding</label></th>
                                <td>
                                    <select id="csv-encoding">
                                        <option value="UTF-8">UTF-8</option>
                                        <option value="ISO-8859-1">ISO-8859-1</option>
                                        <option value="Windows-1252">Windows-1252</option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>

                    ${this.getImportOptionsHtml()}
                </div>
                </div>
            `;

      $('.dt-import-step-content').html(step2Html);
      this.updateNavigation();

      // Bind file upload click handler after HTML is inserted
      $('.file-upload-area')
        .off('click')
        .on('click', (e) => {
          e.preventDefault();
          e.stopPropagation();
          $('#csv-file-input').get(0).click();
        });

      // Populate import options dropdowns
      this.populateImportOptions();
    }

    handleFileSelect(e) {
      const file = e.target.files[0];
      if (file) {
        this.processFile(file);
      }
    }

    handleDragOver(e) {
      e.preventDefault();
      e.stopPropagation();
      $(e.currentTarget).addClass('drag-over');
    }

    handleFileDrop(e) {
      e.preventDefault();
      e.stopPropagation();
      $(e.currentTarget).removeClass('drag-over');

      const files = e.originalEvent.dataTransfer.files;
      if (files.length > 0) {
        this.processFile(files[0]);
      }
    }

    processFile(file) {
      // Validate file
      if (!file.name.toLowerCase().endsWith('.csv')) {
        this.showError(dtImport.translations.invalidFileType);
        return;
      }

      if (file.size > dtImport.maxFileSize) {
        this.showError(
          `${dtImport.translations.fileTooLarge} ${this.formatFileSize(dtImport.maxFileSize)}`,
        );
        return;
      }

      this.showProcessing(dtImport.translations.processingFile);

      // Upload file via REST API
      const formData = new FormData();
      formData.append('csv_file', file);
      formData.append('post_type', this.selectedPostType);

      fetch(`${dtImport.restUrl}upload`, {
        method: 'POST',
        headers: {
          'X-WP-Nonce': dtImport.nonce,
        },
        credentials: 'same-origin',
        body: formData,
      })
        .then((response) => {
          return response.json().then((data) => {
            return { response, data };
          });
        })
        .then(({ response, data }) => {
          this.hideProcessing();

          if (data.success) {
            this.sessionId = data.data.session_id;
            this.csvData = data.data;
            this.showFileInfo(file, data.data);
            $('.dt-import-next').prop('disabled', false);
            this.showSuccess(dtImport.translations.fileUploaded);
          } else {
            this.showError(data.message || dtImport.translations.uploadError);
          }
        })
        .catch((error) => {
          this.hideProcessing();
          this.showError(dtImport.translations.uploadError);
          console.error('Upload error:', error);
        });
    }

    showFileInfo(file, csvData) {
      $('.file-upload-area').hide();
      $('.file-info').show();
      $('.file-info h4').text(file.name);
      $('.file-size').text(this.formatFileSize(file.size));
      $('.file-rows').text(
        `${csvData.row_count} rows, ${csvData.column_count} columns`,
      );

      $('.change-file-btn').on('click', () => {
        $('.file-upload-area').show();
        $('.file-info').hide();
        this.csvData = null;
        $('.dt-import-next').prop('disabled', true);
      });
    }

    // Step 3: Field Mapping
    analyzeCSV() {
      if (!this.sessionId) return;

      // Capture import options from Step 2 before moving to Step 3
      const assignedToVal = $('#import-assigned-to').val();
      const sourceVal = $('#import-source').val();

      // Update import options, preserving existing values if form fields are empty
      this.importOptions = {
        assigned_to:
          assignedToVal && assignedToVal !== ''
            ? assignedToVal
            : this.importOptions
              ? this.importOptions.assigned_to
              : null,
        source:
          sourceVal && sourceVal !== ''
            ? sourceVal
            : this.importOptions
              ? this.importOptions.source
              : null,
        delimiter:
          $('#csv-delimiter').val() ||
          (this.importOptions ? this.importOptions.delimiter : ','),
        encoding:
          $('#csv-encoding').val() ||
          (this.importOptions ? this.importOptions.encoding : 'UTF-8'),
      };

      this.showProcessing('Analyzing CSV columns...');

      fetch(`${dtImport.restUrl}${this.sessionId}/analyze`, {
        method: 'POST',
        headers: {
          'X-WP-Nonce': dtImport.nonce,
          'Content-Type': 'application/json',
        },
      })
        .then((response) => response.json())
        .then((data) => {
          this.hideProcessing();

          if (data.success) {
            // Handle new response format that may include saved data
            const responseData = data.data;
            let mappingSuggestions;

            if (responseData.mapping_suggestions) {
              // New format with saved data
              mappingSuggestions = responseData.mapping_suggestions;

              // Restore saved field mappings if they exist
              if (responseData.saved_field_mappings) {
                this.fieldMappings = responseData.saved_field_mappings;
              }

              // Restore saved do_not_import_columns if they exist
              if (responseData.saved_do_not_import_columns) {
                this.doNotImportColumns = new Set(
                  responseData.saved_do_not_import_columns,
                );

                // Remove any field mappings for columns that are marked as "do not import"
                // This handles cases where there might be conflicting data
                this.doNotImportColumns.forEach((columnIndex) => {
                  if (this.fieldMappings[columnIndex]) {
                    delete this.fieldMappings[columnIndex];
                  }
                });
              }
            } else {
              // Old format - just mapping suggestions
              mappingSuggestions = responseData;
            }

            this.showStep3(mappingSuggestions);
          } else {
            this.showError(data.message || 'Failed to analyze CSV');
          }
        })
        .catch((error) => {
          this.hideProcessing();
          this.showError('Failed to analyze CSV');
          console.error('Analysis error:', error);
        });
    }

    showStep3(mappingSuggestions) {
      this.currentStep = 3;
      this.updateStepIndicator();

      // Extract metadata and remove it from suggestions
      const meta = mappingSuggestions['_meta'] || {};
      delete mappingSuggestions['_meta'];

      const columnsHtml = Object.entries(mappingSuggestions)
        .map(([index, mapping]) => {
          return this.createColumnMappingCard(index, mapping);
        })
        .join('');

      const step3Html = `
                <div class="dt-import-step-content">
                    <h2>${dtImport.translations.mapFields}</h2>
                    <p>Map each CSV column to the appropriate field in Disciple.Tools.</p>
                    
                    <div class="name-field-warning" style="display: none;">
                        <div class="notice notice-error">
                            <p><strong><i class="mdi mdi-alert"></i> Name Field Required</strong></p>
                            <p>The name field is required for all imports. Please map at least one CSV column to the name field before proceeding.</p>
                        </div>
                    </div>
                    
                    <div class="mapping-container">
                        <div class="mapping-columns">
                            ${columnsHtml}
                        </div>
                    </div>
                    
                    <div class="mapping-summary" style="display: none;">
                        <h3>Mapping Summary</h3>
                        <div class="summary-stats"></div>
                    </div>
                </div>
            `;

      $('.dt-import-step-content').html(step3Html);

      // Initialize field mappings from suggested mappings ONLY if no existing mappings AND no do_not_import_columns
      if (
        Object.keys(this.fieldMappings).length === 0 &&
        this.doNotImportColumns.size === 0
      ) {
        // Initialize from suggestions for first-time display (only when no previous user choices exist)
        Object.entries(mappingSuggestions).forEach(([index, mapping]) => {
          if (mapping.suggested_field) {
            this.fieldMappings[index] = {
              field_key: mapping.suggested_field,
              column_index: parseInt(index),
            };
          }
        });
      }

      // Restore existing field mappings to the dropdowns
      setTimeout(() => {
        // First, restore any existing user mappings to the dropdowns
        Object.entries(this.fieldMappings).forEach(([columnIndex, mapping]) => {
          const $select = $(
            `.field-mapping-select[data-column-index="${columnIndex}"]`,
          );
          if ($select.length && mapping.field_key) {
            $select.val(mapping.field_key);
            // Restore field-specific options with existing configurations
            this.restoreFieldSpecificOptions(
              parseInt(columnIndex),
              mapping.field_key,
              mapping,
            );
          }
        });

        // Ensure "do not import" columns have empty dropdowns
        this.doNotImportColumns.forEach((columnIndex) => {
          const $select = $(
            `.field-mapping-select[data-column-index="${columnIndex}"]`,
          );
          if ($select.length) {
            $select.val('');
          }
        });

        // For any dropdown that doesn't have an existing mapping but has a selected value,
        // create a basic mapping (this handles auto-suggestions that weren't saved yet)
        $('.field-mapping-select').each((index, select) => {
          const $select = $(select);
          const columnIndex = $select.data('column-index');
          const fieldKey = $select.val();

          // Only create mapping if a field is selected and we don't already have a mapping
          // AND the column is not marked as "do not import"
          if (
            fieldKey &&
            fieldKey !== '' &&
            fieldKey !== 'create_new' &&
            !this.fieldMappings[columnIndex] &&
            !this.doNotImportColumns.has(columnIndex)
          ) {
            this.fieldMappings[columnIndex] = {
              field_key: fieldKey,
              column_index: parseInt(columnIndex),
            };
            // Show basic field-specific options for new mappings
            this.showFieldSpecificOptions(parseInt(columnIndex), fieldKey);
          }
          // Note: We don't modify doNotImportColumns here since we're just restoring the UI
        });

        // Update the summary and name field warning after mappings are restored
        this.updateMappingSummary();
        this.updateNameFieldWarning();
      }, 100);

      this.updateNavigation();
    }

    createColumnMappingCard(columnIndex, mapping) {
      const sampleDataHtml = mapping.sample_data
        .slice(0, 3)
        .map((sample) => `<li>${this.escapeHtml(sample)}</li>`)
        .join('');

      // Check if there's an existing user mapping for this column
      const existingMapping = this.fieldMappings[columnIndex];
      const isDoNotImport = this.doNotImportColumns.has(columnIndex);

      let selectedField;
      if (isDoNotImport) {
        // User explicitly chose "Do not import"
        selectedField = '';
      } else if (existingMapping) {
        // User has a field mapping
        selectedField = existingMapping.field_key;
      } else {
        // No user choice yet, use auto-suggestion
        selectedField = mapping.suggested_field;
      }

      // For fields with no match and no existing mapping or explicit choice, ensure empty selection
      const finalSelectedField =
        !mapping.has_match && !existingMapping && !isDoNotImport
          ? ''
          : selectedField;

      return `
                <div class="column-mapping-card" data-column-index="${columnIndex}">
                    <div class="column-header">
                        <h4>${this.escapeHtml(mapping.column_name)}</h4>
                        ${
                          !mapping.has_match && !existingMapping
                            ? `
                            <div class="confidence-indicator no-match">
                                No match found
                            </div>
                        `
                            : ''
                        }
                    </div>
                    
                    <div class="sample-data">
                        <strong>Sample data:</strong>
                        <ul>${sampleDataHtml}</ul>
                    </div>
                    
                    <div class="mapping-controls">
                        <label>Map to field:</label>
                        <select class="field-mapping-select" data-column-index="${columnIndex}">
                            <option value="">-- Do not import --</option>
                            ${this.getFieldOptions(finalSelectedField)}
                            <option value="create_new">+ Create New Field</option>
                        </select>
                        
                        <div class="field-specific-options" style="display: none;"></div>
                    </div>
                </div>
            `;
    }

    getFieldOptions(suggestedField) {
      const fieldSettings = this.getFieldSettingsForPostType();

      // Filter to ensure we have valid field configurations
      const validFields = Object.entries(fieldSettings).filter(
        ([fieldKey, fieldConfig]) => {
          return fieldConfig && fieldConfig.name && fieldConfig.type;
        },
      );

      return validFields
        .map(([fieldKey, fieldConfig]) => {
          const selected = fieldKey === suggestedField ? 'selected' : '';
          const fieldName = this.escapeHtml(fieldConfig.name);
          const fieldType = this.escapeHtml(fieldConfig.type);
          const isNameField = fieldKey === 'name';
          const nameIndicator = isNameField ? ' (Required)' : '';
          return `<option value="${fieldKey}" ${selected} data-field-type="${fieldType}">
                    ${fieldName}${nameIndicator} (${fieldType})
                </option>`;
        })
        .join('');
    }

    handleFieldMapping(e) {
      const $select = $(e.target);
      const columnIndex = $select.data('column-index');
      const fieldKey = $select.val();

      // Skip processing if create_new is selected - modals file will handle it
      if (fieldKey === 'create_new') {
        return;
      }

      // Check if we're trying to unmap the name field
      const previousMapping = this.fieldMappings[columnIndex];
      if (
        previousMapping &&
        previousMapping.field_key === 'name' &&
        (!fieldKey || fieldKey === '')
      ) {
        // Check if there's another column mapped to name
        const otherNameMapping = Object.entries(this.fieldMappings).find(
          ([idx, mapping]) =>
            idx != columnIndex && mapping.field_key === 'name',
        );

        if (!otherNameMapping) {
          // Don't allow unmapping the last name field
          this.showError(
            'The name field is required and cannot be unmapped. Please map another column to the name field first.',
          );
          $select.val(previousMapping.field_key); // Restore previous selection
          return;
        }
      }

      // Check if we're trying to map to a field that's already mapped (especially name field)
      if (fieldKey && fieldKey !== '') {
        const existingMapping = Object.entries(this.fieldMappings).find(
          ([idx, mapping]) =>
            idx != columnIndex && mapping.field_key === fieldKey,
        );

        if (existingMapping) {
          if (fieldKey === 'name') {
            // For name field, warn and ask if they want to replace
            const confirmReplace = confirm(
              `The name field is already mapped to column "${existingMapping[0]}". Do you want to replace it with this column?`,
            );

            if (!confirmReplace) {
              $select.val(previousMapping ? previousMapping.field_key : '');
              return;
            } else {
              // Remove the existing mapping
              delete this.fieldMappings[existingMapping[0]];
              // Update the UI for the previously mapped column
              $(
                `.field-mapping-select[data-column-index="${existingMapping[0]}"]`,
              ).val('');
            }
          } else {
            // For other fields, just warn and prevent duplicate mapping
            this.showError(
              `The ${fieldKey} field is already mapped to another column. Please choose a different field.`,
            );
            $select.val(previousMapping ? previousMapping.field_key : '');
            return;
          }
        }
      }

      // Store mapping - properly handle empty values for "do not import"
      if (fieldKey && fieldKey !== '') {
        this.fieldMappings[columnIndex] = {
          field_key: fieldKey,
          column_index: columnIndex,
        };
        // Remove from do not import set if it was there
        this.doNotImportColumns.delete(columnIndex);
      } else {
        // When "do not import" is selected (empty value), remove the mapping entirely
        delete this.fieldMappings[columnIndex];
        // Track that this column was explicitly set to "do not import"
        this.doNotImportColumns.add(columnIndex);
      }

      // Show field-specific options if needed
      this.showFieldSpecificOptions(columnIndex, fieldKey);
      this.updateMappingSummary();
      this.updateNameFieldWarning();
    }

    showFieldSpecificOptions(columnIndex, fieldKey) {
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );
      const $options = $card.find('.field-specific-options');

      if (!fieldKey) {
        $options.hide().empty().removeClass('connection-help');
        return;
      }

      const fieldSettings = this.getFieldSettingsForPostType();
      const fieldConfig = fieldSettings[fieldKey];

      if (!fieldConfig) {
        $options.hide().empty().removeClass('connection-help');
        return;
      }

      // Remove connection-help class first
      $options.removeClass('connection-help');

      if (['key_select', 'multi_select'].includes(fieldConfig.type)) {
        this.showInlineValueMapping(columnIndex, fieldKey, fieldConfig);
      } else if (fieldConfig.type === 'date') {
        this.showDateFormatSelector(columnIndex, fieldKey);
      } else if (fieldConfig.type === 'location_meta') {
        this.showGeocodingServiceSelector(columnIndex, fieldKey);
      } else if (
        fieldConfig.type === 'communication_channel' &&
        this.isCommunicationFieldForDuplicateCheck(fieldKey)
      ) {
        this.showDuplicateCheckingOptions(columnIndex, fieldKey, fieldConfig);
      } else if (fieldConfig.type === 'connection') {
        this.showConnectionFieldHelp(columnIndex, fieldKey, fieldConfig);
      } else {
        $options.hide().empty();
      }
    }

    restoreFieldSpecificOptions(columnIndex, fieldKey, existingMapping) {
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );
      const $options = $card.find('.field-specific-options');

      if (!fieldKey) {
        $options.hide().empty().removeClass('connection-help');
        return;
      }

      const fieldSettings = this.getFieldSettingsForPostType();
      const fieldConfig = fieldSettings[fieldKey];

      if (!fieldConfig) {
        $options.hide().empty().removeClass('connection-help');
        return;
      }

      // Remove connection-help class first
      $options.removeClass('connection-help');

      if (['key_select', 'multi_select'].includes(fieldConfig.type)) {
        this.restoreInlineValueMapping(
          columnIndex,
          fieldKey,
          fieldConfig,
          existingMapping,
        );
      } else if (fieldConfig.type === 'date') {
        this.restoreDateFormatSelector(columnIndex, fieldKey, existingMapping);
      } else if (fieldConfig.type === 'location_meta') {
        this.restoreGeocodingServiceSelector(
          columnIndex,
          fieldKey,
          existingMapping,
        );
      } else if (
        fieldConfig.type === 'communication_channel' &&
        this.isCommunicationFieldForDuplicateCheck(fieldKey)
      ) {
        this.restoreDuplicateCheckingOptions(
          columnIndex,
          fieldKey,
          fieldConfig,
          existingMapping,
        );
      } else if (fieldConfig.type === 'connection') {
        this.showConnectionFieldHelp(columnIndex, fieldKey, fieldConfig);
      } else {
        $options.hide().empty();
      }
    }

    showInlineValueMapping(columnIndex, fieldKey, fieldConfig) {
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );
      const $options = $card.find('.field-specific-options');

      // Get unique values from this CSV column (excluding header row)
      this.getColumnUniqueValues(columnIndex)
        .then((uniqueValues) => {
          if (uniqueValues.length === 0) {
            $options.hide().empty();
            return;
          }

          // Get field options
          this.getFieldOptionsForSelect(fieldKey)
            .then((fieldOptions) => {
              const mappingHtml = this.createInlineValueMappingHtml(
                columnIndex,
                fieldKey,
                uniqueValues,
                fieldOptions,
                fieldConfig.type,
              );
              $options.html(mappingHtml).show();

              // Apply auto-mapping
              this.autoMapInlineValues(columnIndex, fieldOptions);

              // Update field mappings with initial auto-mapped values
              this.updateFieldMappingFromInline(columnIndex, fieldKey);
            })
            .catch((error) => {
              console.error('Error fetching field options:', error);
              $options
                .html('<p style="color: red;">Error loading field options</p>')
                .show();
            });
        })
        .catch((error) => {
          console.error('Error fetching column data:', error);
          $options
            .html('<p style="color: red;">Error loading column data</p>')
            .show();
        });
    }

    showInlineValueMappingWithOptions(
      columnIndex,
      fieldKey,
      fieldConfig,
      fieldOptions,
    ) {
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );
      const $options = $card.find('.field-specific-options');

      // Get unique values from this CSV column (excluding header row)
      this.getColumnUniqueValues(columnIndex)
        .then((uniqueValues) => {
          if (uniqueValues.length === 0) {
            $options.hide().empty();
            return;
          }

          // Use the provided field options directly
          const mappingHtml = this.createInlineValueMappingHtml(
            columnIndex,
            fieldKey,
            uniqueValues,
            fieldOptions,
            fieldConfig.type,
          );
          $options.html(mappingHtml).show();

          // Apply auto-mapping
          this.autoMapInlineValues(columnIndex, fieldOptions);

          // Update field mappings with initial auto-mapped values
          this.updateFieldMappingFromInline(columnIndex, fieldKey);
        })
        .catch((error) => {
          console.error('Error fetching column data:', error);
          $options
            .html('<p style="color: red;">Error loading column data</p>')
            .show();
        });
    }

    getColumnUniqueValues(columnIndex) {
      // Get unique values from the current session's CSV data
      return fetch(
        `${dtImport.restUrl}${this.sessionId}/column-data?column_index=${columnIndex}`,
        {
          headers: {
            'X-WP-Nonce': dtImport.nonce,
          },
        },
      )
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            return data.data.unique_values || [];
          } else {
            throw new Error(data.message || 'Failed to fetch column data');
          }
        });
    }

    getFieldOptionsForSelect(fieldKey) {
      return fetch(
        `${dtImport.restUrl}${this.selectedPostType}/field-options?field_key=${fieldKey}`,
        {
          headers: {
            'X-WP-Nonce': dtImport.nonce,
          },
        },
      )
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            return data.data;
          } else {
            throw new Error(data.message || 'Failed to fetch field options');
          }
        });
    }

    createInlineValueMappingHtml(
      columnIndex,
      fieldKey,
      uniqueValues,
      fieldOptions,
      fieldType,
    ) {
      const fieldOptionsHtml = Object.entries(fieldOptions)
        .map(
          ([key, label]) =>
            `<option value="${this.escapeHtml(key)}">${this.escapeHtml(label)}</option>`,
        )
        .join('');

      const valueMappingRows = uniqueValues
        .slice(0, 10)
        .map((csvValue) => {
          // Limit to first 10 values
          return `
                    <tr class="inline-value-row" data-csv-value="${this.escapeHtml(csvValue)}">
                        <td class="csv-value-cell">
                            <strong>${this.escapeHtml(csvValue)}</strong>
                        </td>
                        <td class="mapping-select-cell">
                            <select class="inline-value-mapping-select" data-csv-value="${this.escapeHtml(csvValue)}" style="width: 100%; font-size: 12px;">
                                <option value="">-- Skip --</option>
                                ${fieldOptionsHtml}
                                <option value="__create__">-- Create --</option>
                            </select>
                        </td>
                    </tr>
                `;
        })
        .join('');

      const moreValuesNote =
        uniqueValues.length > 10
          ? `<p style="font-size: 11px; color: #666; margin-top: 5px;">Showing first 10 of ${uniqueValues.length} unique values</p>`
          : '';

      return `
                <div class="inline-value-mapping-section">
                    <h5 style="margin: 0 0 10px 0; font-size: 13px;">Value Mapping (${fieldType === 'multi_select' ? 'Multi-Select' : 'Dropdown'})</h5>
                    <div class="inline-value-mapping-container" style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd; border-radius: 3px;">
                        <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
                            <thead style="background: #f9f9f9; position: sticky; top: 0;">
                                <tr>
                                    <th style="padding: 6px 8px; text-align: left; width: 50%; border-bottom: 1px solid #ddd;">CSV Value</th>
                                    <th style="padding: 6px 8px; text-align: left; width: 50%; border-bottom: 1px solid #ddd;">Maps To</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${valueMappingRows}
                            </tbody>
                        </table>
                    </div>
                    ${moreValuesNote}
                    <div class="inline-mapping-controls" style="margin-top: 8px;">
                        <button type="button" class="button auto-map-inline-btn" data-column-index="${columnIndex}" style="font-size: 11px; padding: 3px 8px; height: auto;">
                            Auto-map Similar
                        </button>
                        <button type="button" class="button clear-inline-btn" data-column-index="${columnIndex}" style="font-size: 11px; padding: 3px 8px; height: auto;">
                            Clear All
                        </button>
                        <span class="inline-mapping-count" style="font-size: 11px; color: #666; margin-left: 10px;">0 mapped</span>
                    </div>
                </div>
            `;
    }

    autoMapInlineValues(columnIndex, fieldOptions) {
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );

      // Build reverse lookup for field options
      const optionLookup = {};
      Object.entries(fieldOptions).forEach(([key, label]) => {
        optionLookup[key.toLowerCase()] = key;
        optionLookup[label.toLowerCase()] = key;
      });

      $card.find('.inline-value-mapping-select').each(function () {
        const $select = $(this);
        const csvValue = $select.data('csv-value');
        const csvValueLower = csvValue.toString().toLowerCase().trim();

        // Try exact match first (key or label)
        if (optionLookup[csvValueLower]) {
          $select.val(optionLookup[csvValueLower]);
          return;
        }

        // Try partial matches
        for (const [optionText, optionKey] of Object.entries(optionLookup)) {
          if (
            csvValueLower.includes(optionText) ||
            optionText.includes(csvValueLower)
          ) {
            $select.val(optionKey);
            break;
          }
        }
      });

      this.updateInlineMappingCount(columnIndex);
    }

    updateInlineMappingCount(columnIndex) {
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );
      const totalSelects = $card.find('.inline-value-mapping-select').length;
      const mappedSelects = $card
        .find('.inline-value-mapping-select')
        .filter(function () {
          return $(this).val() !== '';
        }).length;

      $card
        .find('.inline-mapping-count')
        .text(`${mappedSelects} of ${totalSelects} mapped`);
    }

    updateFieldMappingFromInline(columnIndex, fieldKey) {
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );
      const valueMappings = {};

      $card.find('.inline-value-mapping-select').each(function () {
        const $select = $(this);
        const csvValue = $select.data('csv-value');
        const dtValue = $select.val();

        if (dtValue) {
          valueMappings[csvValue] = dtValue;
        }
      });

      // Update the field mappings
      if (!this.fieldMappings[columnIndex]) {
        this.fieldMappings[columnIndex] = {
          field_key: fieldKey,
          column_index: columnIndex,
        };
      }

      this.fieldMappings[columnIndex].value_mapping = valueMappings;
    }

    // Step 4: Preview & Import
    saveFieldMappings() {
      // Check if at least one field is mapped
      if (Object.keys(this.fieldMappings).length === 0) {
        this.showError('Please map at least one field before proceeding.');
        return;
      }

      // Check if name field is mapped (critical requirement)
      const nameFieldMapped = Object.values(this.fieldMappings).some(
        (mapping) => mapping.field_key === 'name',
      );

      if (!nameFieldMapped) {
        this.showError(
          'The name field is required and must be mapped to a CSV column before proceeding with the import.',
        );
        return;
      }

      // Ensure all inline value mappings are up to date before saving
      Object.entries(this.fieldMappings).forEach(([columnIndex, mapping]) => {
        const fieldSettings = this.getFieldSettingsForPostType();
        const fieldConfig = fieldSettings[mapping.field_key];

        if (
          fieldConfig &&
          ['key_select', 'multi_select'].includes(fieldConfig.type)
        ) {
          // Check if there are inline value mapping selects for this column
          const $card = $(
            `.column-mapping-card[data-column-index="${columnIndex}"]`,
          );
          if ($card.find('.inline-value-mapping-select').length > 0) {
            this.updateFieldMappingFromInline(
              parseInt(columnIndex),
              mapping.field_key,
            );
          }
        }
      });
      this.showProcessing('Saving field mappings...');

      // Use the import options that were captured in analyzeCSV()
      const importOptions = this.importOptions || {
        assigned_to: null,
        source: null,
        delimiter: ',',
        encoding: 'UTF-8',
      };

      fetch(`${dtImport.restUrl}${this.sessionId}/mapping`, {
        method: 'POST',
        headers: {
          'X-WP-Nonce': dtImport.nonce,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          mappings: this.fieldMappings,
          do_not_import_columns: Array.from(this.doNotImportColumns),
          import_options: importOptions,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          this.hideProcessing();

          if (data.success) {
            this.showStep4();
          } else {
            this.showError(data.message || 'Failed to save mappings');
          }
        })
        .catch((error) => {
          this.hideProcessing();
          this.showError('Failed to save mappings');
          console.error('Mapping save error:', error);
        });
    }

    showStep4() {
      this.currentStep = 4;
      this.updateStepIndicator();

      this.loadPreviewData();
    }

    loadPreviewData(offset = 0, limit = 10) {
      this.showProcessing('Loading preview data...');

      fetch(
        `${dtImport.restUrl}${this.sessionId}/preview?offset=${offset}&limit=${limit}`,
        {
          headers: {
            'X-WP-Nonce': dtImport.nonce,
          },
        },
      )
        .then((response) => response.json())
        .then((data) => {
          this.hideProcessing();

          if (data.success) {
            this.displayPreview(data.data);
          } else {
            this.showError(data.message || 'Failed to load preview');
          }
        })
        .catch((error) => {
          this.hideProcessing();
          this.showError('Failed to load preview');
          console.error('Preview error:', error);
        });
    }

    displayPreview(previewData) {
      // Count total warnings across all rows
      const totalWarnings = previewData.rows.reduce((count, row) => {
        return count + (row.warnings ? row.warnings.length : 0);
      }, 0);

      // Count total errors across all rows
      const totalErrors = previewData.rows.reduce((count, row) => {
        return count + (row.errors ? row.errors.length : 0);
      }, 0);

      const step4Html = `
                <div class="dt-import-step-content">
                    <h2>${dtImport.translations.previewImport}</h2>
                    <p>Review the data before importing ${previewData.total_rows} records.</p>
                    
                    ${
                      previewData.is_estimated
                        ? `
                    <div class="notice notice-info">
                        <p><strong>Large File Detected:</strong> Counts below are estimated based on a sample of ${previewData.sample_size} rows for faster processing. Actual numbers may vary slightly during import.</p>
                    </div>
                    `
                        : ''
                    }
                    
                    <div class="preview-stats">
                        <div class="stat-card">
                            <h3>${previewData.total_rows}</h3>
                            <p>Total Records</p>
                        </div>
                        <div class="stat-card">
                            <h3>${previewData.processable_count}${previewData.is_estimated ? '*' : ''}</h3>
                            <p>Will Import</p>
                        </div>
                        ${
                          totalWarnings > 0
                            ? `
                        <div class="stat-card warning-card">
                            <h3>${totalWarnings}</h3>
                            <p>Warnings</p>
                        </div>
                        `
                            : ''
                        }
                        <div class="stat-card error-card">
                            <h3>${previewData.error_count}${previewData.is_estimated ? '*' : ''}</h3>
                            <p>Errors</p>
                        </div>
                    </div>
                    
                    ${
                      previewData.is_estimated
                        ? `
                    <p style="font-size: 12px; color: #666; margin-top: 5px;"><em>* Estimated values based on sampling</em></p>
                    `
                        : ''
                    }
                    
                    ${
                      totalErrors > 0
                        ? `
                    <div class="errors-summary">
                        <div class="notice notice-error">
                            <h4><i class="mdi mdi-close-circle"></i> Import Errors</h4>
                            <p>Some records have errors and will be skipped during import. Review the errors below for details.</p>
                        </div>
                    </div>
                    `
                        : ''
                    }
                    
                    ${
                      totalWarnings > 0
                        ? `
                    <div class="warnings-summary">
                        <div class="notice notice-warning">
                            <h4><i class="mdi mdi-alert"></i> Import Warnings</h4>
                            <p>Some records will create new connection records. Review the preview below for details.</p>
                        </div>
                    </div>
                    `
                        : ''
                    }
                    
                    <div class="preview-table-container">
                        ${this.createPreviewTable(previewData.rows)}
                    </div>
                </div>
            `;

      $('.dt-import-step-content').html(step4Html);
      this.updateNavigation();

      // Add import button to navigation area
      $('.dt-import-navigation').append(`
        <button type="button" class="button button-primary execute-import-btn">
          Import ${previewData.processable_count} Records
        </button>
      `);
    }

    createPreviewTable(rows) {
      if (!rows || rows.length === 0) {
        return '<p>No data to preview.</p>';
      }

      // Get only the headers for fields that are actually being imported
      // The preview data from the server only contains fields that have mappings
      const headers = Object.keys(rows[0].data);

      if (headers.length === 0) {
        return '<p>No fields selected for import. Please go back and configure field mappings.</p>';
      }

      // Get field settings to convert field keys to human-readable names
      const fieldSettings = this.getFieldSettingsForPostType();

      const headerHtml =
        `<th>Row #</th>` +
        headers
          .map((fieldKey) => {
            // Convert field key to human-readable field name
            const fieldName =
              fieldSettings[fieldKey] && fieldSettings[fieldKey].name
                ? fieldSettings[fieldKey].name
                : fieldKey; // fallback to field key if name not found
            return `<th>${this.escapeHtml(fieldName)}</th>`;
          })
          .join('');

      const rowsHtml = rows
        .map((row) => {
          const hasWarnings = row.warnings && row.warnings.length > 0;
          const willUpdate = row.will_update_existing || false;
          const rowClass = row.has_errors
            ? 'error-row'
            : hasWarnings
              ? 'warning-row'
              : willUpdate
                ? 'preview-row will-update'
                : 'preview-row';

          const cellsHtml = headers
            .map((header) => {
              const cellData = row.data[header];
              const cellClass = cellData && !cellData.valid ? 'error-cell' : '';
              const value = cellData ? cellData.processed || cellData.raw : '';
              return `<td class="${cellClass}">${this.escapeHtml(this.formatCellValue(value))}</td>`;
            })
            .join('');

          // Create warnings display
          const warningsHtml = hasWarnings
            ? `
            <tr class="warnings-row">
              <td colspan="${headers.length + 1}">
                <div class="row-warnings">
                  <strong><i class="mdi mdi-alert"></i> Warnings:</strong>
                  <ul>
                    ${row.warnings.map((warning) => `<li>${this.escapeHtml(warning)}</li>`).join('')}
                  </ul>
                </div>
              </td>
            </tr>
          `
            : '';

          // Create errors display
          const hasErrors = row.errors && row.errors.length > 0;
          const errorsHtml = hasErrors
            ? `
            <tr class="errors-row">
              <td colspan="${headers.length + 1}">
                <div class="row-errors">
                  <strong><i class="mdi mdi-close-circle"></i> Errors:</strong>
                  <ul>
                    ${row.errors.map((error) => `<li>${this.escapeHtml(error)}</li>`).join('')}
                  </ul>
                </div>
              </td>
            </tr>
          `
            : '';

          // Add row number indicator with update status
          const rowNumberDisplay = willUpdate
            ? `Row ${row.row_number} <span class="update-indicator">(UPDATE)</span>`
            : `Row ${row.row_number}`;

          return `<tr class="${rowClass}" data-row-number="${row.row_number}">
                    <td class="row-number">${rowNumberDisplay}</td>
                    ${cellsHtml}
                  </tr>${errorsHtml}${warningsHtml}`;
        })
        .join('');

      return `
                <table class="preview-table">
                    <thead>
                        <tr>${headerHtml}</tr>
                    </thead>
                    <tbody>
                        ${rowsHtml}
                    </tbody>
                </table>
            `;
    }

    getColumnIndexForField(fieldKey) {
      // Find the column index that maps to this field
      for (const [columnIndex, mapping] of Object.entries(this.fieldMappings)) {
        if (mapping.field_key === fieldKey) {
          return parseInt(columnIndex);
        }
      }
      return null;
    }

    executeImport() {
      if (this.isProcessing) return;

      this.showProcessing('Starting import...');
      this.isProcessing = true;

      fetch(`${dtImport.restUrl}${this.sessionId}/execute`, {
        method: 'POST',
        headers: {
          'X-WP-Nonce': dtImport.nonce,
          'Content-Type': 'application/json',
        },
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            // this.startProgressPolling();
          } else {
            this.isProcessing = false;
            this.hideProcessing();
            this.showError(data.message || 'Failed to start import');
          }
        })
        .catch((error) => {
          this.isProcessing = false;
          this.hideProcessing();
          this.showError('Failed to start import');
          console.error('Import error:', error);
        });
      this.startProgressPolling();
    }

    startProgressPolling() {
      let isPolling = false;

      const pollStatus = () => {
        if (isPolling) return; // Skip if previous request is still in progress

        isPolling = true;

        fetch(`${dtImport.restUrl}${this.sessionId}/status`, {
          headers: {
            'X-WP-Nonce': dtImport.nonce,
          },
        })
          .then((response) => response.json())
          .then((data) => {
            if (data.success) {
              const status = data.data.status;
              const progress = data.data.progress || 0;

              this.updateProgress(progress, status);

              if (
                status === 'completed' ||
                status === 'completed_with_errors'
              ) {
                clearInterval(pollInterval);
                this.isProcessing = false;
                this.hideProcessing();
                this.showImportResults(data.data);
                return; // Don't continue polling
              } else if (status === 'failed') {
                clearInterval(pollInterval);
                this.isProcessing = false;
                this.hideProcessing();
                this.showError('Import failed');
                return; // Don't continue polling
              }
            }
          })
          .catch((error) => {
            console.error('Status polling error:', error);
          })
          .finally(() => {
            isPolling = false; // Reset flag when request completes
          });
      };

      const pollInterval = setInterval(pollStatus, 5000); // Poll every 5 seconds
      pollStatus(); // Start first poll immediately
    }

    updateProgress(progress, status) {
      const $processingMessage = $('.processing-message p');

      if (progress > 0) {
        // Show percentage once progress is above 0%
        $processingMessage.text(`Importing records... ${progress}%`);
      } else {
        // Show just "Importing Records" when starting (0%)
        $processingMessage.text('Importing Records');
      }
    }

    showImportResults(results) {
      const resultsHtml = `
                <div class="import-results">
                    <h2>Import Complete!</h2>
                    <div class="results-stats">
                        <div class="stat-card success-card">
                            <h3>${results.records_imported}</h3>
                            <p>Records Imported</p>
                        </div>
                        ${
                          results.errors && results.errors.length > 0
                            ? `
                            <div class="stat-card error-card">
                                <h3>${results.errors.length}</h3>
                                <p>Errors</p>
                            </div>
                        `
                            : ''
                        }
                    </div>
                    
                    ${
                      results.imported_records &&
                      results.imported_records.length > 0
                        ? `
                        <div class="imported-records-list">
                            <h3>Imported Records:</h3>
                            <div class="records-container">
                                <ul class="records-list">
                                    ${results.imported_records
                                      .map(
                                        (record) => `
                                        <li>
                                            <a href="${record.permalink}" target="_blank" rel="noopener noreferrer">
                                                ${this.escapeHtml(record.name)}
                                                ${record.action === 'updated' ? '<span class="action-indicator updated">(UPDATED)</span>' : ''}
                                            </a>
                                        </li>
                                    `,
                                      )
                                      .join('')}
                                </ul>
                                ${
                                  results.records_imported >
                                  results.imported_records.length
                                    ? `
                                    <p class="records-note">
                                        <em>Showing first ${results.imported_records.length} of ${results.records_imported} imported records</em>
                                    </p>
                                `
                                    : ''
                                }
                            </div>
                        </div>
                    `
                        : ''
                    }
                    
                    ${
                      results.errors && results.errors.length > 0
                        ? `
                        <div class="error-details">
                            <h3>Errors:</h3>
                            <ul>
                                ${results.errors
                                  .map(
                                    (error) => `
                                    <li>Row ${error.row}: ${this.escapeHtml(error.message)}</li>
                                `,
                                  )
                                  .join('')}
                            </ul>
                        </div>
                    `
                        : ''
                    }
                    
                    <div class="results-actions">
                        <button type="button" class="button button-primary" onclick="location.reload()">
                            Start New Import
                        </button>
                    </div>
                </div>
            `;

      $('.dt-import-step-content').html(resultsHtml);

      // Mark step 4 as completed (green) since import is successful
      this.markStepAsCompleted();
    }

    // Utility methods
    updateStepIndicator() {
      $('.dt-import-steps .step').removeClass('active completed');

      $('.dt-import-steps .step').each((index, step) => {
        const stepNum = index + 1;
        if (stepNum < this.currentStep) {
          $(step).addClass('completed');
        } else if (stepNum === this.currentStep) {
          $(step).addClass('active');
        }
      });
    }

    updateNavigation() {
      const $backBtn = $('.dt-import-back');
      const $nextBtn = $('.dt-import-next');
      const $importBtn = $('.execute-import-btn');

      // Clear any existing import buttons from navigation
      $importBtn.remove();

      // Back button
      if (this.currentStep === 1) {
        $backBtn.hide();
      } else {
        $backBtn.show();
      }

      // Next button
      if (this.currentStep === 4) {
        $nextBtn.hide();
      } else {
        $nextBtn.show();
        $nextBtn.prop('disabled', !this.canProceedToNextStep());
      }
    }

    canProceedToNextStep() {
      switch (this.currentStep) {
        case 1:
          return !!this.selectedPostType;
        case 2:
          return !!this.csvData;
        case 3: {
          // Check if at least one field is mapped AND the name field is mapped
          const hasFieldMappings = Object.keys(this.fieldMappings).length > 0;
          const nameFieldMapped = Object.values(this.fieldMappings).some(
            (mapping) => mapping.field_key === 'name',
          );
          return hasFieldMappings && nameFieldMapped;
        }
        default:
          return true;
      }
    }

    updateMappingSummary() {
      const mappedCount = Object.keys(this.fieldMappings).length;
      const totalColumns = $('.column-mapping-card').length;

      // Check if name field is mapped
      const nameFieldMapped = Object.values(this.fieldMappings).some(
        (mapping) => mapping.field_key === 'name',
      );

      $('.mapping-summary').show();

      if (!nameFieldMapped) {
        $('.summary-stats').html(`
          <p>${mappedCount} of ${totalColumns} columns mapped</p>
          <p style="color: #dc3232; font-weight: bold;">⚠ Name field is required and must be mapped</p>
        `);
      } else {
        $('.summary-stats').html(`
          <p>${mappedCount} of ${totalColumns} columns mapped</p>
          <p style="color: #46b450;">✓ Name field is mapped</p>
        `);
      }

      // Disable next button if no mappings OR name field is not mapped
      $('.dt-import-next').prop(
        'disabled',
        mappedCount === 0 || !nameFieldMapped,
      );
    }

    showProcessing(message) {
      $('.dt-import-container').append(`
                <div class="processing-overlay">
                    <div class="processing-message">
                        <div class="dt-spinner"></div>
                        <p>${message}</p>
                    </div>
                </div>
            `);
    }

    hideProcessing() {
      $('.processing-overlay').remove();
    }

    showError(message) {
      // Use Toastify for error messages if available
      if (typeof Toastify !== 'undefined') {
        Toastify({
          text: message,
          duration: 5000,
          close: true,
          gravity: 'bottom',
          position: 'center',
          backgroundColor: '#dc3232',
          className: 'dt-import-toast-error',
          stopOnFocus: true,
        }).showToast();
      } else {
        // Fallback to original method if Toastify is not available
        $('.dt-import-errors')
          .html(
            `
                  <div class="notice notice-error">
                      <p>${this.escapeHtml(message)}</p>
                  </div>
              `,
          )
          .show();

        setTimeout(() => {
          $('.dt-import-errors').fadeOut();
        }, 5000);
      }
    }

    showSuccess(message) {
      // Use Toastify for success messages if available
      if (typeof Toastify !== 'undefined') {
        Toastify({
          text: message,
          duration: 3000,
          close: true,
          gravity: 'bottom',
          position: 'center',
          backgroundColor: '#46b450',
          className: 'dt-import-toast-success',
          stopOnFocus: true,
        }).showToast();
      } else {
        // Fallback to original method if Toastify is not available
        $('.dt-import-errors')
          .html(
            `
                  <div class="notice notice-success">
                      <p>${this.escapeHtml(message)}</p>
                  </div>
              `,
          )
          .show();

        setTimeout(() => {
          $('.dt-import-errors').fadeOut();
        }, 3000);
      }
    }

    // Helper methods
    getPostTypeIcon(postType) {
      const icons = {
        contacts: 'account',
        groups: 'account-group',
        default: 'file-document',
      };
      return icons[postType] || icons.default;
    }

    getPostTypeLabel() {
      const postType = dtImport.postTypes.find(
        (pt) => pt.key === this.selectedPostType,
      );
      return postType ? postType.label_plural : '';
    }

    getFieldSettingsForPostType() {
      // Return cached field settings if available
      if (this.cachedFieldSettings) {
        return this.cachedFieldSettings;
      }

      // If we don't have a selected post type, return empty object
      if (!this.selectedPostType) {
        return {};
      }

      // Fetch field settings synchronously for current post type
      // Note: This is acceptable for admin interface with small datasets
      let fieldSettings = {};

      $.ajax({
        url: `${dtImport.restUrl}${this.selectedPostType}/field-settings`,
        method: 'GET',
        async: false, // Synchronous to maintain current interface flow
        headers: {
          'X-WP-Nonce': dtImport.nonce,
        },
        success: (response) => {
          if (response.success && response.data) {
            fieldSettings = response.data;
            this.cachedFieldSettings = fieldSettings;
          }
        },
        error: (xhr, status, error) => {
          console.error('Failed to fetch field settings:', error);
          // Fallback to basic structure
          fieldSettings = {
            title: { name: 'Name', type: 'text' },
            overall_status: { name: 'Status', type: 'key_select' },
            assigned_to: { name: 'Assigned To', type: 'user_select' },
          };
        },
      });

      return fieldSettings;
    }

    formatFileSize(bytes) {
      const units = ['B', 'KB', 'MB', 'GB'];
      let size = bytes;
      let unitIndex = 0;

      while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
      }

      return `${Math.round(size * 100) / 100} ${units[unitIndex]}`;
    }

    formatCellValue(value) {
      if (value === null || value === undefined) {
        return '';
      }

      // Handle objects with values array (multi_select, tags, communication_channels, etc.)
      if (
        typeof value === 'object' &&
        value.values &&
        Array.isArray(value.values)
      ) {
        const values = value.values.map((item) => {
          // Handle location_meta objects within values array
          if (typeof item === 'object' && item.label !== undefined) {
            return item.label;
          }
          if (typeof item === 'object' && item.value !== undefined) {
            return item.value;
          }
          return item;
        });
        return values.join('; ');
      }

      // Handle arrays directly
      if (Array.isArray(value)) {
        return value
          .map((item) => {
            // Handle location_meta objects in arrays
            if (typeof item === 'object' && item.label !== undefined) {
              return item.label;
            }
            if (typeof item === 'object' && item.value !== undefined) {
              return item.value;
            }
            return item;
          })
          .join('; ');
      }

      // Handle location_meta objects directly
      if (typeof value === 'object' && value.label !== undefined) {
        return value.label;
      }

      // Handle coordinate objects
      if (
        typeof value === 'object' &&
        value.lat !== undefined &&
        value.lng !== undefined
      ) {
        return `Coordinates: ${value.lat}, ${value.lng}`;
      }

      // Handle address objects
      if (typeof value === 'object' && value.address !== undefined) {
        return value.address;
      }

      // Handle grid ID objects
      if (typeof value === 'object' && value.grid_id !== undefined) {
        return `Grid ID: ${value.grid_id}`;
      }

      // Handle name objects
      if (typeof value === 'object' && value.name !== undefined) {
        return value.name;
      }

      if (typeof value === 'object') {
        return JSON.stringify(value);
      }
      return String(value);
    }

    escapeHtml(text) {
      const div = document.createElement('div');
      div.textContent = text;
      return div.innerHTML;
    }

    handleInlineValueMappingChange(e) {
      const $select = $(e.target);
      const $card = $select.closest('.column-mapping-card');
      const columnIndex = $card.data('column-index');
      const fieldKey = $card.find('.field-mapping-select').val();

      // Check if "-- Create --" option was selected
      if ($select.val() === '__create__') {
        this.handleCreateFieldOption($select, fieldKey);
        return;
      }

      // Update the mapping count
      this.updateInlineMappingCount(columnIndex);

      // Update field mappings
      this.updateFieldMappingFromInline(columnIndex, fieldKey);
    }

    handleAutoMapInline(e) {
      const $btn = $(e.target);
      const columnIndex = $btn.data('column-index');
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );
      const fieldKey = $card.find('.field-mapping-select').val();

      // Get field options and apply auto-mapping
      this.getFieldOptionsForSelect(fieldKey)
        .then((fieldOptions) => {
          this.autoMapInlineValues(columnIndex, fieldOptions);
          this.updateFieldMappingFromInline(columnIndex, fieldKey);
        })
        .catch((error) => {
          console.error('Error during auto-mapping:', error);
        });
    }

    handleClearInline(e) {
      const $btn = $(e.target);
      const columnIndex = $btn.data('column-index');
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );
      const fieldKey = $card.find('.field-mapping-select').val();

      // Clear all selections
      $card.find('.inline-value-mapping-select').val('');

      // Update count and mappings
      this.updateInlineMappingCount(columnIndex);
      this.updateFieldMappingFromInline(columnIndex, fieldKey);
    }

    handleCreateFieldOption($select, fieldKey) {
      const csvValue = $select.data('csv-value');
      const optionKey = this.sanitizeKey(csvValue);
      const optionLabel = csvValue;

      // Show loading state
      $select.prop('disabled', true);
      const originalOptions = $select.html();
      $select.html('<option value="">Creating...</option>');

      // Prepare the form data
      const optionData = new FormData();
      optionData.append('post_type', this.selectedPostType);
      optionData.append('tile_key', 'other');
      optionData.append('field_key', fieldKey);
      optionData.append('field_option_name', optionLabel);
      optionData.append('field_option_description', '');
      optionData.append('field_option_key', optionKey);

      // Create the field option
      fetch(
        `${dtImport.restUrl.replace('dt-csv-import/v2/', '')}dt-admin-settings/new-field-option`,
        {
          method: 'POST',
          headers: {
            'X-WP-Nonce': dtImport.nonce,
          },
          body: optionData,
        },
      )
        .then((response) => {
          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
          return response.json();
        })
        .then((data) => {
          // Check if response is successful - API returns the field key directly on success
          if (
            data &&
            (typeof data === 'string' ||
              (typeof data === 'object' && !data.error && !data.code))
          ) {
            // Restore original options and add the new one
            $select.html(originalOptions);
            const newOption = `<option value="${optionKey}">${optionLabel}</option>`;
            $select.find('option[value="__create__"]').before(newOption);
            $select.val(optionKey);
            $select.prop('disabled', false);

            // Update the mapping count and field mappings
            const $card = $select.closest('.column-mapping-card');
            const columnIndex = $card.data('column-index');
            this.updateInlineMappingCount(columnIndex);
            this.updateFieldMappingFromInline(columnIndex, fieldKey);
          } else {
            // Handle error - data contains error information
            console.error('Error creating field option:', data);
            $select.html(originalOptions);
            $select.val('');
            $select.prop('disabled', false);
            const errorMessage =
              data.error || data.message || 'Unknown error occurred';
            this.showError(`Error creating field option: ${errorMessage}`);
          }
        })
        .catch((error) => {
          console.error('Error creating field option:', error);
          $select.html(originalOptions);
          $select.val('');
          $select.prop('disabled', false);
          this.showError('Error creating field option. Please try again.');
        });
    }

    sanitizeKey(value) {
      return value
        .toString()
        .toLowerCase()
        .replace(/[^a-z0-9_]/g, '_')
        .replace(/__+/g, '_')
        .replace(/^_+|_+$/g, '');
    }

    markStepAsCompleted() {
      // Mark step 4 as completed (green) and remove active state
      $('.dt-import-steps .step').each((index, step) => {
        const stepNum = index + 1;
        if (stepNum === 4) {
          $(step).removeClass('active').addClass('completed');
        }
      });
    }

    showGeocodingServiceSelector(columnIndex, fieldKey) {
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );
      const $options = $card.find('.field-specific-options');

      const geocodingSelectorHtml = `
        <div class="geocoding-service-section">
          <h5 style="margin: 0 0 10px 0; font-size: 13px;">${dtImport.translations.geocodingService}</h5>
          <div class="geocoding-service-container">
            <label>
              <input type="checkbox" class="geocoding-service-checkbox" data-column-index="${columnIndex}" style="margin-right: 5px;">
              ${dtImport.translations.enableGeocoding}
            </label>
            <div class="geocoding-info" style="font-size: 11px; color: #666; margin-top: 5px;">
              <p>${dtImport.translations.geocodingNote}</p>
              <p>${dtImport.translations.geocodingOptional}</p>
            </div>
          </div>
        </div>
      `;

      $options.html(geocodingSelectorHtml).show();

      // Set default value to false if not already set
      const currentMapping = this.fieldMappings[columnIndex];
      if (
        !currentMapping ||
        !currentMapping.geocode_service ||
        currentMapping.geocode_service === 'none'
      ) {
        $options.find('.geocoding-service-checkbox').prop('checked', false);
        this.updateFieldMappingGeocodingService(columnIndex, false);
      } else {
        $options.find('.geocoding-service-checkbox').prop('checked', true);
      }
    }

    updateFieldMappingGeocodingService(columnIndex, isEnabled) {
      // Update the field mappings with the geocoding enabled state
      if (!this.fieldMappings[columnIndex]) {
        // This shouldn't happen since field mapping should be set first
        console.warn(`No field mapping found for column ${columnIndex}`);
        return;
      } else {
        // Convert boolean to service key for backend compatibility
        this.fieldMappings[columnIndex].geocode_service = isEnabled
          ? 'auto'
          : 'none';
      }

      this.updateMappingSummary();
    }

    handleGeocodingServiceChange(e) {
      const $checkbox = $(e.target);
      const columnIndex = $checkbox.data('column-index');
      const isEnabled = $checkbox.prop('checked');

      this.updateFieldMappingGeocodingService(columnIndex, isEnabled);
    }

    showDuplicateCheckingOptions(columnIndex, fieldKey, fieldConfig) {
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );
      const $options = $card.find('.field-specific-options');

      const duplicateCheckingHtml = `
        <div class="duplicate-checking-section">
          <h5 style="margin: 0 0 10px 0; font-size: 13px;">${dtImport.translations.duplicateChecking}</h5>
          <div class="duplicate-checking-container">
            <label>
              <input type="checkbox" class="duplicate-checking-checkbox" data-column-index="${columnIndex}" style="margin-right: 5px;">
              ${dtImport.translations.enableDuplicateChecking}
            </label>
            <p style="font-size: 11px; color: #666; margin-top: 5px;">
              ${dtImport.translations.duplicateCheckingNote}
            </p>
          </div>
        </div>
      `;

      $options.html(duplicateCheckingHtml).show();

      // Set default value to false if not already set
      const currentMapping = this.fieldMappings[columnIndex];
      if (!currentMapping || !currentMapping.duplicate_checking) {
        $options.find('.duplicate-checking-checkbox').prop('checked', false);
        this.updateFieldMappingDuplicateChecking(columnIndex, false);
      } else {
        $options
          .find('.duplicate-checking-checkbox')
          .prop('checked', currentMapping.duplicate_checking);
      }
    }

    updateFieldMappingDuplicateChecking(columnIndex, isEnabled) {
      // Update the field mappings with the selected duplicate checking state
      if (!this.fieldMappings[columnIndex]) {
        // This shouldn't happen since field mapping should be set first
        console.warn(`No field mapping found for column ${columnIndex}`);
        return;
      } else {
        this.fieldMappings[columnIndex].duplicate_checking = isEnabled;
      }

      this.updateMappingSummary();
    }

    isCommunicationFieldForDuplicateCheck(fieldKey) {
      const fieldSettings = this.getFieldSettingsForPostType();
      const fieldConfig = fieldSettings[fieldKey];

      if (fieldConfig && fieldConfig.type === 'communication_channel') {
        return true;
      }
      return false;
    }

    handleDuplicateCheckingChange(e) {
      const $checkbox = $(e.target);
      const columnIndex = $checkbox.data('column-index');
      const isEnabled = $checkbox.prop('checked');

      this.updateFieldMappingDuplicateChecking(columnIndex, isEnabled);
    }

    showDateFormatSelector(columnIndex, fieldKey) {
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );
      const $options = $card.find('.field-specific-options');

      const dateFormatOptions = {
        auto: 'Auto-detect (recommended)',
        'Y-m-d': 'YYYY-MM-DD (2024-01-15)',
        'm/d/Y': 'MM/DD/YYYY (01/15/2024)',
        'd/m/Y': 'DD/MM/YYYY (15/01/2024)',
        'F j, Y': 'Month Day, Year (January 15, 2024)',
        'j M Y': 'Day Mon Year (15 Jan 2024)',
        'Y-m-d H:i:s': 'YYYY-MM-DD HH:MM:SS (2024-01-15 14:30:00)',
      };

      const formatOptionsHtml = Object.entries(dateFormatOptions)
        .map(
          ([value, label]) =>
            `<option value="${this.escapeHtml(value)}">${this.escapeHtml(label)}</option>`,
        )
        .join('');

      const dateFormatHtml = `
        <div class="date-format-section">
          <h5 style="margin: 0 0 10px 0; font-size: 13px;">Date Format</h5>
          <div class="date-format-container">
            <label for="date-format-${columnIndex}" style="display: block; font-size: 12px; margin-bottom: 5px;">
              Select the format of dates in your CSV:
            </label>
            <select id="date-format-${columnIndex}" class="date-format-select" data-column-index="${columnIndex}" style="width: 100%; padding: 5px;">
              ${formatOptionsHtml}
            </select>
            <p style="font-size: 11px; color: #666; margin-top: 5px;">
              Auto-detect works for most formats, but specifying the exact format ensures accuracy.
            </p>
          </div>
        </div>
      `;

      $options.html(dateFormatHtml).show();

      // Set default value to 'auto' if not already set
      const currentMapping = this.fieldMappings[columnIndex];
      if (!currentMapping || !currentMapping.date_format) {
        $options.find('.date-format-select').val('auto');
        this.updateFieldMappingDateFormat(columnIndex, 'auto');
      } else {
        $options.find('.date-format-select').val(currentMapping.date_format);
      }
    }

    updateFieldMappingDateFormat(columnIndex, dateFormat) {
      // Update the field mappings with the selected date format
      if (!this.fieldMappings[columnIndex]) {
        // This shouldn't happen since field mapping should be set first
        console.warn(`No field mapping found for column ${columnIndex}`);
        return;
      } else {
        this.fieldMappings[columnIndex].date_format = dateFormat;
      }

      this.updateMappingSummary();
    }

    handleDateFormatChange(e) {
      const $select = $(e.target);
      const columnIndex = $select.data('column-index');
      const dateFormat = $select.val();

      this.updateFieldMappingDateFormat(columnIndex, dateFormat);
    }

    showConnectionFieldHelp(columnIndex, fieldKey, fieldConfig) {
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );
      const $options = $card.find('.field-specific-options');

      const postTypeName = fieldConfig.post_type || 'records';
      const fieldName = fieldConfig.name || 'Connection';

      const helpHtml = `
                <div class="connection-field-help">
                    <div class="field-help-header">
                        <i class="mdi mdi-information-outline"></i>
                        <strong>Connection Field</strong>
                    </div>
                    <div class="field-help-content">
                        <ul>
                            <li><strong>Record ID</strong> (number): Links to that specific record</li>
                            <li><strong>Name</strong> (text): Searches for existing ${postTypeName}</li>
                            <li><strong>One match</strong>: Links to existing record</li>
                            <li><strong>No match</strong>: Creates new record</li>
                            <li><strong>Multiple matches</strong>: Connection skipped</li>
                        </ul>
                    </div>
                </div>
            `;

      $options.html(helpHtml).show().addClass('connection-help');
    }

    getImportOptionsHtml() {
      if (!this.selectedPostType) {
        return '';
      }

      // Use field settings from getFieldSettingsForPostType instead
      const fieldSettings = this.getFieldSettingsForPostType();

      let html = '';

      // Check if this post type supports assigned_to field
      if (fieldSettings && fieldSettings.assigned_to) {
        html += `
          <div class="import-options">
            <h3>Import Options</h3>
            <table class="form-table">
              <tr>
                <th><label for="import-assigned-to">Assign to User</label></th>
                <td>
                  <select id="import-assigned-to">
                    <option value="">Select a user...</option>
                  </select>
                  <p class="description">All imported records will be assigned to this user.</p>
                </td>
              </tr>`;
      }

      // Check if this post type supports sources field
      if (fieldSettings && fieldSettings.sources) {
        if (!html) {
          html += `
            <div class="import-options">
              <h3>Import Options</h3>
              <table class="form-table">`;
        }
        html += `
              <tr>
                <th><label for="import-source">Source</label></th>
                <td>
                  <select id="import-source">
                    <option value="">Select a source...</option>
                  </select>
                  <p class="description">All imported records will have this source.</p>
                </td>
              </tr>`;
      }

      if (html) {
        html += `
            </table>
          </div>`;
      }

      return html;
    }

    populateImportOptions() {
      // Populate assigned_to dropdown
      if ($('#import-assigned-to').length > 0) {
        this.loadUsers()
          .then((users) => {
            const $select = $('#import-assigned-to');
            $select
              .empty()
              .append('<option value="">Select a user...</option>');

            users.forEach((user) => {
              $select.append(
                `<option value="${user.ID}">${this.escapeHtml(user.name)}</option>`,
              );
            });

            // Restore previously selected value if it exists
            if (this.importOptions && this.importOptions.assigned_to) {
              $select.val(this.importOptions.assigned_to);
            }
          })
          .catch((error) => {
            console.error('Error loading users:', error);
          });
      }

      // Populate source dropdown
      if ($('#import-source').length > 0) {
        this.loadSources()
          .then((sources) => {
            const $select = $('#import-source');
            $select
              .empty()
              .append('<option value="">Select a source...</option>');

            sources.forEach((source) => {
              $select.append(
                `<option value="${this.escapeHtml(source.key)}">${this.escapeHtml(source.label)}</option>`,
              );
            });

            // Restore previously selected value if it exists
            if (this.importOptions && this.importOptions.source) {
              $select.val(this.importOptions.source);
            }
          })
          .catch((error) => {
            console.error('Error loading sources:', error);
          });
      }

      // Also restore the CSV options (delimiter and encoding)
      if (this.importOptions) {
        if (this.importOptions.delimiter) {
          $('#csv-delimiter').val(this.importOptions.delimiter);
        }
        if (this.importOptions.encoding) {
          $('#csv-encoding').val(this.importOptions.encoding);
        }
      }
    }

    loadUsers() {
      // Use the existing DT users endpoint
      const restUrl = window.wpApiSettings
        ? window.wpApiSettings.root
        : '/wp-json/';
      const usersUrl = `${restUrl}dt/v1/users/get_users`;

      return $.ajax({
        url: usersUrl,
        method: 'GET',
        headers: {
          'X-WP-Nonce': dtImport.nonce,
        },
        data: {
          get_all: 1, // Get all assignable users
          post_type: this.selectedPostType, // Pass the current post type
        },
      }).then((response) => {
        if (response && Array.isArray(response)) {
          return response;
        } else {
          throw new Error('Invalid response format for assignable users');
        }
      });
    }

    loadSources() {
      const fieldSettings = this.getFieldSettingsForPostType();
      const sourcesField = fieldSettings.sources;

      if (sourcesField && sourcesField.default) {
        // Convert sources field options to array format
        const sources = Object.entries(sourcesField.default).map(
          ([key, option]) => ({
            key: key,
            label: option.label || key,
          }),
        );
        return Promise.resolve(sources);
      }

      return Promise.resolve([]);
    }

    // Restore methods for preserving field-specific configurations
    restoreInlineValueMapping(
      columnIndex,
      fieldKey,
      fieldConfig,
      existingMapping,
    ) {
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );
      const $options = $card.find('.field-specific-options');

      // Get unique values from this CSV column (excluding header row)
      this.getColumnUniqueValues(columnIndex)
        .then((uniqueValues) => {
          if (uniqueValues.length === 0) {
            $options.hide().empty();
            return;
          }

          // Get field options
          this.getFieldOptionsForSelect(fieldKey)
            .then((fieldOptions) => {
              const mappingHtml = this.createInlineValueMappingHtml(
                columnIndex,
                fieldKey,
                uniqueValues,
                fieldOptions,
                fieldConfig.type,
              );
              $options.html(mappingHtml).show();

              // Restore existing value mappings instead of auto-mapping
              if (existingMapping.value_mapping) {
                this.restoreInlineValueMappingSelections(
                  columnIndex,
                  existingMapping.value_mapping,
                );
                // Update field mappings with restored values
                this.updateFieldMappingFromInline(columnIndex, fieldKey);
              } else {
                // Apply auto-mapping if no existing mappings
                this.autoMapInlineValues(columnIndex, fieldOptions);
                // Update field mappings with auto-mapped values
                this.updateFieldMappingFromInline(columnIndex, fieldKey);
              }

              // Update count
              this.updateInlineMappingCount(columnIndex);
            })
            .catch((error) => {
              console.error('Error fetching field options:', error);
              $options
                .html('<p style="color: red;">Error loading field options</p>')
                .show();
            });
        })
        .catch((error) => {
          console.error('Error fetching column data:', error);
          $options
            .html('<p style="color: red;">Error loading column data</p>')
            .show();
        });
    }

    restoreInlineValueMappingSelections(columnIndex, valueMappings) {
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );

      // Restore each value mapping
      Object.entries(valueMappings).forEach(([csvValue, dtValue]) => {
        const $select = $card.find(
          `.inline-value-mapping-select[data-csv-value="${csvValue}"]`,
        );
        if ($select.length) {
          $select.val(dtValue);
        }
      });
    }

    restoreDateFormatSelector(columnIndex, fieldKey, existingMapping) {
      // Show the date format selector first
      this.showDateFormatSelector(columnIndex, fieldKey);

      // Then restore the selected value
      setTimeout(() => {
        if (existingMapping.date_format) {
          const $select = $(
            `.column-mapping-card[data-column-index="${columnIndex}"] .date-format-select`,
          );
          if ($select.length) {
            $select.val(existingMapping.date_format);
          }
        }
      }, 50);
    }

    restoreGeocodingServiceSelector(columnIndex, fieldKey, existingMapping) {
      // Show the geocoding service selector first
      this.showGeocodingServiceSelector(columnIndex, fieldKey);

      // Then restore the selected value
      setTimeout(() => {
        if (existingMapping.geocode_service) {
          const isEnabled = existingMapping.geocode_service !== 'none';
          const $checkbox = $(
            `.column-mapping-card[data-column-index="${columnIndex}"] .geocoding-service-checkbox`,
          );
          if ($checkbox.length) {
            $checkbox.prop('checked', isEnabled);
          }
        }
      }, 50);
    }

    restoreDuplicateCheckingOptions(
      columnIndex,
      fieldKey,
      fieldConfig,
      existingMapping,
    ) {
      // Show the duplicate checking options first
      this.showDuplicateCheckingOptions(columnIndex, fieldKey, fieldConfig);

      // Then restore the selected value
      setTimeout(() => {
        if (existingMapping.duplicate_checking !== undefined) {
          const $checkbox = $(
            `.column-mapping-card[data-column-index="${columnIndex}"] .duplicate-checking-checkbox`,
          );
          if ($checkbox.length) {
            $checkbox.prop('checked', existingMapping.duplicate_checking);
          }
        }
      }, 50);
    }

    updateNameFieldWarning() {
      // Check if name field is mapped
      const nameFieldMapped = Object.values(this.fieldMappings).some(
        (mapping) => mapping.field_key === 'name',
      );

      if (nameFieldMapped) {
        $('.name-field-warning').hide();
      } else {
        $('.name-field-warning').show();
      }
    }
  }

  // Initialize when DOM is ready
  $(document).ready(() => {
    if ($('.dt-import-container').length > 0 && !window.dtImportInstance) {
      try {
        window.dtImportInstance = new DTImport();
      } catch (error) {
        console.error('Error initializing DT Import:', error);
      }
    }
  });
})(jQuery);

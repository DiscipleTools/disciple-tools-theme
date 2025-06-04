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

      $('.dt-import-next').prop('disabled', false);
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
                    <h2>Step 1: Select Post Type</h2>
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

      // Debug logging
      console.log('DT Import Debug:');
      console.log('REST URL:', `${dtImport.restUrl}upload`);
      console.log('Nonce:', dtImport.nonce);
      console.log('Post Type:', this.selectedPostType);
      console.log('Nonce length:', dtImport.nonce.length);
      console.log('Nonce type:', typeof dtImport.nonce);

      fetch(`${dtImport.restUrl}upload`, {
        method: 'POST',
        headers: {
          'X-WP-Nonce': dtImport.nonce,
        },
        credentials: 'same-origin',
        body: formData,
      })
        .then((response) => {
          console.log('Response status:', response.status);
          console.log('Response headers:', response.headers);
          console.log('Response ok:', response.ok);

          return response.json().then((data) => {
            console.log('Response data:', data);
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
            this.showStep3(data.data);
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

      const columnsHtml = Object.entries(mappingSuggestions)
        .map(([index, mapping]) => {
          return this.createColumnMappingCard(index, mapping);
        })
        .join('');

      const step3Html = `
                <div class="dt-import-step-content">
                    <h2>${dtImport.translations.mapFields}</h2>
                    <p>Map each CSV column to the appropriate field in Disciple.Tools.</p>
                    
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

      // Initialize field mappings from suggested mappings ONLY if no existing mappings
      if (Object.keys(this.fieldMappings).length === 0) {
        // Initialize from suggestions for first-time display
        Object.entries(mappingSuggestions).forEach(([index, mapping]) => {
          if (mapping.suggested_field) {
            this.fieldMappings[index] = {
              field_key: mapping.suggested_field,
              column_index: parseInt(index),
            };
          }
        });
      }

      // Restore existing field mappings to the dropdowns and read actual dropdown values
      setTimeout(() => {
        // First, restore any existing user mappings to the dropdowns
        Object.entries(this.fieldMappings).forEach(([columnIndex, mapping]) => {
          const $select = $(
            `.field-mapping-select[data-column-index="${columnIndex}"]`,
          );
          if ($select.length && mapping.field_key) {
            $select.val(mapping.field_key);
            this.showFieldSpecificOptions(
              parseInt(columnIndex),
              mapping.field_key,
            );
          }
        });

        // Read the actual dropdown values to ensure mappings match what's displayed
        // This handles cases where suggestions were auto-selected but user wants different behavior
        const actualMappings = {};
        $('.field-mapping-select').each((index, select) => {
          const $select = $(select);
          const columnIndex = $select.data('column-index');
          const fieldKey = $select.val();

          // Only create mapping if a field is actually selected (not empty)
          if (fieldKey && fieldKey !== '' && fieldKey !== 'create_new') {
            actualMappings[columnIndex] = {
              field_key: fieldKey,
              column_index: parseInt(columnIndex),
            };
          }
        });

        // Update field mappings to match actual dropdown state
        this.fieldMappings = actualMappings;
        console.log(
          'DT Import: Field mappings synchronized with dropdown state:',
          this.fieldMappings,
        );

        // Update the summary after mappings are initialized
        this.updateMappingSummary();
      }, 100);

      this.updateNavigation();
    }

    createColumnMappingCard(columnIndex, mapping) {
      const confidenceClass =
        mapping.confidence >= 80
          ? 'high'
          : mapping.confidence >= 60
            ? 'medium'
            : 'low';

      const sampleDataHtml = mapping.sample_data
        .slice(0, 3)
        .map((sample) => `<li>${this.escapeHtml(sample)}</li>`)
        .join('');

      // Check if there's an existing user mapping for this column
      const existingMapping = this.fieldMappings[columnIndex];
      const selectedField = existingMapping
        ? existingMapping.field_key
        : mapping.suggested_field; // Restore auto-mapping

      return `
                <div class="column-mapping-card" data-column-index="${columnIndex}">
                    <div class="column-header">
                        <h4>${this.escapeHtml(mapping.column_name)}</h4>
                        ${
                          mapping.confidence > 0 && !existingMapping
                            ? `
                            <div class="confidence-indicator confidence-${confidenceClass}">
                                ${mapping.confidence}% confidence
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
                            ${this.getFieldOptions(selectedField)}
                            <option value="create_new">+ Create New Field</option>
                        </select>
                        
                        <div class="field-specific-options" style="display: none;"></div>
                    </div>
                </div>
            `;
    }

    getFieldOptions(suggestedField) {
      const fieldSettings = this.getFieldSettingsForPostType();

      // Filter out hidden fields and ensure we have valid field configurations
      const validFields = Object.entries(fieldSettings).filter(
        ([fieldKey, fieldConfig]) => {
          return (
            fieldConfig &&
            fieldConfig.name &&
            fieldConfig.type &&
            !fieldConfig.hidden &&
            fieldConfig.customizable !== false
          );
        },
      );

      return validFields
        .map(([fieldKey, fieldConfig]) => {
          const selected = fieldKey === suggestedField ? 'selected' : '';
          const fieldName = this.escapeHtml(fieldConfig.name);
          const fieldType = this.escapeHtml(fieldConfig.type);
          return `<option value="${fieldKey}" ${selected} data-field-type="${fieldType}">
                    ${fieldName} (${fieldType})
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

      console.log(
        `DT Import: Column ${columnIndex} field mapping changed to: "${fieldKey}"`,
      );

      // Store mapping - properly handle empty values for "do not import"
      if (fieldKey && fieldKey !== '') {
        this.fieldMappings[columnIndex] = {
          field_key: fieldKey,
          column_index: columnIndex,
        };
        console.log(
          `DT Import: Added mapping for column ${columnIndex} -> ${fieldKey}`,
        );
      } else {
        // When "do not import" is selected (empty value), remove the mapping entirely
        delete this.fieldMappings[columnIndex];
        console.log(
          `DT Import: Removed mapping for column ${columnIndex} (do not import)`,
        );
      }

      console.log('DT Import: Current field mappings:', this.fieldMappings);

      // Show field-specific options if needed
      this.showFieldSpecificOptions(columnIndex, fieldKey);
      this.updateMappingSummary();
    }

    showFieldSpecificOptions(columnIndex, fieldKey) {
      const $card = $(
        `.column-mapping-card[data-column-index="${columnIndex}"]`,
      );
      const $options = $card.find('.field-specific-options');

      if (!fieldKey) {
        $options.hide().empty();
        return;
      }

      const fieldSettings = this.getFieldSettingsForPostType();
      const fieldConfig = fieldSettings[fieldKey];

      if (!fieldConfig) {
        $options.hide().empty();
        return;
      }

      if (['key_select', 'multi_select'].includes(fieldConfig.type)) {
        this.showInlineValueMapping(columnIndex, fieldKey, fieldConfig);
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
      if (Object.keys(this.fieldMappings).length === 0) {
        this.showError('Please map at least one field before proceeding.');
        return;
      }

      console.log(
        'DT Import: Saving field mappings to server:',
        this.fieldMappings,
      );
      this.showProcessing('Saving field mappings...');

      fetch(`${dtImport.restUrl}${this.sessionId}/mapping`, {
        method: 'POST',
        headers: {
          'X-WP-Nonce': dtImport.nonce,
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          mappings: this.fieldMappings,
        }),
      })
        .then((response) => response.json())
        .then((data) => {
          this.hideProcessing();

          if (data.success) {
            console.log('DT Import: Field mappings saved successfully');
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

      const step4Html = `
                <div class="dt-import-step-content">
                    <h2>${dtImport.translations.previewImport}</h2>
                    <p>Review the data before importing ${previewData.total_rows} records.</p>
                    
                    <div class="preview-stats">
                        <div class="stat-card">
                            <h3>${previewData.total_rows}</h3>
                            <p>Total Records</p>
                        </div>
                        <div class="stat-card">
                            <h3>${previewData.processable_count}</h3>
                            <p>Will Import</p>
                        </div>
                        ${
                          totalWarnings > 0
                            ? `
                        <div class="stat-card warning">
                            <h3>${totalWarnings}</h3>
                            <p>Warnings</p>
                        </div>
                        `
                            : ''
                        }
                        <div class="stat-card error">
                            <h3>${previewData.error_count}</h3>
                            <p>Errors</p>
                        </div>
                    </div>
                    
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
                    
                    <div class="import-actions">
                        <button type="button" class="button button-primary execute-import-btn">
                            Import ${previewData.processable_count} Records
                        </button>
                    </div>
                </div>
            `;

      $('.dt-import-step-content').html(step4Html);
      this.updateNavigation();
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

      const headerHtml = headers
        .map((header) => `<th>${this.escapeHtml(header)}</th>`)
        .join('');

      const rowsHtml = rows
        .map((row) => {
          const hasWarnings = row.warnings && row.warnings.length > 0;
          const rowClass = row.has_errors
            ? 'error-row'
            : hasWarnings
              ? 'warning-row'
              : '';

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
              <td colspan="${headers.length}">
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

          return `<tr class="${rowClass}">${cellsHtml}</tr>${warningsHtml}`;
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
            this.startProgressPolling();
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
    }

    startProgressPolling() {
      const pollInterval = setInterval(() => {
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
              } else if (status === 'failed') {
                clearInterval(pollInterval);
                this.isProcessing = false;
                this.hideProcessing();
                this.showError('Import failed');
              }
            }
          })
          .catch((error) => {
            console.error('Status polling error:', error);
          });
      }, 2000); // Poll every 2 seconds
    }

    updateProgress(progress, status) {
      $('.processing-message').text(`Importing records... ${progress}%`);
      // You could add a progress bar here
    }

    showImportResults(results) {
      const resultsHtml = `
                <div class="import-results">
                    <h2>Import Complete!</h2>
                    <div class="results-stats">
                        <div class="stat-card success">
                            <h3>${results.records_imported}</h3>
                            <p>Records Imported</p>
                        </div>
                        ${
                          results.errors && results.errors.length > 0
                            ? `
                            <div class="stat-card error">
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
        case 3:
          return Object.keys(this.fieldMappings).length > 0;
        default:
          return true;
      }
    }

    updateMappingSummary() {
      const mappedCount = Object.keys(this.fieldMappings).length;
      const totalColumns = $('.column-mapping-card').length;

      $('.mapping-summary').show();
      $('.summary-stats').html(`
                <p>${mappedCount} of ${totalColumns} columns mapped</p>
            `);

      $('.dt-import-next').prop('disabled', mappedCount === 0);
    }

    showProcessing(message) {
      $('.dt-import-container').append(`
                <div class="processing-overlay">
                    <div class="processing-message">
                        <div class="spinner"></div>
                        <p>${message}</p>
                    </div>
                </div>
            `);
    }

    hideProcessing() {
      $('.processing-overlay').remove();
    }

    showError(message) {
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

    showSuccess(message) {
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
            if (typeof item === 'object' && item.value !== undefined) {
              return item.value;
            }
            return item;
          })
          .join('; ');
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

    markStepAsCompleted() {
      // Mark step 4 as completed (green) and remove active state
      $('.dt-import-steps .step').each((index, step) => {
        const stepNum = index + 1;
        if (stepNum === 4) {
          $(step).removeClass('active').addClass('completed');
        }
      });
    }
  }

  // Initialize when DOM is ready
  $(document).ready(() => {
    if ($('.dt-import-container').length > 0 && !window.dtImportInstance) {
      try {
        window.dtImportInstance = new DTImport();
        console.log('DT Import initialized successfully');
      } catch (error) {
        console.error('Error initializing DT Import:', error);
      }
    }
  });
})(jQuery);

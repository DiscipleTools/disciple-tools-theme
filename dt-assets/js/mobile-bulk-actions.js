/**
 * Mobile Bulk Actions Manager
 * Handles advanced bulk operations for mobile contact management
 * 
 * Features:
 * - Multi-select with gesture support
 * - Drag and drop organization
 * - Advanced bulk operations
 * - Undo/redo functionality
 * - Progress tracking and cancellation
 */

class MobileBulkActions {
  constructor() {
    this.selectedItems = new Set();
    this.isSelectionMode = false;
    this.draggedItems = [];
    this.operationHistory = [];
    this.historyIndex = -1;
    this.currentOperation = null;
    this.operationQueue = [];
    
    // Configuration
    this.config = {
      maxHistory: 20,
      longPressDelay: 500,
      dragThreshold: 10,
      maxBatchSize: 50,
      progressUpdateInterval: 100
    };
    
    // State management
    this.state = {
      isProcessing: false,
      cancelRequested: false,
      lastAction: null,
      dragStartPosition: null,
      activeModal: null
    };
    
    this.init();
  }
  
  /**
   * Initialize bulk actions system
   */
  init() {
    console.log('Bulk Actions: Initializing...');
    
    this.setupEventListeners();
    this.setupGestureHandlers();
    this.setupKeyboardShortcuts();
    this.loadOperationHistory();
    
    console.log('Bulk Actions: Initialized successfully');
  }
  
  /**
   * Set up event listeners
   */
  setupEventListeners() {
    // Bulk action buttons
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('mobile-bulk-action')) {
        this.handleBulkAction(e.target.dataset.action);
      }
      
      if (e.target.classList.contains('mobile-bulk-action-item')) {
        this.handleBulkAction(e.target.dataset.action);
      }
    });
    
    // Selection controls
    document.addEventListener('click', (e) => {
      if (e.target.id === 'mobile-bulk-clear') {
        this.clearSelection();
      }
      
      if (e.target.id === 'mobile-bulk-toggle') {
        this.toggleExpandedActions();
      }
    });
    
    // History controls
    document.addEventListener('click', (e) => {
      if (e.target.id === 'mobile-bulk-undo') {
        this.undo();
      }
      
      if (e.target.id === 'mobile-bulk-redo') {
        this.redo();
      }
    });
    
    // Modal controls
    document.addEventListener('click', (e) => {
      if (e.target.classList.contains('mobile-bulk-modal-close')) {
        this.closeModal(e.target.dataset.modal);
      }
      
      if (e.target.classList.contains('mobile-bulk-cancel')) {
        this.closeModal(e.target.dataset.modal);
      }
    });
    
    // Contact card selection
    document.addEventListener('change', (e) => {
      if (e.target.classList.contains('mobile-contact-checkbox')) {
        this.handleContactSelection(e.target);
      }
    });
    
    // Progress modal controls
    document.addEventListener('click', (e) => {
      if (e.target.id === 'mobile-bulk-cancel-operation') {
        this.cancelCurrentOperation();
      }
    });
  }
  
  /**
   * Setup gesture handlers for mobile interactions
   */
  setupGestureHandlers() {
    // Long press to enter selection mode
    document.addEventListener('touchstart', (e) => {
      if (e.target.closest('.mobile-contact-card')) {
        this.handleTouchStart(e);
      }
    });
    
    document.addEventListener('touchend', (e) => {
      this.handleTouchEnd(e);
    });
    
    // Drag and drop for contact organization
    document.addEventListener('dragstart', (e) => {
      if (e.target.closest('.mobile-contact-card')) {
        this.handleDragStart(e);
      }
    });
    
    document.addEventListener('dragover', (e) => {
      e.preventDefault();
      this.handleDragOver(e);
    });
    
    document.addEventListener('drop', (e) => {
      e.preventDefault();
      this.handleDrop(e);
    });
  }
  
  /**
   * Setup keyboard shortcuts
   */
  setupKeyboardShortcuts() {
    document.addEventListener('keydown', (e) => {
      // Ctrl/Cmd + A - Select all
      if ((e.ctrlKey || e.metaKey) && e.key === 'a' && this.isSelectionMode) {
        e.preventDefault();
        this.selectAll();
      }
      
      // Escape - Clear selection
      if (e.key === 'Escape') {
        this.clearSelection();
        this.closeAllModals();
      }
      
      // Ctrl/Cmd + Z - Undo
      if ((e.ctrlKey || e.metaKey) && e.key === 'z' && !e.shiftKey) {
        e.preventDefault();
        this.undo();
      }
      
      // Ctrl/Cmd + Shift + Z - Redo
      if ((e.ctrlKey || e.metaKey) && e.key === 'z' && e.shiftKey) {
        e.preventDefault();
        this.redo();
      }
      
      // Delete - Delete selected items
      if (e.key === 'Delete' && this.selectedItems.size > 0) {
        this.handleBulkAction('delete');
      }
    });
  }
  
  /**
   * Handle touch start for long press detection
   */
  handleTouchStart(e) {
    const card = e.target.closest('.mobile-contact-card');
    if (!card) return;
    
    this.longPressTimer = setTimeout(() => {
      this.enterSelectionMode(card);
      
      // Haptic feedback if available
      if (navigator.vibrate) {
        navigator.vibrate(50);
      }
    }, this.config.longPressDelay);
  }
  
  /**
   * Handle touch end
   */
  handleTouchEnd(e) {
    if (this.longPressTimer) {
      clearTimeout(this.longPressTimer);
      this.longPressTimer = null;
    }
  }
  
  /**
   * Enter selection mode
   */
  enterSelectionMode(initialCard) {
    console.log('Bulk Actions: Entering selection mode');
    
    this.isSelectionMode = true;
    document.body.classList.add('mobile-selection-mode');
    
    // Show selection checkboxes
    document.querySelectorAll('.mobile-contact-card').forEach(card => {
      card.classList.add('selection-mode');
    });
    
    // Select initial card if provided
    if (initialCard) {
      this.selectContact(initialCard);
    }
    
    // Show bulk actions panel
    this.showBulkActionsPanel();
    
    // Update UI
    this.updateSelectionUI();
  }
  
  /**
   * Exit selection mode
   */
  exitSelectionMode() {
    console.log('Bulk Actions: Exiting selection mode');
    
    this.isSelectionMode = false;
    document.body.classList.remove('mobile-selection-mode');
    
    // Hide selection checkboxes
    document.querySelectorAll('.mobile-contact-card').forEach(card => {
      card.classList.remove('selection-mode', 'selected');
    });
    
    // Hide bulk actions panel
    this.hideBulkActionsPanel();
    
    // Clear selection
    this.selectedItems.clear();
  }
  
  /**
   * Handle contact selection
   */
  handleContactSelection(checkbox) {
    const card = checkbox.closest('.mobile-contact-card');
    const contactId = card.dataset.contactId;
    
    if (checkbox.checked) {
      this.selectContact(card);
    } else {
      this.deselectContact(card);
    }
    
    this.updateSelectionUI();
    
    // Exit selection mode if no items selected
    if (this.selectedItems.size === 0) {
      this.exitSelectionMode();
    }
  }
  
  /**
   * Select a contact
   */
  selectContact(card) {
    const contactId = card.dataset.contactId;
    
    this.selectedItems.add(contactId);
    card.classList.add('selected');
    
    const checkbox = card.querySelector('.mobile-contact-checkbox');
    if (checkbox) {
      checkbox.checked = true;
    }
    
    // Enter selection mode if not already active
    if (!this.isSelectionMode) {
      this.enterSelectionMode();
    }
  }
  
  /**
   * Deselect a contact
   */
  deselectContact(card) {
    const contactId = card.dataset.contactId;
    
    this.selectedItems.delete(contactId);
    card.classList.remove('selected');
    
    const checkbox = card.querySelector('.mobile-contact-checkbox');
    if (checkbox) {
      checkbox.checked = false;
    }
  }
  
  /**
   * Select all visible contacts
   */
  selectAll() {
    document.querySelectorAll('.mobile-contact-card').forEach(card => {
      this.selectContact(card);
    });
    
    this.updateSelectionUI();
  }
  
  /**
   * Clear all selections
   */
  clearSelection() {
    this.selectedItems.clear();
    
    document.querySelectorAll('.mobile-contact-card').forEach(card => {
      this.deselectContact(card);
    });
    
    this.exitSelectionMode();
  }
  
  /**
   * Update selection UI
   */
  updateSelectionUI() {
    const count = this.selectedItems.size;
    const countElement = document.getElementById('mobile-bulk-count');
    
    if (countElement) {
      countElement.textContent = `${count} selected`;
    }
    
    // Update modal counters
    ['assign', 'status', 'tag'].forEach(modalType => {
      const element = document.getElementById(`${modalType}-selected-count`);
      if (element) {
        element.textContent = count;
      }
    });
    
    // Enable/disable actions based on selection
    const hasSelection = count > 0;
    document.querySelectorAll('.mobile-bulk-action, .mobile-bulk-action-item').forEach(button => {
      button.disabled = !hasSelection;
    });
  }
  
  /**
   * Show bulk actions panel
   */
  showBulkActionsPanel() {
    const panel = document.getElementById('mobile-bulk-actions');
    if (panel) {
      panel.style.display = 'block';
      panel.classList.add('mobile-slide-up');
    }
  }
  
  /**
   * Hide bulk actions panel
   */
  hideBulkActionsPanel() {
    const panel = document.getElementById('mobile-bulk-actions');
    if (panel) {
      panel.style.display = 'none';
      panel.classList.remove('mobile-slide-up');
    }
  }
  
  /**
   * Toggle expanded actions
   */
  toggleExpandedActions() {
    const expanded = document.getElementById('mobile-bulk-expanded');
    const toggle = document.getElementById('mobile-bulk-toggle');
    
    if (expanded && toggle) {
      const isExpanded = expanded.style.display !== 'none';
      
      expanded.style.display = isExpanded ? 'none' : 'block';
      toggle.querySelector('i').className = isExpanded ? 'mdi mdi-chevron-up' : 'mdi mdi-chevron-down';
    }
  }
  
  /**
   * Handle bulk action execution
   */
  async handleBulkAction(action) {
    if (this.selectedItems.size === 0) {
      this.showToast('No contacts selected', 'warning');
      return;
    }
    
    console.log(`Bulk Actions: Executing ${action} on ${this.selectedItems.size} contacts`);
    
    switch (action) {
      case 'assign':
        this.showAssignModal();
        break;
        
      case 'status':
        this.showStatusModal();
        break;
        
      case 'tag':
        this.showTagModal();
        break;
        
      case 'delete':
        this.confirmDangerousAction('delete', 'Delete Contacts', 
          `Are you sure you want to delete ${this.selectedItems.size} contacts?`);
        break;
        
      case 'export':
        await this.executeExport();
        break;
        
      case 'message':
        this.showMessageModal();
        break;
        
      case 'more':
        this.toggleExpandedActions();
        break;
        
      default:
        console.warn('Bulk Actions: Unknown action:', action);
    }
  }
  
  /**
   * Show assign modal
   */
  async showAssignModal() {
    await this.loadUsers();
    this.showModal('assign');
    
    // Setup assign confirmation
    document.getElementById('mobile-bulk-assign-confirm').onclick = () => {
      this.executeAssign();
    };
  }
  
  /**
   * Show status modal
   */
  showStatusModal() {
    this.showModal('status');
    
    // Setup status change handling
    const statusSelect = document.getElementById('mobile-bulk-status-value');
    const reasonGroup = document.getElementById('mobile-bulk-reason-group');
    
    statusSelect.onchange = () => {
      const status = statusSelect.value;
      if (status === 'closed' || status === 'paused') {
        reasonGroup.style.display = 'block';
        this.loadReasons(status);
      } else {
        reasonGroup.style.display = 'none';
      }
    };
    
    // Setup status confirmation
    document.getElementById('mobile-bulk-status-confirm').onclick = () => {
      this.executeStatusUpdate();
    };
  }
  
  /**
   * Show tag modal
   */
  async showTagModal() {
    await this.loadTags();
    this.showModal('tag');
    
    // Setup tag search
    const tagSearch = document.getElementById('mobile-bulk-tag-search');
    let searchTimeout;
    
    tagSearch.oninput = () => {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(() => {
        this.searchTags(tagSearch.value);
      }, 300);
    };
    
    // Setup tag confirmation
    document.getElementById('mobile-bulk-tag-confirm').onclick = () => {
      this.executeTagUpdate();
    };
  }
  
  /**
   * Execute assign operation
   */
  async executeAssign() {
    const userId = document.getElementById('mobile-bulk-assign-user').value;
    const notify = document.getElementById('mobile-bulk-assign-notify').checked;
    const note = document.getElementById('mobile-bulk-assign-note').value;
    
    if (!userId) {
      this.showToast('Please select a user to assign to', 'error');
      return;
    }
    
    this.closeModal('assign');
    
    const operation = {
      type: 'assign',
      data: { userId, notify, note },
      contacts: Array.from(this.selectedItems),
      timestamp: Date.now()
    };
    
    await this.executeOperation(operation);
  }
  
  /**
   * Execute status update operation
   */
  async executeStatusUpdate() {
    const status = document.getElementById('mobile-bulk-status-value').value;
    const reason = document.getElementById('mobile-bulk-reason-value').value;
    const note = document.getElementById('mobile-bulk-status-note').value;
    
    this.closeModal('status');
    
    const operation = {
      type: 'status',
      data: { status, reason, note },
      contacts: Array.from(this.selectedItems),
      timestamp: Date.now()
    };
    
    await this.executeOperation(operation);
  }
  
  /**
   * Execute tag update operation
   */
  async executeTagUpdate() {
    const addTags = this.getSelectedTags();
    const removeTags = this.getTagsToRemove();
    
    this.closeModal('tag');
    
    const operation = {
      type: 'tags',
      data: { addTags, removeTags },
      contacts: Array.from(this.selectedItems),
      timestamp: Date.now()
    };
    
    await this.executeOperation(operation);
  }
  
  /**
   * Execute bulk operation with progress tracking
   */
  async executeOperation(operation) {
    if (this.state.isProcessing) {
      console.warn('Bulk Actions: Another operation is already in progress');
      return;
    }
    
    this.state.isProcessing = true;
    this.state.cancelRequested = false;
    this.currentOperation = operation;
    
    try {
      // Show progress modal
      this.showProgressModal(operation);
      
      // Add to history before execution
      this.addToHistory(operation);
      
      // Execute operation in batches
      const results = await this.executeBatchOperation(operation);
      
      // Hide progress modal
      this.hideProgressModal();
      
      // Show success message
      this.showToast(`${operation.type} completed successfully for ${results.success} contacts`, 'success');
      
      // Clear selection
      this.clearSelection();
      
      // Refresh contact list
      if (window.mobileAPI && window.mobileAPI.refreshContacts) {
        window.mobileAPI.refreshContacts();
      }
      
    } catch (error) {
      console.error('Bulk Actions: Operation failed:', error);
      this.hideProgressModal();
      this.showToast(`Operation failed: ${error.message}`, 'error');
    } finally {
      this.state.isProcessing = false;
      this.currentOperation = null;
    }
  }
  
  /**
   * Execute operation in batches with progress updates
   */
  async executeBatchOperation(operation) {
    const contacts = operation.contacts;
    const batchSize = this.config.maxBatchSize;
    const totalBatches = Math.ceil(contacts.length / batchSize);
    
    let processed = 0;
    let success = 0;
    let errors = [];
    
    for (let i = 0; i < totalBatches && !this.state.cancelRequested; i++) {
      const batchStart = i * batchSize;
      const batchEnd = Math.min(batchStart + batchSize, contacts.length);
      const batch = contacts.slice(batchStart, batchEnd);
      
      try {
        // Update progress
        this.updateProgress(processed, contacts.length, `Processing batch ${i + 1} of ${totalBatches}...`);
        
        // Execute batch
        const batchResult = await this.executeBatch(operation.type, operation.data, batch);
        
        success += batchResult.success || 0;
        if (batchResult.errors) {
          errors = errors.concat(batchResult.errors);
        }
        
        processed += batch.length;
        
        // Small delay to prevent overwhelming the server
        await new Promise(resolve => setTimeout(resolve, 100));
        
      } catch (error) {
        console.error(`Bulk Actions: Batch ${i + 1} failed:`, error);
        errors.push({ batch: i + 1, error: error.message });
        processed += batch.length;
      }
    }
    
    return { success, errors, processed, cancelled: this.state.cancelRequested };
  }
  
  /**
   * Execute a single batch
   */
  async executeBatch(operationType, data, contactIds) {
    const endpoint = this.getBatchEndpoint(operationType);
    
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': window.wpApiShare.nonce
      },
      body: JSON.stringify({
        contacts: contactIds,
        data: data
      })
    });
    
    if (!response.ok) {
      throw new Error(`HTTP ${response.status}: ${response.statusText}`);
    }
    
    return await response.json();
  }
  
  /**
   * Get API endpoint for batch operation
   */
  getBatchEndpoint(operationType) {
    const baseUrl = window.wpApiShare.root + 'dt/v1/';
    
    switch (operationType) {
      case 'assign':
        return `${baseUrl}contacts/batch/assign`;
      case 'status':
        return `${baseUrl}contacts/batch/status`;
      case 'tags':
        return `${baseUrl}contacts/batch/tags`;
      case 'delete':
        return `${baseUrl}contacts/batch/delete`;
      default:
        throw new Error(`Unknown operation type: ${operationType}`);
    }
  }
  
  /**
   * Show progress modal
   */
  showProgressModal(operation) {
    const modal = document.getElementById('mobile-bulk-progress-modal');
    const title = document.getElementById('mobile-bulk-progress-title');
    const total = document.getElementById('mobile-bulk-progress-total');
    
    if (modal && title && total) {
      title.textContent = `${this.capitalizeFirst(operation.type)} Contacts`;
      total.textContent = operation.contacts.length;
      modal.style.display = 'block';
    }
  }
  
  /**
   * Update progress
   */
  updateProgress(current, total, details) {
    const currentEl = document.getElementById('mobile-bulk-progress-current');
    const fillEl = document.getElementById('mobile-bulk-progress-fill');
    const detailsEl = document.getElementById('mobile-bulk-progress-details');
    
    if (currentEl) currentEl.textContent = current;
    if (detailsEl) detailsEl.textContent = details;
    
    if (fillEl) {
      const percentage = (current / total) * 100;
      fillEl.style.width = `${percentage}%`;
    }
  }
  
  /**
   * Hide progress modal
   */
  hideProgressModal() {
    const modal = document.getElementById('mobile-bulk-progress-modal');
    if (modal) {
      modal.style.display = 'none';
    }
  }
  
  /**
   * Cancel current operation
   */
  cancelCurrentOperation() {
    this.state.cancelRequested = true;
    this.showToast('Operation cancelled', 'info');
    this.hideProgressModal();
  }
  
  /**
   * Utility Functions
   */
  
  showModal(modalType) {
    const modal = document.getElementById(`mobile-bulk-${modalType}-modal`);
    if (modal) {
      modal.style.display = 'block';
      this.state.activeModal = modalType;
    }
  }
  
  closeModal(modalType) {
    const modal = document.getElementById(`mobile-bulk-${modalType}-modal`);
    if (modal) {
      modal.style.display = 'none';
      this.state.activeModal = null;
    }
  }
  
  closeAllModals() {
    document.querySelectorAll('.mobile-bulk-modal').forEach(modal => {
      modal.style.display = 'none';
    });
    this.state.activeModal = null;
  }
  
  showToast(message, type = 'info') {
    const toast = document.getElementById('mobile-bulk-toast');
    const messageEl = document.getElementById('mobile-bulk-toast-message');
    const iconEl = toast.querySelector('.mobile-bulk-toast-icon i');
    
    if (toast && messageEl) {
      messageEl.textContent = message;
      
      // Update icon based on type
      const iconClass = {
        success: 'mdi-check-circle',
        error: 'mdi-alert-circle',
        warning: 'mdi-alert',
        info: 'mdi-information'
      }[type] || 'mdi-information';
      
      iconEl.className = `mdi ${iconClass}`;
      
      // Show toast
      toast.style.display = 'block';
      toast.className = `mobile-bulk-toast ${type}`;
      
      // Auto-hide after 5 seconds
      setTimeout(() => {
        if (toast.style.display !== 'none') {
          toast.style.display = 'none';
        }
      }, 5000);
    }
    
    // Close button
    document.getElementById('mobile-bulk-toast-close').onclick = () => {
      toast.style.display = 'none';
    };
  }
  
  capitalizeFirst(str) {
    return str.charAt(0).toUpperCase() + str.slice(1);
  }
  
  /**
   * History Management
   */
  
  addToHistory(operation) {
    // Remove any future history if we're not at the end
    if (this.historyIndex < this.operationHistory.length - 1) {
      this.operationHistory = this.operationHistory.slice(0, this.historyIndex + 1);
    }
    
    // Add new operation
    this.operationHistory.push(operation);
    this.historyIndex++;
    
    // Limit history size
    if (this.operationHistory.length > this.config.maxHistory) {
      this.operationHistory.shift();
      this.historyIndex--;
    }
    
    this.updateHistoryButtons();
    this.saveOperationHistory();
  }
  
  undo() {
    if (this.historyIndex >= 0) {
      const operation = this.operationHistory[this.historyIndex];
      this.revertOperation(operation);
      this.historyIndex--;
      this.updateHistoryButtons();
    }
  }
  
  redo() {
    if (this.historyIndex < this.operationHistory.length - 1) {
      this.historyIndex++;
      const operation = this.operationHistory[this.historyIndex];
      this.executeOperation(operation);
      this.updateHistoryButtons();
    }
  }
  
  updateHistoryButtons() {
    const undoBtn = document.getElementById('mobile-bulk-undo');
    const redoBtn = document.getElementById('mobile-bulk-redo');
    
    if (undoBtn) {
      undoBtn.disabled = this.historyIndex < 0;
    }
    
    if (redoBtn) {
      redoBtn.disabled = this.historyIndex >= this.operationHistory.length - 1;
    }
  }
  
  loadOperationHistory() {
    try {
      const saved = localStorage.getItem('dt_bulk_operations_history');
      if (saved) {
        const parsed = JSON.parse(saved);
        this.operationHistory = parsed.history || [];
        this.historyIndex = parsed.index || -1;
        this.updateHistoryButtons();
      }
    } catch (error) {
      console.warn('Bulk Actions: Failed to load operation history:', error);
    }
  }
  
  saveOperationHistory() {
    try {
      localStorage.setItem('dt_bulk_operations_history', JSON.stringify({
        history: this.operationHistory,
        index: this.historyIndex
      }));
    } catch (error) {
      console.warn('Bulk Actions: Failed to save operation history:', error);
    }
  }
  
  /**
   * Data Loading Functions
   */
  
  async loadUsers() {
    try {
      const response = await fetch(`${window.wpApiShare.root}dt/v1/users/get_users`, {
        headers: {
          'X-WP-Nonce': window.wpApiShare.nonce
        }
      });
      
      const users = await response.json();
      const select = document.getElementById('mobile-bulk-assign-user');
      
      if (select) {
        select.innerHTML = '<option value="">Select user...</option>';
        users.forEach(user => {
          const option = document.createElement('option');
          option.value = user.ID;
          option.textContent = user.display_name;
          select.appendChild(option);
        });
      }
    } catch (error) {
      console.error('Bulk Actions: Failed to load users:', error);
    }
  }
  
  async loadTags() {
    // Implementation for loading tags
    console.log('Loading tags...');
  }
  
  async loadReasons(status) {
    // Implementation for loading reasons based on status
    console.log('Loading reasons for status:', status);
  }
  
  searchTags(query) {
    // Implementation for tag search
    console.log('Searching tags:', query);
  }
  
  getSelectedTags() {
    // Implementation to get selected tags
    return [];
  }
  
  getTagsToRemove() {
    // Implementation to get tags to remove
    return [];
  }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
  module.exports = MobileBulkActions;
} 
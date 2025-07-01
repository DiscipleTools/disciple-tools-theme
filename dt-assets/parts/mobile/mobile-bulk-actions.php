<?php
/**
 * Mobile Bulk Actions Component
 * Advanced bulk operations interface for mobile devices
 * 
 * Features:
 * - Multi-select with gestures
 * - Drag and drop organization
 * - Advanced bulk operations
 * - Undo/redo functionality
 * - Selection state management
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="mobile-bulk-actions" id="mobile-bulk-actions" style="display: none;">
    <!-- Selection Header -->
    <div class="mobile-bulk-header">
        <div class="mobile-bulk-info">
            <span class="mobile-bulk-count" id="mobile-bulk-count">0 selected</span>
            <button class="mobile-bulk-clear" id="mobile-bulk-clear">
                <i class="mdi mdi-close"></i> Clear
            </button>
        </div>
        <div class="mobile-bulk-actions-toggle">
            <button class="mobile-bulk-toggle" id="mobile-bulk-toggle">
                <i class="mdi mdi-chevron-up"></i>
            </button>
        </div>
    </div>

    <!-- Quick Actions Bar -->
    <div class="mobile-bulk-quick-actions">
        <button class="mobile-bulk-action" data-action="assign" title="Assign">
            <i class="mdi mdi-account-plus"></i>
            <span>Assign</span>
        </button>
        <button class="mobile-bulk-action" data-action="status" title="Status">
            <i class="mdi mdi-flag"></i>
            <span>Status</span>
        </button>
        <button class="mobile-bulk-action" data-action="tag" title="Tag">
            <i class="mdi mdi-tag"></i>
            <span>Tag</span>
        </button>
        <button class="mobile-bulk-action" data-action="more" title="More">
            <i class="mdi mdi-dots-horizontal"></i>
            <span>More</span>
        </button>
    </div>

    <!-- Expanded Actions Panel -->
    <div class="mobile-bulk-expanded" id="mobile-bulk-expanded" style="display: none;">
        <div class="mobile-bulk-section">
            <h4 class="mobile-bulk-section-title">Contact Actions</h4>
            <div class="mobile-bulk-action-grid">
                <button class="mobile-bulk-action-item" data-action="assign">
                    <i class="mdi mdi-account-plus"></i>
                    <span>Assign To</span>
                </button>
                <button class="mobile-bulk-action-item" data-action="status">
                    <i class="mdi mdi-flag"></i>
                    <span>Update Status</span>
                </button>
                <button class="mobile-bulk-action-item" data-action="seeker-path">
                    <i class="mdi mdi-map-marker-path"></i>
                    <span>Seeker Path</span>
                </button>
                <button class="mobile-bulk-action-item" data-action="reason-closed">
                    <i class="mdi mdi-close-circle"></i>
                    <span>Reason Closed</span>
                </button>
                <button class="mobile-bulk-action-item" data-action="reason-paused">
                    <i class="mdi mdi-pause-circle"></i>
                    <span>Reason Paused</span>
                </button>
                <button class="mobile-bulk-action-item" data-action="follow-up">
                    <i class="mdi mdi-calendar-clock"></i>
                    <span>Follow-up Date</span>
                </button>
            </div>
        </div>

        <div class="mobile-bulk-section">
            <h4 class="mobile-bulk-section-title">Communication</h4>
            <div class="mobile-bulk-action-grid">
                <button class="mobile-bulk-action-item" data-action="message">
                    <i class="mdi mdi-message"></i>
                    <span>Send Message</span>
                </button>
                <button class="mobile-bulk-action-item" data-action="email">
                    <i class="mdi mdi-email"></i>
                    <span>Send Email</span>
                </button>
                <button class="mobile-bulk-action-item" data-action="call">
                    <i class="mdi mdi-phone"></i>
                    <span>Schedule Call</span>
                </button>
                <button class="mobile-bulk-action-item" data-action="meeting">
                    <i class="mdi mdi-calendar"></i>
                    <span>Schedule Meeting</span>
                </button>
            </div>
        </div>

        <div class="mobile-bulk-section">
            <h4 class="mobile-bulk-section-title">Organization</h4>
            <div class="mobile-bulk-action-grid">
                <button class="mobile-bulk-action-item" data-action="tag">
                    <i class="mdi mdi-tag"></i>
                    <span>Add Tags</span>
                </button>
                <button class="mobile-bulk-action-item" data-action="remove-tag">
                    <i class="mdi mdi-tag-remove"></i>
                    <span>Remove Tags</span>
                </button>
                <button class="mobile-bulk-action-item" data-action="group">
                    <i class="mdi mdi-account-group"></i>
                    <span>Add to Group</span>
                </button>
                <button class="mobile-bulk-action-item" data-action="location">
                    <i class="mdi mdi-map-marker"></i>
                    <span>Set Location</span>
                </button>
            </div>
        </div>

        <div class="mobile-bulk-section">
            <h4 class="mobile-bulk-section-title">Data Management</h4>
            <div class="mobile-bulk-action-grid">
                <button class="mobile-bulk-action-item" data-action="export">
                    <i class="mdi mdi-export"></i>
                    <span>Export</span>
                </button>
                <button class="mobile-bulk-action-item" data-action="duplicate">
                    <i class="mdi mdi-content-copy"></i>
                    <span>Duplicate</span>
                </button>
                <button class="mobile-bulk-action-item" data-action="merge">
                    <i class="mdi mdi-merge"></i>
                    <span>Merge</span>
                </button>
                <button class="mobile-bulk-action-item" data-action="archive">
                    <i class="mdi mdi-archive"></i>
                    <span>Archive</span>
                </button>
            </div>
        </div>

        <div class="mobile-bulk-section mobile-bulk-danger">
            <h4 class="mobile-bulk-section-title">Danger Zone</h4>
            <div class="mobile-bulk-action-grid">
                <button class="mobile-bulk-action-item danger" data-action="delete">
                    <i class="mdi mdi-delete"></i>
                    <span>Delete</span>
                </button>
                <button class="mobile-bulk-action-item danger" data-action="permanent-delete">
                    <i class="mdi mdi-delete-forever"></i>
                    <span>Permanent Delete</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Undo/Redo Controls -->
    <div class="mobile-bulk-history" id="mobile-bulk-history" style="display: none;">
        <button class="mobile-bulk-undo" id="mobile-bulk-undo" disabled>
            <i class="mdi mdi-undo"></i>
            <span>Undo</span>
        </button>
        <button class="mobile-bulk-redo" id="mobile-bulk-redo" disabled>
            <i class="mdi mdi-redo"></i>
            <span>Redo</span>
        </button>
    </div>
</div>

<!-- Bulk Action Modals -->

<!-- Assign Modal -->
<div class="mobile-bulk-modal" id="mobile-bulk-assign-modal" style="display: none;">
    <div class="mobile-bulk-modal-content">
        <div class="mobile-bulk-modal-header">
            <h3>Assign Contacts</h3>
            <button class="mobile-bulk-modal-close" data-modal="assign">
                <i class="mdi mdi-close"></i>
            </button>
        </div>
        <div class="mobile-bulk-modal-body">
            <div class="mobile-bulk-selected-info">
                <span id="assign-selected-count"></span> contacts selected
            </div>
            <div class="mobile-bulk-form-group">
                <label>Assign To:</label>
                <select id="mobile-bulk-assign-user" class="mobile-bulk-select">
                    <option value="">Select user...</option>
                    <!-- Users populated by JavaScript -->
                </select>
            </div>
            <div class="mobile-bulk-form-group">
                <label>
                    <input type="checkbox" id="mobile-bulk-assign-notify">
                    Send notification to assignee
                </label>
            </div>
            <div class="mobile-bulk-form-group">
                <label>Note (optional):</label>
                <textarea id="mobile-bulk-assign-note" placeholder="Add a note for the assignee..."></textarea>
            </div>
        </div>
        <div class="mobile-bulk-modal-actions">
            <button class="mobile-bulk-cancel" data-modal="assign">Cancel</button>
            <button class="mobile-bulk-confirm" id="mobile-bulk-assign-confirm">Assign</button>
        </div>
    </div>
</div>

<!-- Status Modal -->
<div class="mobile-bulk-modal" id="mobile-bulk-status-modal" style="display: none;">
    <div class="mobile-bulk-modal-content">
        <div class="mobile-bulk-modal-header">
            <h3>Update Status</h3>
            <button class="mobile-bulk-modal-close" data-modal="status">
                <i class="mdi mdi-close"></i>
            </button>
        </div>
        <div class="mobile-bulk-modal-body">
            <div class="mobile-bulk-selected-info">
                <span id="status-selected-count"></span> contacts selected
            </div>
            <div class="mobile-bulk-form-group">
                <label>New Status:</label>
                <select id="mobile-bulk-status-value" class="mobile-bulk-select">
                    <option value="new">New</option>
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            <div class="mobile-bulk-form-group" id="mobile-bulk-reason-group" style="display: none;">
                <label>Reason:</label>
                <select id="mobile-bulk-reason-value" class="mobile-bulk-select">
                    <!-- Reasons populated by JavaScript based on status -->
                </select>
            </div>
            <div class="mobile-bulk-form-group">
                <label>Note (optional):</label>
                <textarea id="mobile-bulk-status-note" placeholder="Add a note about this status change..."></textarea>
            </div>
        </div>
        <div class="mobile-bulk-modal-actions">
            <button class="mobile-bulk-cancel" data-modal="status">Cancel</button>
            <button class="mobile-bulk-confirm" id="mobile-bulk-status-confirm">Update Status</button>
        </div>
    </div>
</div>

<!-- Tag Modal -->
<div class="mobile-bulk-modal" id="mobile-bulk-tag-modal" style="display: none;">
    <div class="mobile-bulk-modal-content">
        <div class="mobile-bulk-modal-header">
            <h3>Manage Tags</h3>
            <button class="mobile-bulk-modal-close" data-modal="tag">
                <i class="mdi mdi-close"></i>
            </button>
        </div>
        <div class="mobile-bulk-modal-body">
            <div class="mobile-bulk-selected-info">
                <span id="tag-selected-count"></span> contacts selected
            </div>
            <div class="mobile-bulk-form-group">
                <label>Add Tags:</label>
                <div class="mobile-bulk-tag-input">
                    <input type="text" id="mobile-bulk-tag-search" placeholder="Search or create tags...">
                    <div class="mobile-bulk-tag-suggestions" id="mobile-bulk-tag-suggestions"></div>
                </div>
                <div class="mobile-bulk-selected-tags" id="mobile-bulk-selected-tags"></div>
            </div>
            <div class="mobile-bulk-form-group">
                <label>Remove Tags:</label>
                <div class="mobile-bulk-existing-tags" id="mobile-bulk-existing-tags">
                    <!-- Existing tags populated by JavaScript -->
                </div>
            </div>
        </div>
        <div class="mobile-bulk-modal-actions">
            <button class="mobile-bulk-cancel" data-modal="tag">Cancel</button>
            <button class="mobile-bulk-confirm" id="mobile-bulk-tag-confirm">Update Tags</button>
        </div>
    </div>
</div>

<!-- Progress Modal -->
<div class="mobile-bulk-progress-modal" id="mobile-bulk-progress-modal" style="display: none;">
    <div class="mobile-bulk-progress-content">
        <div class="mobile-bulk-progress-header">
            <h3 id="mobile-bulk-progress-title">Processing...</h3>
        </div>
        <div class="mobile-bulk-progress-body">
            <div class="mobile-bulk-progress-bar">
                <div class="mobile-bulk-progress-fill" id="mobile-bulk-progress-fill"></div>
            </div>
            <div class="mobile-bulk-progress-text">
                <span id="mobile-bulk-progress-current">0</span> of 
                <span id="mobile-bulk-progress-total">0</span> contacts processed
            </div>
            <div class="mobile-bulk-progress-details" id="mobile-bulk-progress-details">
                Preparing bulk operation...
            </div>
        </div>
        <div class="mobile-bulk-progress-actions">
            <button class="mobile-bulk-cancel-operation" id="mobile-bulk-cancel-operation">
                Cancel
            </button>
        </div>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="mobile-bulk-confirm-modal" id="mobile-bulk-confirm-modal" style="display: none;">
    <div class="mobile-bulk-confirm-content">
        <div class="mobile-bulk-confirm-header">
            <h3 id="mobile-bulk-confirm-title">Confirm Action</h3>
        </div>
        <div class="mobile-bulk-confirm-body">
            <div class="mobile-bulk-confirm-icon" id="mobile-bulk-confirm-icon">
                <i class="mdi mdi-alert"></i>
            </div>
            <div class="mobile-bulk-confirm-message" id="mobile-bulk-confirm-message">
                Are you sure you want to perform this action?
            </div>
            <div class="mobile-bulk-confirm-details" id="mobile-bulk-confirm-details">
                This action cannot be undone.
            </div>
        </div>
        <div class="mobile-bulk-confirm-actions">
            <button class="mobile-bulk-cancel" id="mobile-bulk-confirm-cancel">Cancel</button>
            <button class="mobile-bulk-confirm-action" id="mobile-bulk-confirm-action">Confirm</button>
        </div>
    </div>
</div>

<!-- Drag and Drop Overlay -->
<div class="mobile-bulk-drag-overlay" id="mobile-bulk-drag-overlay" style="display: none;">
    <div class="mobile-bulk-drag-content">
        <div class="mobile-bulk-drag-icon">
            <i class="mdi mdi-drag"></i>
        </div>
        <div class="mobile-bulk-drag-text">
            Drag to organize contacts
        </div>
    </div>
</div>

<!-- Success Toast -->
<div class="mobile-bulk-toast" id="mobile-bulk-toast" style="display: none;">
    <div class="mobile-bulk-toast-content">
        <div class="mobile-bulk-toast-icon">
            <i class="mdi mdi-check-circle"></i>
        </div>
        <div class="mobile-bulk-toast-message" id="mobile-bulk-toast-message">
            Action completed successfully
        </div>
        <button class="mobile-bulk-toast-close" id="mobile-bulk-toast-close">
            <i class="mdi mdi-close"></i>
        </button>
    </div>
</div>

<script>
// Initialize mobile bulk actions when component loads
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.MobileBulkActions === 'undefined') {
        console.log('MobileBulkActions not loaded yet, waiting...');
        return;
    }
    
    // Initialize the bulk actions system
    window.mobileBulkActions = new MobileBulkActions();
    
    console.log('Mobile bulk actions initialized');
});
</script> 
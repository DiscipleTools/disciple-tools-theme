/**
 * Mobile Gesture Manager
 * Advanced gesture recognition and interaction handling for mobile devices
 * 
 * Features:
 * - Swipe actions (left, right, up, down)
 * - Pull-to-refresh functionality
 * - Pinch-to-zoom for contact details
 * - Long-press context menus
 * - Multi-touch gesture support
 * - Haptic feedback integration
 * - Gesture velocity and momentum tracking
 */

class MobileGestureManager {
  constructor() {
    this.activeGestures = new Map();
    this.gestureCallbacks = new Map();
    this.touchStartTime = 0;
    this.touchStartPosition = { x: 0, y: 0 };
    this.touchCurrentPosition = { x: 0, y: 0 };
    this.touchHistory = [];
    this.isTracking = false;
    this.longPressTimer = null;
    this.doubleTapTimer = null;
    this.lastTapTime = 0;
    
    // Configuration
    this.config = {
      // Swipe thresholds
      swipeThreshold: 100,        // Minimum distance for swipe
      swipeVelocityThreshold: 0.3, // Minimum velocity for swipe
      swipeTimeThreshold: 300,    // Maximum time for swipe (ms)
      
      // Long press
      longPressDelay: 500,        // Long press detection delay (ms)
      longPressMoveThreshold: 10, // Maximum movement during long press
      
      // Double tap
      doubleTapDelay: 300,        // Maximum time between taps
      doubleTapThreshold: 30,     // Maximum distance between taps
      
      // Pull to refresh
      pullThreshold: 80,          // Minimum pull distance
      pullReleaseThreshold: 100,  // Release threshold
      pullResistance: 0.5,        // Pull resistance factor
      
      // Pinch to zoom
      pinchThreshold: 10,         // Minimum pinch distance
      zoomMin: 0.5,              // Minimum zoom level
      zoomMax: 3.0,              // Maximum zoom level
      
      // Touch tracking
      maxTouchHistory: 10,        // Maximum touch history points
      velocityTimeWindow: 100,    // Velocity calculation window (ms)
      
      // Haptic feedback
      enableHaptics: true,        // Enable haptic feedback
      swipeHapticDuration: 50,    // Swipe haptic duration
      longPressHapticDuration: 30, // Long press haptic duration
    };
    
    // State management
    this.state = {
      isPulling: false,
      pullDistance: 0,
      isZooming: false,
      zoomLevel: 1,
      zoomCenter: { x: 0, y: 0 },
      lastPinchDistance: 0,
      swipeDirection: null,
      contextMenu: null
    };
    
    this.init();
  }
  
  /**
   * Initialize gesture manager
   */
  init() {
    console.log('Gesture Manager: Initializing...');
    
    this.setupEventListeners();
    this.setupSwipeActions();
    this.setupPullToRefresh();
    this.initializeUI();
    
    console.log('Gesture Manager: Initialized successfully');
  }
  
  /**
   * Setup main event listeners
   */
  setupEventListeners() {
    // Touch events for mobile
    document.addEventListener('touchstart', this.handleTouchStart.bind(this), { passive: false });
    document.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: false });
    document.addEventListener('touchend', this.handleTouchEnd.bind(this), { passive: false });
    document.addEventListener('touchcancel', this.handleTouchCancel.bind(this), { passive: false });
    
    // Mouse events for desktop testing
    document.addEventListener('mousedown', this.handleMouseDown.bind(this));
    document.addEventListener('mousemove', this.handleMouseMove.bind(this));
    document.addEventListener('mouseup', this.handleMouseUp.bind(this));
    
    // Prevent default touch behaviors on gesture areas
    document.addEventListener('touchstart', (e) => {
      if (e.target.closest('.gesture-area')) {
        e.preventDefault();
      }
    }, { passive: false });
  }
  
  /**
   * Handle touch start
   */
  handleTouchStart(e) {
    const touch = e.touches[0];
    const now = Date.now();
    
    this.isTracking = true;
    this.touchStartTime = now;
    this.touchStartPosition = { x: touch.clientX, y: touch.clientY };
    this.touchCurrentPosition = { x: touch.clientX, y: touch.clientY };
    this.touchHistory = [{ x: touch.clientX, y: touch.clientY, time: now }];
    
    // Handle multi-touch for pinch-to-zoom
    if (e.touches.length === 2) {
      this.handlePinchStart(e);
    }
    
    // Start long press detection
    this.startLongPressDetection(e);
    
    // Handle double tap detection
    this.handleTapDetection(e);
    
    // Trigger touch start callbacks
    this.triggerCallbacks('touchstart', { 
      element: e.target, 
      position: this.touchStartPosition,
      timestamp: now
    });
  }
  
  /**
   * Handle touch move
   */
  handleTouchMove(e) {
    if (!this.isTracking) return;
    
    const touch = e.touches[0];
    const now = Date.now();
    
    this.touchCurrentPosition = { x: touch.clientX, y: touch.clientY };
    
    // Update touch history
    this.touchHistory.push({ x: touch.clientX, y: touch.clientY, time: now });
    if (this.touchHistory.length > this.config.maxTouchHistory) {
      this.touchHistory.shift();
    }
    
    // Handle multi-touch for pinch-to-zoom
    if (e.touches.length === 2) {
      this.handlePinchMove(e);
      return;
    }
    
    // Calculate movement
    const deltaX = this.touchCurrentPosition.x - this.touchStartPosition.x;
    const deltaY = this.touchCurrentPosition.y - this.touchStartPosition.y;
    const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
    
    // Cancel long press if moved too far
    if (distance > this.config.longPressMoveThreshold) {
      this.cancelLongPress();
    }
    
    // Handle pull-to-refresh
    if (this.shouldHandlePullToRefresh(e)) {
      this.handlePullToRefresh(deltaY);
    }
    
    // Handle swipe preview
    this.handleSwipePreview(deltaX, deltaY);
    
    // Trigger move callbacks
    this.triggerCallbacks('touchmove', {
      element: e.target,
      position: this.touchCurrentPosition,
      delta: { x: deltaX, y: deltaY },
      distance: distance,
      timestamp: now
    });
  }
  
  /**
   * Handle touch end
   */
  handleTouchEnd(e) {
    if (!this.isTracking) return;
    
    const now = Date.now();
    const deltaTime = now - this.touchStartTime;
    const deltaX = this.touchCurrentPosition.x - this.touchStartPosition.x;
    const deltaY = this.touchCurrentPosition.y - this.touchStartPosition.y;
    const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
    
    // Cancel long press
    this.cancelLongPress();
    
    // Handle swipe detection
    if (this.isSwipeGesture(distance, deltaTime)) {
      this.handleSwipeGesture(deltaX, deltaY, deltaTime);
    }
    
    // Handle pull-to-refresh release
    if (this.state.isPulling) {
      this.handlePullRelease();
    }
    
    // Handle pinch end
    if (this.state.isZooming) {
      this.handlePinchEnd();
    }
    
    // Trigger touch end callbacks
    this.triggerCallbacks('touchend', {
      element: e.target,
      position: this.touchCurrentPosition,
      delta: { x: deltaX, y: deltaY },
      distance: distance,
      duration: deltaTime,
      timestamp: now
    });
    
    this.resetTrackingState();
  }
  
  /**
   * Handle touch cancel
   */
  handleTouchCancel(e) {
    this.cancelLongPress();
    this.resetTrackingState();
    
    this.triggerCallbacks('touchcancel', {
      element: e.target,
      timestamp: Date.now()
    });
  }
  
  /**
   * Mouse event handlers for desktop testing
   */
  handleMouseDown(e) {
    // Convert mouse event to touch-like event
    const touchEvent = {
      touches: [{ clientX: e.clientX, clientY: e.clientY }],
      target: e.target,
      preventDefault: () => e.preventDefault()
    };
    this.handleTouchStart(touchEvent);
  }
  
  handleMouseMove(e) {
    if (!this.isTracking) return;
    
    const touchEvent = {
      touches: [{ clientX: e.clientX, clientY: e.clientY }],
      target: e.target,
      preventDefault: () => e.preventDefault()
    };
    this.handleTouchMove(touchEvent);
  }
  
  handleMouseUp(e) {
    if (!this.isTracking) return;
    
    const touchEvent = {
      target: e.target,
      preventDefault: () => e.preventDefault()
    };
    this.handleTouchEnd(touchEvent);
  }
  
  /**
   * Long press detection
   */
  startLongPressDetection(e) {
    this.longPressTimer = setTimeout(() => {
      if (this.isTracking) {
        this.handleLongPressGesture(e);
      }
    }, this.config.longPressDelay);
  }
  
  cancelLongPress() {
    if (this.longPressTimer) {
      clearTimeout(this.longPressTimer);
      this.longPressTimer = null;
    }
  }
  
  handleLongPressGesture(e) {
    console.log('Gesture: Long press detected');
    
    // Haptic feedback
    this.hapticFeedback(this.config.longPressHapticDuration);
    
    // Show context menu if applicable
    if (e.target.closest('.mobile-contact-card')) {
      this.showContextMenu(e);
    }
    
    this.triggerCallbacks('longpress', {
      element: e.target,
      position: this.touchStartPosition
    });
  }
  
  /**
   * Double tap detection
   */
  handleTapDetection(e) {
    const now = Date.now();
    const timeSinceLastTap = now - this.lastTapTime;
    
    if (timeSinceLastTap < this.config.doubleTapDelay) {
      // Double tap detected
      this.handleDoubleTapGesture(e);
      this.lastTapTime = 0; // Reset to prevent triple tap
    } else {
      // Single tap - start timer
      this.doubleTapTimer = setTimeout(() => {
        this.handleSingleTapGesture(e);
      }, this.config.doubleTapDelay);
      this.lastTapTime = now;
    }
  }
  
  handleSingleTapGesture(e) {
    this.triggerCallbacks('singletap', {
      element: e.target,
      position: this.touchStartPosition
    });
  }
  
  handleDoubleTapGesture(e) {
    if (this.doubleTapTimer) {
      clearTimeout(this.doubleTapTimer);
      this.doubleTapTimer = null;
    }
    
    console.log('Gesture: Double tap detected');
    
    // Handle zoom toggle for contact details
    if (e.target.closest('.mobile-contact-details')) {
      this.toggleZoom(e.target);
    }
    
    this.triggerCallbacks('doubletap', {
      element: e.target,
      position: this.touchStartPosition
    });
  }
  
  /**
   * Swipe gesture detection and handling
   */
  isSwipeGesture(distance, duration) {
    const velocity = distance / duration;
    return distance > this.config.swipeThreshold &&
           duration < this.config.swipeTimeThreshold &&
           velocity > this.config.swipeVelocityThreshold;
  }
  
  handleSwipeGesture(deltaX, deltaY, duration) {
    const direction = this.getSwipeDirection(deltaX, deltaY);
    const velocity = Math.sqrt(deltaX * deltaX + deltaY * deltaY) / duration;
    
    console.log(`Gesture: Swipe ${direction} detected (velocity: ${velocity.toFixed(2)})`);
    
    // Haptic feedback
    this.hapticFeedback(this.config.swipeHapticDuration);
    
    // Handle swipe actions
    this.executeSwipeAction(direction, velocity);
    
    this.triggerCallbacks('swipe', {
      direction: direction,
      velocity: velocity,
      delta: { x: deltaX, y: deltaY },
      duration: duration
    });
  }
  
  getSwipeDirection(deltaX, deltaY) {
    const absX = Math.abs(deltaX);
    const absY = Math.abs(deltaY);
    
    if (absX > absY) {
      return deltaX > 0 ? 'right' : 'left';
    } else {
      return deltaY > 0 ? 'down' : 'up';
    }
  }
  
  handleSwipePreview(deltaX, deltaY) {
    const direction = this.getSwipeDirection(deltaX, deltaY);
    const distance = Math.sqrt(deltaX * deltaX + deltaY * deltaY);
    
    if (distance > 20) { // Minimum distance for preview
      this.showSwipePreview(direction, distance);
    }
  }
  
  showSwipePreview(direction, distance) {
    // Implementation for showing swipe action preview
    // This could show action icons or animate the element
    console.log(`Gesture: Swipe preview ${direction} (${distance}px)`);
  }
  
  executeSwipeAction(direction, velocity) {
    // Find the target element
    const targetElement = document.elementFromPoint(
      this.touchStartPosition.x,
      this.touchStartPosition.y
    );
    
    const contactCard = targetElement?.closest('.mobile-contact-card');
    if (!contactCard) return;
    
    const contactId = contactCard.dataset.contactId;
    
    switch (direction) {
      case 'left':
        // Destructive actions (archive, delete)
        this.showSwipeActionMenu(contactCard, 'left', ['archive', 'delete']);
        break;
        
      case 'right':
        // Quick actions (edit, message, call)
        this.showSwipeActionMenu(contactCard, 'right', ['edit', 'message', 'call']);
        break;
        
      case 'up':
        // Mark as complete or assign
        this.executeQuickAction(contactId, 'complete');
        break;
        
      case 'down':
        // Add to favorites or follow-up
        this.executeQuickAction(contactId, 'favorite');
        break;
    }
  }
  
  /**
   * Pull-to-refresh functionality
   */
  shouldHandlePullToRefresh(e) {
    // Only handle pull-to-refresh at the top of scrollable areas
    const scrollableElement = e.target.closest('.mobile-contact-list, .mobile-content-box');
    return scrollableElement && scrollableElement.scrollTop === 0;
  }
  
  handlePullToRefresh(deltaY) {
    if (deltaY <= 0) return; // Only handle downward pulls
    
    this.state.isPulling = true;
    this.state.pullDistance = deltaY * this.config.pullResistance;
    
    // Update pull indicator
    this.updatePullIndicator(this.state.pullDistance);
    
    // Trigger pull callbacks
    this.triggerCallbacks('pull', {
      distance: this.state.pullDistance,
      threshold: this.config.pullThreshold
    });
  }
  
  handlePullRelease() {
    if (this.state.pullDistance > this.config.pullReleaseThreshold) {
      console.log('Gesture: Pull-to-refresh triggered');
      this.executePullToRefresh();
    }
    
    this.resetPullState();
  }
  
  executePullToRefresh() {
    // Show loading indicator
    this.showPullLoading();
    
    // Trigger refresh
    this.triggerCallbacks('refresh', {});
    
    // If no callback handled it, refresh the contact list
    if (window.mobileAPI && window.mobileAPI.refreshContacts) {
      window.mobileAPI.refreshContacts().then(() => {
        this.hidePullLoading();
      }).catch(() => {
        this.hidePullLoading();
      });
    } else {
      // Default: reload the page
      setTimeout(() => {
        window.location.reload();
      }, 1000);
    }
  }
  
  updatePullIndicator(distance) {
    let indicator = document.getElementById('pull-to-refresh-indicator');
    
    if (!indicator) {
      indicator = this.createPullIndicator();
    }
    
    const progress = Math.min(distance / this.config.pullThreshold, 1);
    indicator.style.transform = `translateY(${distance}px)`;
    indicator.style.opacity = progress;
    
    const arrow = indicator.querySelector('.pull-arrow');
    if (arrow) {
      arrow.style.transform = progress >= 1 ? 'rotate(180deg)' : 'rotate(0deg)';
    }
  }
  
  createPullIndicator() {
    const indicator = document.createElement('div');
    indicator.id = 'pull-to-refresh-indicator';
    indicator.className = 'pull-to-refresh-indicator';
    indicator.innerHTML = `
      <div class="pull-content">
        <div class="pull-arrow">↓</div>
        <div class="pull-text">Pull to refresh</div>
      </div>
    `;
    
    document.body.appendChild(indicator);
    return indicator;
  }
  
  showPullLoading() {
    const indicator = document.getElementById('pull-to-refresh-indicator');
    if (indicator) {
      indicator.innerHTML = `
        <div class="pull-content">
          <div class="pull-spinner">⟳</div>
          <div class="pull-text">Refreshing...</div>
        </div>
      `;
    }
  }
  
  hidePullLoading() {
    const indicator = document.getElementById('pull-to-refresh-indicator');
    if (indicator) {
      indicator.style.opacity = '0';
      setTimeout(() => {
        indicator.remove();
      }, 300);
    }
  }
  
  resetPullState() {
    this.state.isPulling = false;
    this.state.pullDistance = 0;
  }
  
  /**
   * Pinch-to-zoom functionality
   */
  handlePinchStart(e) {
    const touch1 = e.touches[0];
    const touch2 = e.touches[1];
    
    this.state.isZooming = true;
    this.state.lastPinchDistance = this.getTouchDistance(touch1, touch2);
    this.state.zoomCenter = this.getTouchCenter(touch1, touch2);
  }
  
  handlePinchMove(e) {
    if (!this.state.isZooming) return;
    
    const touch1 = e.touches[0];
    const touch2 = e.touches[1];
    const currentDistance = this.getTouchDistance(touch1, touch2);
    
    if (this.state.lastPinchDistance > 0) {
      const scale = currentDistance / this.state.lastPinchDistance;
      const newZoomLevel = Math.max(
        this.config.zoomMin,
        Math.min(this.config.zoomMax, this.state.zoomLevel * scale)
      );
      
      this.applyZoom(newZoomLevel, this.state.zoomCenter);
      this.state.zoomLevel = newZoomLevel;
    }
    
    this.state.lastPinchDistance = currentDistance;
  }
  
  handlePinchEnd() {
    this.state.isZooming = false;
    this.state.lastPinchDistance = 0;
  }
  
  getTouchDistance(touch1, touch2) {
    const dx = touch2.clientX - touch1.clientX;
    const dy = touch2.clientY - touch1.clientY;
    return Math.sqrt(dx * dx + dy * dy);
  }
  
  getTouchCenter(touch1, touch2) {
    return {
      x: (touch1.clientX + touch2.clientX) / 2,
      y: (touch1.clientY + touch2.clientY) / 2
    };
  }
  
  applyZoom(zoomLevel, center) {
    const zoomableElement = document.querySelector('.mobile-contact-details');
    if (zoomableElement) {
      zoomableElement.style.transform = `scale(${zoomLevel})`;
      zoomableElement.style.transformOrigin = `${center.x}px ${center.y}px`;
    }
  }
  
  toggleZoom(element) {
    const currentZoom = this.state.zoomLevel;
    const newZoom = currentZoom > 1 ? 1 : 2;
    
    this.applyZoom(newZoom, { x: window.innerWidth / 2, y: window.innerHeight / 2 });
    this.state.zoomLevel = newZoom;
  }
  
  /**
   * Context menu functionality
   */
  showContextMenu(e) {
    const contactCard = e.target.closest('.mobile-contact-card');
    if (!contactCard) return;
    
    // Remove existing context menu
    this.hideContextMenu();
    
    const menu = document.createElement('div');
    menu.className = 'mobile-context-menu';
    menu.innerHTML = `
      <div class="context-menu-item" data-action="edit">
        <i class="mdi mdi-pencil"></i> Edit
      </div>
      <div class="context-menu-item" data-action="message">
        <i class="mdi mdi-message"></i> Message
      </div>
      <div class="context-menu-item" data-action="call">
        <i class="mdi mdi-phone"></i> Call
      </div>
      <div class="context-menu-item" data-action="assign">
        <i class="mdi mdi-account-plus"></i> Assign
      </div>
      <div class="context-menu-item danger" data-action="delete">
        <i class="mdi mdi-delete"></i> Delete
      </div>
    `;
    
    // Position menu
    menu.style.left = `${this.touchStartPosition.x}px`;
    menu.style.top = `${this.touchStartPosition.y}px`;
    
    document.body.appendChild(menu);
    this.state.contextMenu = menu;
    
    // Handle menu item clicks
    menu.addEventListener('click', (e) => {
      const action = e.target.closest('.context-menu-item')?.dataset.action;
      if (action) {
        this.executeContextAction(contactCard, action);
        this.hideContextMenu();
      }
    });
    
    // Hide menu on outside click
    setTimeout(() => {
      document.addEventListener('click', this.hideContextMenu.bind(this), { once: true });
    }, 100);
  }
  
  hideContextMenu() {
    if (this.state.contextMenu) {
      this.state.contextMenu.remove();
      this.state.contextMenu = null;
    }
  }
  
  executeContextAction(contactCard, action) {
    const contactId = contactCard.dataset.contactId;
    console.log(`Gesture: Context action ${action} for contact ${contactId}`);
    
    // Trigger action callback
    this.triggerCallbacks('contextaction', {
      contactId: contactId,
      action: action,
      element: contactCard
    });
  }
  
  /**
   * Utility functions
   */
  
  hapticFeedback(duration = 50) {
    if (this.config.enableHaptics && navigator.vibrate) {
      navigator.vibrate(duration);
    }
  }
  
  resetTrackingState() {
    this.isTracking = false;
    this.touchHistory = [];
    this.state.swipeDirection = null;
  }
  
  /**
   * Callback management
   */
  
  on(eventType, callback) {
    if (!this.gestureCallbacks.has(eventType)) {
      this.gestureCallbacks.set(eventType, []);
    }
    this.gestureCallbacks.get(eventType).push(callback);
  }
  
  off(eventType, callback) {
    if (this.gestureCallbacks.has(eventType)) {
      const callbacks = this.gestureCallbacks.get(eventType);
      const index = callbacks.indexOf(callback);
      if (index > -1) {
        callbacks.splice(index, 1);
      }
    }
  }
  
  triggerCallbacks(eventType, data) {
    if (this.gestureCallbacks.has(eventType)) {
      this.gestureCallbacks.get(eventType).forEach(callback => {
        try {
          callback(data);
        } catch (error) {
          console.error(`Gesture: Callback error for ${eventType}:`, error);
        }
      });
    }
  }
  
  /**
   * Setup swipe actions
   */
  setupSwipeActions() {
    // Add swipe action styles
    this.injectSwipeStyles();
  }
  
  injectSwipeStyles() {
    const style = document.createElement('style');
    style.textContent = `
      .mobile-contact-card {
        position: relative;
        overflow: hidden;
      }
      
      .mobile-contact-card.swiping {
        transition: transform 0.2s ease-out;
      }
      
      .swipe-actions {
        position: absolute;
        top: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        z-index: 1;
      }
      
      .swipe-actions.left {
        right: 0;
        background: linear-gradient(90deg, #ff4444, #cc0000);
      }
      
      .swipe-actions.right {
        left: 0;
        background: linear-gradient(90deg, #44ff44, #00cc00);
      }
      
      .swipe-action {
        color: white;
        padding: 0 15px;
        font-size: 20px;
        cursor: pointer;
      }
      
      .mobile-context-menu {
        position: fixed;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        z-index: 9999;
        overflow: hidden;
        animation: contextMenuAppear 0.2s ease-out;
      }
      
      .context-menu-item {
        padding: 12px 16px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: background-color 0.15s;
      }
      
      .context-menu-item:hover {
        background-color: #f5f5f5;
      }
      
      .context-menu-item.danger {
        color: #ff4444;
      }
      
      .pull-to-refresh-indicator {
        position: fixed;
        top: -60px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 9999;
        transition: opacity 0.3s;
      }
      
      .pull-content {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 10px;
        background: rgba(255,255,255,0.9);
        border-radius: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      }
      
      .pull-arrow, .pull-spinner {
        font-size: 20px;
        transition: transform 0.3s;
      }
      
      .pull-spinner {
        animation: spin 1s linear infinite;
      }
      
      @keyframes contextMenuAppear {
        from { opacity: 0; transform: scale(0.8); }
        to { opacity: 1; transform: scale(1); }
      }
      
      @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
      }
    `;
    
    document.head.appendChild(style);
  }
  
  initializeUI() {
    // Mark compatible elements as gesture areas
    document.querySelectorAll('.mobile-contact-card, .mobile-contact-list').forEach(element => {
      element.classList.add('gesture-area');
    });
  }
  
  /**
   * Public API methods
   */
  
  getState() {
    return { ...this.state };
  }
  
  getConfig() {
    return { ...this.config };
  }
  
  updateConfig(newConfig) {
    this.config = { ...this.config, ...newConfig };
  }
  
  enableHaptics(enable = true) {
    this.config.enableHaptics = enable;
  }
  
  destroy() {
    // Clean up event listeners and timers
    this.gestureCallbacks.clear();
    this.cancelLongPress();
    if (this.doubleTapTimer) {
      clearTimeout(this.doubleTapTimer);
    }
    this.hideContextMenu();
  }
}

// Initialize gesture manager when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.mobileGestureManager = new MobileGestureManager();
  });
} else {
  window.mobileGestureManager = new MobileGestureManager();
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
  module.exports = MobileGestureManager;
} 
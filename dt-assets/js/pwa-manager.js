/**
 * PWA Manager for Disciple Tools Mobile
 * Coordinates Progressive Web App functionality including:
 * - Service Worker registration and updates
 * - App installation prompts
 * - Push notification management
 * - Offline/online status handling
 * - Background sync coordination
 */

class PWAManager {
  constructor() {
    this.serviceWorker = null;
    this.deferredPrompt = null;
    this.isOnline = navigator.onLine;
    this.installable = false;
    this.pushSubscription = null;
    
    // Configuration
    this.config = {
      serviceWorkerPath: '/dt-assets/js/service-worker.js',
      manifestPath: '/manifest.json',
      vapidPublicKey: null, // Set from server
      installPromptDelay: 30000, // 30 seconds
      updateCheckInterval: 300000, // 5 minutes
      maxOfflineActions: 100,
      cachePruneInterval: 86400000 // 24 hours
    };
    
    // Initialize PWA features
    this.init();
  }
  
  /**
   * Initialize PWA Manager
   */
  async init() {
    console.log('PWA: Initializing PWA Manager...');
    
    try {
      // Check for PWA support
      if (!this.isPWASupported()) {
        console.warn('PWA: PWA features not fully supported in this browser');
        return;
      }
      
      // Register service worker
      await this.registerServiceWorker();
      
      // Set up installation handling
      this.setupInstallation();
      
      // Set up push notifications
      this.setupPushNotifications();
      
      // Set up offline/online detection
      this.setupOnlineDetection();
      
      // Set up periodic tasks
      this.setupPeriodicTasks();
      
      // Initialize UI elements
      this.initializeUI();
      
      console.log('PWA: PWA Manager initialized successfully');
      
      // Notify that PWA is ready
      this.dispatchEvent('pwa:ready');
      
    } catch (error) {
      console.error('PWA: Failed to initialize PWA Manager:', error);
    }
  }
  
  /**
   * Check if PWA features are supported
   */
  isPWASupported() {
    return 'serviceWorker' in navigator && 
           'caches' in window && 
           'indexedDB' in window &&
           'Notification' in window;
  }
  
  /**
   * Register Service Worker
   */
  async registerServiceWorker() {
    if (!('serviceWorker' in navigator)) {
      throw new Error('Service Worker not supported');
    }
    
    try {
      const registration = await navigator.serviceWorker.register(
        this.config.serviceWorkerPath,
        { scope: '/' }
      );
      
      this.serviceWorker = registration;
      
      console.log('PWA: Service Worker registered:', registration.scope);
      
      // Handle service worker updates
      registration.addEventListener('updatefound', () => {
        this.handleServiceWorkerUpdate(registration);
      });
      
      // Handle active service worker
      if (registration.active) {
        this.setupServiceWorkerMessaging(registration.active);
      }
      
      return registration;
      
    } catch (error) {
      console.error('PWA: Service Worker registration failed:', error);
      throw error;
    }
  }
  
  /**
   * Handle Service Worker Updates
   */
  handleServiceWorkerUpdate(registration) {
    const newWorker = registration.installing;
    
    console.log('PWA: New Service Worker available');
    
    newWorker.addEventListener('statechange', () => {
      if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
        console.log('PWA: New Service Worker installed, prompting for update');
        this.showUpdatePrompt();
      }
    });
  }
  
  /**
   * Show update prompt to user
   */
  showUpdatePrompt() {
    const updateBanner = document.createElement('div');
    updateBanner.className = 'pwa-update-banner';
    updateBanner.innerHTML = `
      <div class="pwa-update-content">
        <span class="pwa-update-message">A new version is available!</span>
        <button class="pwa-update-button" id="pwa-update-btn">
          Update Now
        </button>
        <button class="pwa-update-dismiss" id="pwa-update-dismiss">
          Later
        </button>
      </div>
    `;
    
    document.body.appendChild(updateBanner);
    
    // Handle update button
    document.getElementById('pwa-update-btn').addEventListener('click', () => {
      this.applyUpdate();
      updateBanner.remove();
    });
    
    // Handle dismiss button
    document.getElementById('pwa-update-dismiss').addEventListener('click', () => {
      updateBanner.remove();
    });
    
    // Auto-dismiss after 10 seconds
    setTimeout(() => {
      if (document.body.contains(updateBanner)) {
        updateBanner.remove();
      }
    }, 10000);
  }
  
  /**
   * Apply service worker update
   */
  async applyUpdate() {
    if (!this.serviceWorker) return;
    
    try {
      // Tell the new service worker to skip waiting
      const newWorker = this.serviceWorker.waiting;
      if (newWorker) {
        newWorker.postMessage({ action: 'skipWaiting' });
        
        // Reload the page when the new worker takes control
        navigator.serviceWorker.addEventListener('controllerchange', () => {
          window.location.reload();
        });
      }
    } catch (error) {
      console.error('PWA: Failed to apply update:', error);
    }
  }
  
  /**
   * Setup App Installation
   */
  setupInstallation() {
    // Listen for install prompt
    window.addEventListener('beforeinstallprompt', (e) => {
      console.log('PWA: Install prompt available');
      
      // Prevent default prompt
      e.preventDefault();
      
      // Store the event for later use
      this.deferredPrompt = e;
      this.installable = true;
      
      // Show install button
      this.showInstallButton();
      
      // Auto-prompt after delay (optional)
      setTimeout(() => {
        if (this.deferredPrompt && !this.isAppInstalled()) {
          this.showInstallPrompt();
        }
      }, this.config.installPromptDelay);
    });
    
    // Listen for app installed
    window.addEventListener('appinstalled', () => {
      console.log('PWA: App installed');
      this.installable = false;
      this.deferredPrompt = null;
      this.hideInstallButton();
      
      // Track installation
      this.trackEvent('pwa_installed');
    });
  }
  
  /**
   * Show install button in UI
   */
  showInstallButton() {
    const installButton = document.getElementById('pwa-install-button');
    if (installButton) {
      installButton.style.display = 'block';
      installButton.addEventListener('click', () => {
        this.promptInstall();
      });
    }
  }
  
  /**
   * Hide install button
   */
  hideInstallButton() {
    const installButton = document.getElementById('pwa-install-button');
    if (installButton) {
      installButton.style.display = 'none';
    }
  }
  
  /**
   * Show install prompt dialog
   */
  showInstallPrompt() {
    if (!this.deferredPrompt) return;
    
    const installModal = document.createElement('div');
    installModal.className = 'pwa-install-modal';
    installModal.innerHTML = `
      <div class="pwa-install-content">
        <div class="pwa-install-header">
          <img src="/dt-assets/images/pwa/icon-96x96.png" alt="DT Mobile" class="pwa-install-icon">
          <h3>Install DT Mobile</h3>
        </div>
        <div class="pwa-install-body">
          <p>Install Disciple Tools Mobile for quick access and offline functionality.</p>
          <ul class="pwa-install-features">
            <li>✓ Works offline</li>
            <li>✓ Fast loading</li>
            <li>✓ Push notifications</li>
            <li>✓ Home screen access</li>
          </ul>
        </div>
        <div class="pwa-install-actions">
          <button class="pwa-install-yes">Install</button>
          <button class="pwa-install-no">Not Now</button>
        </div>
      </div>
    `;
    
    document.body.appendChild(installModal);
    
    // Handle install
    installModal.querySelector('.pwa-install-yes').addEventListener('click', () => {
      this.promptInstall();
      installModal.remove();
    });
    
    // Handle dismiss
    installModal.querySelector('.pwa-install-no').addEventListener('click', () => {
      installModal.remove();
    });
  }
  
  /**
   * Prompt user to install app
   */
  async promptInstall() {
    if (!this.deferredPrompt) {
      console.log('PWA: No install prompt available');
      return;
    }
    
    try {
      // Show the install prompt
      this.deferredPrompt.prompt();
      
      // Wait for user choice
      const choiceResult = await this.deferredPrompt.userChoice;
      
      console.log('PWA: Install choice:', choiceResult.outcome);
      
      if (choiceResult.outcome === 'accepted') {
        console.log('PWA: User accepted installation');
        this.trackEvent('pwa_install_accepted');
      } else {
        console.log('PWA: User dismissed installation');
        this.trackEvent('pwa_install_dismissed');
      }
      
      // Clear the prompt
      this.deferredPrompt = null;
      this.installable = false;
      
    } catch (error) {
      console.error('PWA: Install prompt failed:', error);
    }
  }
  
  /**
   * Check if app is installed
   */
  isAppInstalled() {
    return window.matchMedia('(display-mode: standalone)').matches ||
           window.navigator.standalone === true;
  }
  
  /**
   * Setup Push Notifications
   */
  async setupPushNotifications() {
    if (!('Notification' in window) || !('PushManager' in window)) {
      console.warn('PWA: Push notifications not supported');
      return;
    }
    
    try {
      // Check current permission
      const permission = await Notification.requestPermission();
      
      if (permission === 'granted') {
        console.log('PWA: Notification permission granted');
        await this.subscribeToPush();
      } else {
        console.log('PWA: Notification permission denied');
      }
      
    } catch (error) {
      console.error('PWA: Push notification setup failed:', error);
    }
  }
  
  /**
   * Subscribe to push notifications
   */
  async subscribeToPush() {
    if (!this.serviceWorker || !this.config.vapidPublicKey) {
      console.log('PWA: Cannot subscribe to push - missing service worker or VAPID key');
      return;
    }
    
    try {
      const subscription = await this.serviceWorker.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: this.urlBase64ToUint8Array(this.config.vapidPublicKey)
      });
      
      this.pushSubscription = subscription;
      
      console.log('PWA: Push subscription created');
      
      // Send subscription to server
      await this.sendSubscriptionToServer(subscription);
      
    } catch (error) {
      console.error('PWA: Push subscription failed:', error);
    }
  }
  
  /**
   * Send push subscription to server
   */
  async sendSubscriptionToServer(subscription) {
    try {
      const response = await fetch('/wp-json/dt/v1/push-subscription', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': window.wpApiShare.nonce
        },
        body: JSON.stringify({
          subscription: subscription.toJSON()
        })
      });
      
      if (response.ok) {
        console.log('PWA: Push subscription registered with server');
      } else {
        console.error('PWA: Failed to register push subscription with server');
      }
      
    } catch (error) {
      console.error('PWA: Error sending subscription to server:', error);
    }
  }
  
  /**
   * Setup Online/Offline Detection
   */
  setupOnlineDetection() {
    // Listen for online/offline events
    window.addEventListener('online', () => {
      console.log('PWA: Connection restored');
      this.isOnline = true;
      this.handleOnlineStatus(true);
    });
    
    window.addEventListener('offline', () => {
      console.log('PWA: Connection lost');
      this.isOnline = false;
      this.handleOnlineStatus(false);
    });
    
    // Initial status check
    this.handleOnlineStatus(this.isOnline);
  }
  
  /**
   * Handle online/offline status changes
   */
  handleOnlineStatus(isOnline) {
    // Update UI
    document.body.classList.toggle('pwa-offline', !isOnline);
    document.body.classList.toggle('pwa-online', isOnline);
    
    // Show/hide offline indicator
    this.toggleOfflineIndicator(!isOnline);
    
    // Trigger background sync if back online
    if (isOnline && this.serviceWorker) {
      this.triggerBackgroundSync();
    }
    
    // Dispatch event
    this.dispatchEvent(isOnline ? 'pwa:online' : 'pwa:offline');
  }
  
  /**
   * Show/hide offline indicator
   */
  toggleOfflineIndicator(show) {
    let indicator = document.getElementById('pwa-offline-indicator');
    
    if (show && !indicator) {
      indicator = document.createElement('div');
      indicator.id = 'pwa-offline-indicator';
      indicator.className = 'pwa-offline-indicator';
      indicator.innerHTML = `
        <div class="pwa-offline-content">
          <span class="pwa-offline-icon">📱</span>
          <span class="pwa-offline-text">Working Offline</span>
        </div>
      `;
      document.body.appendChild(indicator);
    } else if (!show && indicator) {
      indicator.remove();
    }
  }
  
  /**
   * Trigger background sync
   */
  async triggerBackgroundSync() {
    if (!this.serviceWorker || !('sync' in window.ServiceWorkerRegistration.prototype)) {
      console.log('PWA: Background sync not supported');
      return;
    }
    
    try {
      await this.serviceWorker.sync.register('contact-updates');
      await this.serviceWorker.sync.register('contact-creation');
      await this.serviceWorker.sync.register('bulk-actions');
      
      console.log('PWA: Background sync triggered');
      
    } catch (error) {
      console.error('PWA: Background sync failed:', error);
    }
  }
  
  /**
   * Setup periodic tasks
   */
  setupPeriodicTasks() {
    // Check for service worker updates
    setInterval(() => {
      if (this.serviceWorker) {
        this.serviceWorker.update();
      }
    }, this.config.updateCheckInterval);
    
    // Prune caches
    setInterval(() => {
      this.pruneCaches();
    }, this.config.cachePruneInterval);
  }
  
  /**
   * Prune old caches
   */
  async pruneCaches() {
    try {
      const cacheNames = await caches.keys();
      const oldCaches = cacheNames.filter(name => 
        !name.includes('dt-mobile-v3.0.0')
      );
      
      await Promise.all(
        oldCaches.map(cacheName => caches.delete(cacheName))
      );
      
      console.log('PWA: Pruned old caches');
      
    } catch (error) {
      console.error('PWA: Cache pruning failed:', error);
    }
  }
  
  /**
   * Initialize PWA UI elements
   */
  initializeUI() {
    // Add PWA status to body
    document.body.classList.add('pwa-enabled');
    
    if (this.isAppInstalled()) {
      document.body.classList.add('pwa-installed');
    }
    
    // Create install button if not exists
    if (!document.getElementById('pwa-install-button')) {
      const installButton = document.createElement('button');
      installButton.id = 'pwa-install-button';
      installButton.className = 'pwa-install-button';
      installButton.innerHTML = '📱 Install App';
      installButton.style.display = 'none';
      
      // Add to header or appropriate location
      const header = document.querySelector('.mobile-header');
      if (header) {
        header.appendChild(installButton);
      }
    }
  }
  
  /**
   * Utility Functions
   */
  
  // Convert VAPID key
  urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
      .replace(/-/g, '+')
      .replace(/_/g, '/');
    
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);
    
    for (let i = 0; i < rawData.length; ++i) {
      outputArray[i] = rawData.charCodeAt(i);
    }
    
    return outputArray;
  }
  
  // Dispatch custom events
  dispatchEvent(eventName, detail = {}) {
    const event = new CustomEvent(eventName, { detail });
    window.dispatchEvent(event);
  }
  
  // Track events (integrate with analytics)
  trackEvent(eventName, properties = {}) {
    if (window.gtag) {
      window.gtag('event', eventName, properties);
    }
    
    console.log('PWA: Event tracked:', eventName, properties);
  }
  
  /**
   * Setup Service Worker Messaging
   */
  setupServiceWorkerMessaging(worker) {
    navigator.serviceWorker.addEventListener('message', (event) => {
      const { type, payload } = event.data;
      
      switch (type) {
        case 'CACHE_UPDATED':
          console.log('PWA: Cache updated for:', payload.url);
          break;
          
        case 'SYNC_COMPLETED':
          console.log('PWA: Background sync completed:', payload.tag);
          this.dispatchEvent('pwa:sync-completed', payload);
          break;
          
        case 'OFFLINE_ACTION_QUEUED':
          console.log('PWA: Offline action queued:', payload.action);
          this.showOfflineActionFeedback(payload.action);
          break;
          
        default:
          console.log('PWA: Unknown message from service worker:', event.data);
      }
    });
  }
  
  /**
   * Show feedback for offline actions
   */
  showOfflineActionFeedback(action) {
    const toast = document.createElement('div');
    toast.className = 'pwa-offline-toast';
    toast.innerHTML = `
      <div class="pwa-toast-content">
        <span class="pwa-toast-icon">📤</span>
        <span class="pwa-toast-message">Action saved for sync</span>
      </div>
    `;
    
    document.body.appendChild(toast);
    
    // Auto-remove after 3 seconds
    setTimeout(() => {
      if (document.body.contains(toast)) {
        toast.remove();
      }
    }, 3000);
  }
  
  /**
   * Get PWA status
   */
  getStatus() {
    return {
      isOnline: this.isOnline,
      isInstalled: this.isAppInstalled(),
      isInstallable: this.installable,
      hasServiceWorker: !!this.serviceWorker,
      hasPushSubscription: !!this.pushSubscription,
      cacheStatus: this.getCacheStatus()
    };
  }
  
  /**
   * Get cache status
   */
  async getCacheStatus() {
    try {
      const cacheNames = await caches.keys();
      return {
        caches: cacheNames,
        version: 'v3.0.0'
      };
    } catch (error) {
      return { error: error.message };
    }
  }
}

// Initialize PWA Manager when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    window.pwaManager = new PWAManager();
  });
} else {
  window.pwaManager = new PWAManager();
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
  module.exports = PWAManager;
} 
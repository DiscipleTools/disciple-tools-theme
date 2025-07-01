/**
 * Service Worker for Disciple Tools Mobile PWA
 * Provides offline caching, background sync, and push notifications
 */

const CACHE_NAME = 'dt-mobile-v3.0.0';
const CACHE_OFFLINE_NAME = 'dt-mobile-offline-v3.0.0';
const CACHE_DYNAMIC_NAME = 'dt-mobile-dynamic-v3.0.0';

// Core app shell files to cache immediately
const APP_SHELL_URLS = [
  '/',
  '/contacts',
  '/offline.html',
  '/dt-assets/build/css/mobile-styles.min.css',
  '/dt-assets/build/js/mobile-api.min.js',
  '/dt-assets/build/js/pwa-manager.min.js',
  '/wp-content/themes/disciple-tools-theme/dt-assets/images/dt-logo-mobile.png'
];

// API endpoints to cache dynamically
const API_CACHE_PATTERNS = [
  '/wp-json/dt/v1/contacts',
  '/wp-json/dt/v1/contact',
  '/wp-json/dt/v1/users',
  '/wp-json/dt/v1/search'
];

// Maximum cache sizes
const CACHE_LIMITS = {
  dynamic: 50, // 50 dynamic responses
  offline: 100, // 100 offline items
  images: 30   // 30 cached images
};

/**
 * Service Worker Installation
 * Cache the app shell immediately
 */
self.addEventListener('install', event => {
  console.log('SW: Installing service worker...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('SW: Caching app shell');
        return cache.addAll(APP_SHELL_URLS);
      })
      .then(() => {
        console.log('SW: App shell cached successfully');
        return self.skipWaiting(); // Force activation
      })
      .catch(error => {
        console.error('SW: Failed to cache app shell:', error);
      })
  );
});

/**
 * Service Worker Activation
 * Clean up old caches and take control
 */
self.addEventListener('activate', event => {
  console.log('SW: Activating service worker...');
  
  event.waitUntil(
    caches.keys()
      .then(cacheNames => {
        return Promise.all(
          cacheNames.map(cacheName => {
            if (cacheName !== CACHE_NAME && 
                cacheName !== CACHE_OFFLINE_NAME && 
                cacheName !== CACHE_DYNAMIC_NAME) {
              console.log('SW: Deleting old cache:', cacheName);
              return caches.delete(cacheName);
            }
          })
        );
      })
      .then(() => {
        console.log('SW: Service worker activated');
        return self.clients.claim(); // Take control immediately
      })
  );
});

/**
 * Fetch Event Handler
 * Implement cache-first strategy with network fallback
 */
self.addEventListener('fetch', event => {
  const request = event.request;
  const url = new URL(request.url);
  
  // Skip non-GET requests
  if (request.method !== 'GET') {
    return;
  }
  
  // Skip chrome-extension and moz-extension requests
  if (url.protocol === 'chrome-extension:' || url.protocol === 'moz-extension:') {
    return;
  }
  
  // Handle different types of requests
  if (isAppShellRequest(request)) {
    event.respondWith(handleAppShellRequest(request));
  } else if (isAPIRequest(request)) {
    event.respondWith(handleAPIRequest(request));
  } else if (isImageRequest(request)) {
    event.respondWith(handleImageRequest(request));
  } else if (isHTMLRequest(request)) {
    event.respondWith(handleHTMLRequest(request));
  } else {
    event.respondWith(handleOtherRequest(request));
  }
});

/**
 * Background Sync Handler
 * Handle offline actions when connection is restored
 */
self.addEventListener('sync', event => {
  console.log('SW: Background sync event:', event.tag);
  
  if (event.tag === 'contact-updates') {
    event.waitUntil(syncContactUpdates());
  } else if (event.tag === 'contact-creation') {
    event.waitUntil(syncContactCreation());
  } else if (event.tag === 'bulk-actions') {
    event.waitUntil(syncBulkActions());
  }
});

/**
 * Push Notification Handler
 * Display push notifications from the server
 */
self.addEventListener('push', event => {
  console.log('SW: Push notification received');
  
  let notificationData = {
    title: 'Disciple Tools',
    body: 'You have a new notification',
    icon: '/dt-assets/images/dt-logo-192.png',
    badge: '/dt-assets/images/dt-badge-72.png',
    tag: 'dt-notification',
    actions: [
      {
        action: 'view',
        title: 'View',
        icon: '/dt-assets/images/view-icon.png'
      },
      {
        action: 'dismiss',
        title: 'Dismiss',
        icon: '/dt-assets/images/dismiss-icon.png'
      }
    ]
  };
  
  // Parse push data if available
  if (event.data) {
    try {
      const pushData = event.data.json();
      notificationData = { ...notificationData, ...pushData };
    } catch (error) {
      console.error('SW: Error parsing push data:', error);
    }
  }
  
  event.waitUntil(
    self.registration.showNotification(notificationData.title, notificationData)
      .catch(error => {
        console.error('SW: Error showing notification:', error);
      })
  );
});

/**
 * Notification Click Handler
 * Handle user interactions with notifications
 */
self.addEventListener('notificationclick', event => {
  console.log('SW: Notification clicked:', event.notification.tag);
  
  event.notification.close();
  
  const action = event.action;
  const notificationData = event.notification.data || {};
  
  if (action === 'dismiss') {
    return; // Just close the notification
  }
  
  // Default action or 'view' action
  const urlToOpen = notificationData.url || '/contacts';
  
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true })
      .then(clientList => {
        // Check if there's already a window open
        for (const client of clientList) {
          if (client.url.includes(urlToOpen) && 'focus' in client) {
            return client.focus();
          }
        }
        
        // Open new window
        if (clients.openWindow) {
          return clients.openWindow(urlToOpen);
        }
      })
  );
});

/**
 * Request Type Checking Functions
 */
function isAppShellRequest(request) {
  return APP_SHELL_URLS.some(url => request.url.endsWith(url));
}

function isAPIRequest(request) {
  return API_CACHE_PATTERNS.some(pattern => request.url.includes(pattern));
}

function isImageRequest(request) {
  return request.destination === 'image' || 
         /\.(jpg|jpeg|png|gif|webp|svg)$/i.test(request.url);
}

function isHTMLRequest(request) {
  return request.headers.get('Accept').includes('text/html');
}

/**
 * Request Handler Functions
 */

// Cache-first strategy for app shell
async function handleAppShellRequest(request) {
  try {
    const cache = await caches.open(CACHE_NAME);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
      return cachedResponse;
    }
    
    const networkResponse = await fetch(request);
    cache.put(request, networkResponse.clone());
    return networkResponse;
  } catch (error) {
    console.error('SW: App shell request failed:', error);
    return caches.match('/offline.html');
  }
}

// Network-first strategy for API requests with offline queue
async function handleAPIRequest(request) {
  try {
    const networkResponse = await fetch(request);
    
    // Cache successful GET requests
    if (request.method === 'GET' && networkResponse.ok) {
      const cache = await caches.open(CACHE_DYNAMIC_NAME);
      await trimCache(CACHE_DYNAMIC_NAME, CACHE_LIMITS.dynamic);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.log('SW: API request failed, checking cache:', request.url);
    
    // Try to serve from cache for GET requests
    if (request.method === 'GET') {
      const cache = await caches.open(CACHE_DYNAMIC_NAME);
      const cachedResponse = await cache.match(request);
      
      if (cachedResponse) {
        return cachedResponse;
      }
    }
    
    // Queue POST/PUT/DELETE requests for background sync
    if (['POST', 'PUT', 'DELETE'].includes(request.method)) {
      await queueOfflineAction(request);
    }
    
    return new Response(
      JSON.stringify({ 
        error: 'Offline', 
        message: 'Request queued for sync when online',
        queued: true 
      }),
      { 
        status: 202,
        headers: { 'Content-Type': 'application/json' }
      }
    );
  }
}

// Cache-first strategy for images
async function handleImageRequest(request) {
  try {
    const cache = await caches.open(CACHE_DYNAMIC_NAME);
    const cachedResponse = await cache.match(request);
    
    if (cachedResponse) {
      return cachedResponse;
    }
    
    const networkResponse = await fetch(request);
    
    if (networkResponse.ok) {
      await trimCache(CACHE_DYNAMIC_NAME, CACHE_LIMITS.images);
      cache.put(request, networkResponse.clone());
    }
    
    return networkResponse;
  } catch (error) {
    console.error('SW: Image request failed:', error);
    // Return placeholder image or cached response
    const cache = await caches.open(CACHE_DYNAMIC_NAME);
    return cache.match('/dt-assets/images/placeholder-avatar.png') ||
           new Response('', { status: 404 });
  }
}

// Network-first strategy for HTML pages
async function handleHTMLRequest(request) {
  try {
    const networkResponse = await fetch(request);
    return networkResponse;
  } catch (error) {
    console.log('SW: HTML request failed, serving offline page');
    return caches.match('/offline.html');
  }
}

// Generic handler for other requests
async function handleOtherRequest(request) {
  try {
    return await fetch(request);
  } catch (error) {
    console.error('SW: Other request failed:', error);
    return new Response('', { status: 404 });
  }
}

/**
 * Utility Functions
 */

// Trim cache to specified limit
async function trimCache(cacheName, maxItems) {
  const cache = await caches.open(cacheName);
  const keys = await cache.keys();
  
  if (keys.length > maxItems) {
    const deletePromises = keys
      .slice(0, keys.length - maxItems)
      .map(key => cache.delete(key));
    
    await Promise.all(deletePromises);
  }
}

// Queue offline actions for background sync
async function queueOfflineAction(request) {
  const action = {
    url: request.url,
    method: request.method,
    headers: Object.fromEntries(request.headers.entries()),
    body: request.method !== 'GET' ? await request.text() : null,
    timestamp: Date.now()
  };
  
  // Store in IndexedDB for background sync
  const db = await openOfflineDB();
  const transaction = db.transaction(['offline_actions'], 'readwrite');
  const store = transaction.objectStore('offline_actions');
  
  await store.add(action);
  
  // Register background sync
  if ('serviceWorker' in navigator && 'sync' in window.ServiceWorkerRegistration.prototype) {
    self.registration.sync.register('contact-updates');
  }
}

// Background sync functions
async function syncContactUpdates() {
  console.log('SW: Syncing contact updates...');
  
  const db = await openOfflineDB();
  const transaction = db.transaction(['offline_actions'], 'readwrite');
  const store = transaction.objectStore('offline_actions');
  const actions = await store.getAll();
  
  for (const action of actions) {
    try {
      const response = await fetch(action.url, {
        method: action.method,
        headers: action.headers,
        body: action.body
      });
      
      if (response.ok) {
        await store.delete(action.id);
        console.log('SW: Successfully synced action:', action.url);
      }
    } catch (error) {
      console.error('SW: Failed to sync action:', action.url, error);
    }
  }
}

async function syncContactCreation() {
  // Similar to syncContactUpdates but for contact creation
  console.log('SW: Syncing contact creation...');
}

async function syncBulkActions() {
  // Handle bulk action sync
  console.log('SW: Syncing bulk actions...');
}

// IndexedDB helper
function openOfflineDB() {
  return new Promise((resolve, reject) => {
    const request = indexedDB.open('dt_offline', 1);
    
    request.onerror = () => reject(request.error);
    request.onsuccess = () => resolve(request.result);
    
    request.onupgradeneeded = event => {
      const db = event.target.result;
      
      if (!db.objectStoreNames.contains('offline_actions')) {
        const store = db.createObjectStore('offline_actions', { 
          keyPath: 'id',
          autoIncrement: true 
        });
        store.createIndex('timestamp', 'timestamp');
      }
    };
  });
}

console.log('SW: Service worker script loaded'); 
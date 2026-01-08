/* global jsObject */

class HomeApp {
  apps = [];
  links = [];

  constructor() {
    // Check authentication before loading content
    // If user is not authenticated, they will be redirected by PHP before this JavaScript runs
    if (jsObject.user_id === 0) {
      // This should not happen as PHP redirects unauthenticated users
      // But as a fallback, redirect to login
      const currentUrl = window.location.href;
      window.location.href =
        jsObject.wp_login_url +
        '?redirect_to=' +
        encodeURIComponent(currentUrl);
      return;
    }

    // User authenticated - proceed with normal flow
    // Determine current view from URL (?view=apps|training)
    const params = new URLSearchParams(window.location.search);
    const view = (params.get('view') || 'apps').toLowerCase();

    if (view === 'training') {
      this.loadTrainingVideos();
    } else {
      // default to apps
      this.loadApps();
    }
    this.bindEvents();
  }

  /**
   * Loads apps from REST API endpoint
   * @returns {Promise<void>}
   */
  async loadApps() {
    // Use REST API endpoint
    const url = new URL(
      jsObject.root + jsObject.parts.root + '/v1/' + jsObject.parts.type,
    );
    url.searchParams.append('action', 'get_apps');
    for (const [key, value] of Object.entries(jsObject.parts)) {
      url.searchParams.append('parts[' + key + ']', value);
    }

    try {
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': jsObject.nonce,
        },
      });

      const data = await response.json();

      if (data.success && data.apps) {
        // Split apps into apps and links arrays based on type property
        this.apps = [];
        this.links = [];

        for (const app of data.apps) {
          // Determine type: if type exists use it, otherwise use fallback logic
          let appType = app.type;
          if (!appType || (appType !== 'app' && appType !== 'link')) {
            // Fallback logic: if creation_type is 'coded', default to 'app', otherwise 'link'
            appType = app.creation_type === 'coded' ? 'app' : 'link';
          }

          if (appType === 'link') {
            this.links.push(app);
          } else {
            this.apps.push(app);
          }
        }

        // Display apps and links separately
        this.displayApps();
        this.displayLinks();
      } else {
        this.showError(
          'Failed to load apps: ' + (data.message || 'Unknown error'),
        );
      }
    } catch (error) {
      this.showError('Error loading apps: ' + error.message);
    }
  }

  /**
   * Display apps in the grid with new icon button layout
   * @returns {void}
   */
  displayApps() {
    let html = '';

    // Safety check: ensure apps is an array
    if (!Array.isArray(this.apps)) {
      console.error('Apps is not an array:', this.apps);
      html =
        '<div class="app-card-wrapper"><div class="app-card"><div class="app-icon"><i class="mdi mdi-alert"></i></div></div><div class="app-title">Error loading apps.</div></div>';
    } else if (this.apps.length === 0) {
      html =
        '<div class="app-card-wrapper"><div class="app-card"><div class="app-icon"><i class="mdi mdi-information"></i></div></div><div class="app-title">No apps available.</div></div>';
    } else {
      for (const app of this.apps) {
        // Trim title to max 12 characters with ellipsis to fit under card
        const trimmedTitle =
          app.title.length > 12
            ? app.title.substring(0, 12) + '...'
            : app.title;

        // Determine app type: if type exists use it, otherwise use fallback logic
        let appType = app.type;
        if (!appType || (appType !== 'app' && appType !== 'link')) {
          // Fallback logic: if creation_type is 'coded', default to 'app', otherwise 'link'
          appType = app.creation_type === 'coded' ? 'app' : 'link';
        }

        // App-type apps navigate in same tab with launcher parameter, link-type apps open in new tab
        let onClickHandler = '';
        if (appType === 'app') {
          // Check if app is cross-domain (different domain than current)
          const currentHost = window.location.hostname;
          const appUrlObj = new URL(app.url, window.location.origin);
          const appHost = appUrlObj.hostname;
          const isCrossDomain =
            appHost !== currentHost && appHost !== window.location.hostname;

          if (isCrossDomain) {
            // For cross-domain apps, use WordPress wrapper URL with app URL as parameter
            const wrapperUrl =
              jsObject.dt_home_magic_url +
              '?launcher=1&app_url=' +
              encodeURIComponent(app.url);
            onClickHandler = `onclick="window.location.href = '${wrapperUrl}'; return false;"`;
          } else {
            // For same-domain apps, add launcher=1 parameter
            const separator = app.url.includes('?') ? '&' : '?';
            const appUrlWithLauncher = app.url + separator + 'launcher=1';
            onClickHandler = `onclick="window.location.href = '${appUrlWithLauncher}'; return false;"`;
          }
        } else {
          // Open in new tab (link-type)
          onClickHandler = `onclick="window.open('${app.url}', '_blank'); return false;"`;
        }

        // Determine icon display: image or icon class
        let iconHtml = '';
        const isImageIcon =
          app.icon && (app.icon.startsWith('http') || app.icon.startsWith('/'));

        if (isImageIcon) {
          // Render image icon
          const safeIconUrl = app.icon.replace(/"/g, '&quot;');
          const safeTitle = trimmedTitle.replace(/"/g, '&quot;');
          iconHtml = `<img src="${safeIconUrl}" alt="${safeTitle}" />`;
        } else {
          // Render icon class with color support
          // Determine default icon color based on theme
          // Custom colors override defaults
          let iconColor = 'inherit';
          const hasCustomColor = app.color && app.color.trim() !== '';

          if (hasCustomColor) {
            // Use custom color if specified
            iconColor = app.color.trim();
          }
          iconHtml = `<i class="${app.icon}" style="color: ${iconColor};" data-has-custom-color="${hasCustomColor}"></i>`;
        }

        const appHtml = `
                            <div class="app-card-wrapper">
                                <div class="app-card" ${onClickHandler} title="${app.title}">
                                    <div class="app-icon">
                                        ${iconHtml}
                                    </div>
                                </div>
                                <div class="app-title">${trimmedTitle}</div>
                            </div>
                        `;

        //console.log('Generated HTML for app "' + app.title + '":', appHtml);
        html += appHtml;
      }
    }

    //console.log('Final HTML being inserted:', html);
    // $('#apps-grid').html(html);
    const appsGrid = document.getElementById('apps-grid');
    if (appsGrid) {
      appsGrid.innerHTML = html;
    }
  }

  /**
   * Display links in the links list with link widget layout
   * @param links
   */
  displayLinks(links) {
    let html = '';

    // Safety check: ensure links is an array
    if (!Array.isArray(this.links)) {
      console.error('Links is not an array:', this.links);
      html =
        '<div class="link-item"><div class="link-item__title">Error loading links.</div></div>';
    } else if (this.links.length === 0) {
      html =
        '<div class="link-item"><div class="link-item__title">No links available.</div></div>';
    } else {
      for (const link of this.links) {
        // Escape HTML to prevent XSS
        const safeUrl = (link.url || '#')
          .replace(/'/g, "\\'")
          .replace(/"/g, '&quot;');
        const safeUrlAttr = (link.url || '#').replace(/"/g, '&quot;');
        const safeTitle = (link.title || 'Link')
          .replace(/</g, '&lt;')
          .replace(/>/g, '&gt;')
          .replace(/"/g, '&quot;');
        const safeIcon = link.icon || 'mdi mdi-link';

        // Determine default icon color based on theme (same logic as apps)
        // Custom colors override defaults
        let iconColor = 'inherit';
        const hasCustomColor =
          link.color &&
          typeof link.color === 'string' &&
          link.color.trim() !== '';

        if (hasCustomColor) {
          // Validate hex color format (#rrggbb or #rgb)
          const hexColorPattern = /^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/;
          if (hexColorPattern.test(link.color.trim())) {
            iconColor = link.color.trim();
          }
        }

        // Determine icon display: image or icon class
        let iconHtml = '';
        if (
          link.icon &&
          (link.icon.startsWith('http') || link.icon.startsWith('/'))
        ) {
          const safeIconUrl = link.icon.replace(/"/g, '&quot;');
          iconHtml = `<img src="${safeIconUrl}" alt="${safeTitle}" />`;
        } else {
          // Apply color to icon using inline style with data attribute for theme updates
          iconHtml = `<i class="${safeIcon}" aria-hidden="true" style="color: ${iconColor};" data-has-custom-color="${hasCustomColor}"></i>`;
        }

        const linkHtml = `
            <div class="link-item" onclick="window.open('${safeUrl}', '_blank')" title="${safeTitle}">
                <div class="link-item__icon">
                    ${iconHtml}
                </div>
                <div class="link-item__content">
                    <div class="link-item__title">${safeTitle}</div>
                    <a href="${safeUrlAttr}" class="link-item__url" target="_blank" rel="noopener noreferrer" onclick="event.stopPropagation();">
                        ${safeUrlAttr}
                    </a>
                </div>
                <button class="link-item__copy" data-url="${safeUrl}" title="Copy link">
                    <i class="mdi mdi-content-copy"></i>
                </button>
            </div>
        `;

        html += linkHtml;
      }
    }

    const linksList = document.getElementById('links-list');
    if (linksList) {
      linksList.innerHTML = html;
    }

    // Attach copy link button click event listeners
    const linksCopy = linksList.querySelectorAll('.link-item__copy');
    for (const linkCopy of linksCopy) {
      linkCopy.addEventListener('click', (evt) => {
        this.copyLinkUrl(linkCopy.dataset.url, evt);
      });
    }
  }
  bindEvents() {}

  /**
   * Copy link URL to clipboard
   */
  copyLinkUrl(url, evt) {
    // Prevent event bubbling
    if (evt) {
      evt.stopPropagation();
      evt.preventDefault();
    }
    const button = evt.currentTarget;

    // Store original button state
    const originalIcon = button.innerHTML;
    const originalClass = button.className;

    // Try modern clipboard API first
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard
        .writeText(url)
        .then(() => {
          this.showCopyFeedback(button, originalIcon, originalClass);
        })
        .catch((err) => {
          console.error('Failed to copy:', err);
          this.fallbackCopy(url, button, originalIcon, originalClass);
        });
    } else {
      // Fallback for older browsers
      this.fallbackCopy(url, button, originalIcon, originalClass);
    }
  }

  /**
   * Fallback copy method for older browsers
   */
  fallbackCopy(url, button, originalIcon, originalClass) {
    const textArea = document.createElement('textarea');
    textArea.value = url;
    textArea.style.position = 'fixed';
    textArea.style.opacity = '0';
    document.body.appendChild(textArea);
    textArea.select();

    try {
      document.execCommand('copy');
      this.showCopyFeedback(button, originalIcon, originalClass);
    } catch (err) {
      console.error('Fallback copy failed:', err);
    }

    document.body.removeChild(textArea);
  }

  /**
   * Show visual feedback when URL is copied
   */
  showCopyFeedback(button, originalIcon, originalClass) {
    // Change to checkmark icon and success color
    button.innerHTML = '<i class="mdi mdi-check"></i>';
    button.classList.add('copied');

    // Reset after animation
    setTimeout(function () {
      button.innerHTML = originalIcon;
      button.className = originalClass;
    }, 1500);
  }

  async loadTrainingVideos() {
    // Use REST API endpoint
    const url = new URL(
      jsObject.root + jsObject.parts.root + '/v1/' + jsObject.parts.type,
    );
    url.searchParams.append('action', 'get_training');
    for (const [key, value] of Object.entries(jsObject.parts)) {
      url.searchParams.append('parts[' + key + ']', value);
    }

    try {
      const response = await fetch(url, {
        method: 'GET',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': jsObject.nonce,
        },
      });

      const data = await response.json();

      if (data.success && data.training_videos) {
        this.displayTrainingVideos(data.training_videos);
      } else {
        this.showError(
          'Failed to load training videos: ' +
            (data.message || 'Unknown error'),
        );
      }
    } catch (error) {
      this.showError('Error loading training videos: ' + error.message);
    }
  }

  /**
   * Display training videos in the grid with video previews
   */
  displayTrainingVideos(videos) {
    let html = '';

    if (videos.length === 0) {
      html =
        '<div class="training-card"><div class="training-video-title-text">No training videos available.</div></div>';
    } else {
      for (const video of videos) {
        // Extract YouTube video ID for embedded preview
        const videoId = this.extractYouTubeVideoId(video.video_url);
        const thumbnailUrl =
          video.thumbnail_url ||
          (videoId
            ? `https://img.youtube.com/vi/${videoId}/maxresdefault.jpg`
            : '');

        // Get video duration if available
        const duration = video.duration || '0:00';

        const videoHtml = `
            <div class="training-card" data-video-id="${videoId || ''}" data-video-url="${video.video_url}" data-video-title="${video.title}" title="${video.title}">
                <!-- Video Info Header: Title and Duration on same line -->
                <div class="training-video-info">
                    <div class="training-video-title-text">${video.title}</div>
                    <div class="training-video-duration-badge">${duration}</div>
                </div>
                <!-- Thumbnail Container with Splash Screen -->
                <div class="training-video-thumbnail-container">
                    <img src="${thumbnailUrl}" alt="${video.title}" class="training-video-thumbnail" onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgdmlld0JveD0iMCAwIDIwMCAxNTAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxyZWN0IHdpZHRoPSIyMDAiIGhlaWdodD0iMTUwIiBmaWxsPSIjRjVGNUY1Ii8+CjxwYXRoIGQ9Ik04MCA2MEwxMjAgOTBMMTgwIDYwVjkwSDEyMFY2MEg4MFoiIGZpbGw9IiM2Nzc5RUEiLz4KPC9zdmc+'" />
                    <div class="training-video-overlay">
                        <div class="training-play-button">
                            <i class="mdi mdi-play"></i>
                        </div>
                    </div>
                </div>
                <!-- Embedded Video Container (hidden initially) -->
                <div class="training-video-embed" style="display: none;">
                    <iframe width="100%" height="100%" src="" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                    <div class="training-video-external-link">
                        <a href="${video.video_url}" target="_blank" rel="noopener noreferrer" class="external-link-button">
                            <i class="mdi mdi-open-in-new"></i>
                            <span>Watch on ${video.video_url.includes('youtube.com') || video.video_url.includes('youtu.be') ? 'YouTube' : 'Vimeo'}</span>
                        </a>
                    </div>
                </div>
            </div>
        `;

        html += videoHtml;
      }
    }

    const trainingGrid = document.getElementById('training-grid');
    if (trainingGrid) {
      trainingGrid.innerHTML = html;
    }

    const trainingCards = document.querySelectorAll('.training-card');
    for (const trainingCard of trainingCards) {
      trainingCard.addEventListener('click', (evt) => {
        this.toggleVideoPlayback(evt.currentTarget);
      });
    }
  }

  /**
   * Extract YouTube video ID from URL
   */
  extractYouTubeVideoId(url) {
    const regExp =
      /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
    const match = url.match(regExp);
    return match && match[2].length === 11 ? match[2] : null;
  }

  /**
   * Toggle video playback between thumbnail and embedded player
   */
  toggleVideoPlayback(cardElement) {
    const thumbnailContainer = cardElement.querySelector(
      '.training-video-thumbnail-container',
    );
    const embedContainer = cardElement.querySelector('.training-video-embed');
    const iframe = embedContainer.querySelector('iframe');
    const videoId = cardElement.getAttribute('data-video-id');
    const videoUrl = cardElement.getAttribute('data-video-url');

    if (thumbnailContainer.style.display !== 'none') {
      // Switch to embedded player
      thumbnailContainer.style.display = 'none';
      embedContainer.style.display = 'block';
      cardElement.classList.add('playing');

      // Set up the iframe source
      if (videoId) {
        // YouTube video
        iframe.src = `https://www.youtube.com/embed/${videoId}?autoplay=1&rel=0&modestbranding=1`;
      } else if (videoUrl.includes('vimeo.com')) {
        // Vimeo video
        const vimeoId = this.extractVimeoVideoId(videoUrl);
        if (vimeoId) {
          iframe.src = `https://player.vimeo.com/video/${vimeoId}?autoplay=1&title=0&byline=0&portrait=0`;
        }
      } else {
        // Fallback to external link
        window.open(videoUrl, '_blank');
        return;
      }
    } else {
      // Switch back to thumbnail
      embedContainer.style.display = 'none';
      thumbnailContainer.style.display = 'block';
      cardElement.classList.remove('playing');
      iframe.src = ''; // Stop the video
    }
  }

  /**
   * Extract Vimeo video ID from URL
   */
  extractVimeoVideoId(url) {
    const regExp = /vimeo\.com\/(\d+)/;
    const match = url.match(regExp);
    return match ? match[1] : null;
  }

  /**
   * Show error message
   */
  showError(message) {
    console.error('Home Screen Error:', message);
    // You could also display this in the UI if needed
  }
}
document.addEventListener('DOMContentLoaded', function () {
  try {
    window.homeAppInstance = new HomeApp();
  } catch (error) {
    console.error('Error initializing home app:', error);
  }
});
window.HomeApp = HomeApp;

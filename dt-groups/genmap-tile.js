(function ($) {
  'use strict';

  const TILE_SELECTOR = '#group-genmap-tile';
  const ENDPOINT = 'metrics/personal/genmap';
  const MAX_CANVAS_HEIGHT = 320;
  const MIN_CANVAS_HEIGHT = 220;
  const LAYOUT_STORAGE_KEY = 'group_genmap_layout';
  const DEFAULT_PAYLOAD = {
    p2p_type: 'groups_to_groups',
    p2p_direction: 'to',
    post_type: 'groups',
    gen_depth_limit: 100,
    show_archived: false,
    data_layers: {
      color: 'default-node-color',
      layers: [],
      show_icons_for_fields: [],
    },
    slug: 'personal',
  };

  function getDefaultLayout() {
    // Check if mobile (screen width < 768px)
    const isMobile = window.innerWidth < 768;
    if (isMobile) {
      return 't2b'; // Vertical for mobile
    }
    // Check localStorage for saved preference
    const saved = localStorage.getItem(LAYOUT_STORAGE_KEY);
    if (saved === 't2b' || saved === 'l2r') {
      return saved;
    }
    // Default to vertical for desktop
    return 't2b';
  }

  function saveLayoutPreference(layout) {
    localStorage.setItem(LAYOUT_STORAGE_KEY, layout);
  }

  function isMobileView() {
    return window.innerWidth < 768;
  }

  /**
   * Determine graph orientation based on section CSS classes
   * @param {jQuery} wrapper - Optional wrapper to check for stored orientation (for modal)
   * @returns {string} - 't2b' for top-to-bottom (vertical) or 'l2r' for left-to-right (horizontal)
   */
  function getGraphOrientationFromSection(wrapper = null) {
    // Check if wrapper has stored graph orientation (for modal context)
    if (wrapper && wrapper.data('graphOrientation')) {
      return wrapper.data('graphOrientation');
    }

    const genmapSection = jQuery('#genmap');
    if (!genmapSection.length) {
      return 't2b'; // Default to vertical
    }

    // Check if section has xlarge-6, large-12, or medium-6 classes (half width = vertical graph)
    // Half width sections use vertical graphs
    if (
      genmapSection.hasClass('xlarge-6') ||
      genmapSection.hasClass('large-12') ||
      genmapSection.hasClass('medium-6')
    ) {
      return 't2b'; // Top-to-bottom graph (vertical, uses vertical space)
    }

    // Check if section has small-12 class (full width = horizontal graph)
    // Full width sections use horizontal graphs
    if (genmapSection.hasClass('small-12')) {
      return 'l2r'; // Left-to-right graph (horizontal, uses horizontal space)
    }

    // Default to vertical if no matching classes found
    return 't2b';
  }

  jQuery(document).ready(() => {
    // Add CSS for selected node border and truncation
    if (!document.getElementById('group-genmap-tile-styles')) {
      const style = document.createElement('style');
      style.id = 'group-genmap-tile-styles';
      style.textContent = `
        .group-genmap-chart .node.group-genmap-node-selected {
          border: 2px dashed rgba(238, 217, 54, 0.8) !important;
          box-shadow: 0 0 0 2px rgba(238, 217, 54, 0.3);
        }
        .group-genmap-chart .node .title {
          max-width: 120px;
          overflow: hidden;
          text-overflow: ellipsis;
          white-space: nowrap;
        }
        @media (max-width: 767px) {
          .group-genmap-layout-toggle,
          .group-genmap-maximize {
            display: none !important;
          }
        }
        /* Vertical layout connection line fixes */
        .group-genmap-chart .orgchart.t2b .nodes {
          justify-content: center !important;
        }
        /* Ensure hierarchy containers center their nodes */
        .group-genmap-chart .orgchart.t2b .hierarchy {
          text-align: center !important;
        }
        /* Vertical mode: Set specific left position for horizontal connecting line */
        .group-genmap-chart .orgchart.t2b .hierarchy::before {
          border-top: 2px solid var(--dt-blue-o) !important;
          left: 40px !important;
          width: 100% !important;
        }
        /* Vertical mode: First child uses specific left position */
        .group-genmap-chart .orgchart.t2b .hierarchy:first-child::before,
        .group-genmap-chart .orgchart.t2b .hierarchy.isSiblingsCollapsed.left-sibs::before {
          left: 65px !important;
          width: calc(50% + 1px) !important;
        }
        /* Vertical mode: Last child starts from left, extends to center */
        .group-genmap-chart .orgchart.t2b .hierarchy:last-child::before,
        .group-genmap-chart .orgchart.t2b .hierarchy.isSiblingsCollapsed.right-sibs::before {
          left: 0 !important;
          width: calc(50% + 1px) !important;
        }
        /* Vertical mode: Only child - centered vertical line connecting to parent */
        .group-genmap-chart .orgchart.t2b .hierarchy:not(.hidden):only-child::before {
          left: calc(50% - 1px) !important;
          width: 2px !important;
          border-top: none !important;
          border-left: none !important;
          border-right: none !important;
          border-bottom: 2px solid var(--dt-blue-o) !important;
          height: 11px !important;
        }
        /* Ensure vertical line from parent node is centered */
        .group-genmap-chart .orgchart.t2b .node:not(:only-child)::after {
          left: calc(50% - 1px) !important;
        }
        /* Ensure vertical line to parent node is centered (all levels) */
        .group-genmap-chart .orgchart.t2b ul li ul li > .node::before {
          left: calc(50% - 1px) !important;
        }
        /* Horizontal mode: Revert to base CSS defaults (let base CSS handle it) */
        .group-genmap-chart .orgchart.l2r .hierarchy::before {
          left: 0 !important;
        }
        .group-genmap-chart .orgchart.l2r .hierarchy:first-child::before,
        .group-genmap-chart .orgchart.l2r .hierarchy.isSiblingsCollapsed.left-sibs::before {
          left: 25px !important;
        }
      `;
      document.head.appendChild(style);
    }

    const wrapper = $(TILE_SELECTOR);
    if (!wrapper.length) {
      return;
    }

    // Initialize layout toggle button
    const layoutToggle = $('#group-genmap-layout-toggle');
    const currentLayout = getDefaultLayout();

    // Show toggle only on desktop and only for legacy orgchart (not D3)
    // D3 visualization will hide it when rendering
    if (!isMobileView()) {
      layoutToggle.show();
      updateLayoutToggleIcon(currentLayout);
    }

    // Setup layout toggle handler will be set up after functions are defined

    const postId = parseInt(wrapper.data('postId'), 10);
    if (!postId) {
      setMessage(wrapper, 'empty');
      return;
    }

    // Store initial layout
    wrapper.data('currentLayout', currentLayout);

    // Set initial section width (with delay to ensure DOM is ready)
    setTimeout(() => {
      updateSectionWidth(currentLayout);
    }, 100);

    fetchGenmap(wrapper, postId, currentLayout);
  });

  function fetchGenmap(wrapper, focusId, layout = null) {
    setMessage(wrapper, 'loading');
    const currentLayout =
      layout || wrapper.data('currentLayout') || getDefaultLayout();
    wrapper.data('currentLayout', currentLayout);

    const payload = {
      ...DEFAULT_PAYLOAD,
      focus_id: focusId,
    };

    const request = window.makeRequest
      ? window.makeRequest('POST', ENDPOINT, payload)
      : $.ajax({
          type: 'POST',
          url: `${window.wpApiShare?.root || ''}dt/v1/${ENDPOINT}`,
          beforeSend: (xhr) => {
            if (window.wpApiShare?.nonce) {
              xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
            }
          },
          data: payload,
        });

    const deferred =
      typeof request.promise === 'function' ? request.promise() : request;

    deferred
      .then((response) => {
        const genmap = response?.genmap || null;
        if (typeof genmap === 'string' && genmap.includes('No Results')) {
          setMessage(wrapper, 'empty');
          return;
        }
        if (!genmap || !Object.keys(genmap).length) {
          setMessage(wrapper, 'empty');
          return;
        }
        const currentLayout =
          wrapper.data('currentLayout') || getDefaultLayout();
        const sanitizedGenmap = sanitizeNode(genmap);
        // Store genmap data for layout switching
        wrapper.data('currentGenmapData', sanitizedGenmap);

        // Phase 3: Use D3.js visualization - check stored preference or default
        // Store preference in wrapper data so layout toggle can access it
        let useD3Visualization = wrapper.data('useD3Visualization');
        if (useD3Visualization === undefined) {
          // Default to D3.js visualization
          useD3Visualization = true;
          wrapper.data('useD3Visualization', useD3Visualization);
        }

        // Store current layout for tile size management (even for D3)
        wrapper.data('currentLayout', currentLayout);

        if (useD3Visualization && typeof d3 !== 'undefined') {
          renderD3Chart(wrapper, sanitizedGenmap, currentLayout);
        } else {
          renderChart(wrapper, sanitizedGenmap, currentLayout);
        }
      })
      .fail(() => {
        setMessage(wrapper, 'error');
      })
      .always(() => {
        wrapper.addClass('group-genmap-loaded');
      });
  }

  function renderChart(wrapper, genmap, layout = null) {
    if (!genmap) {
      setMessage(wrapper, 'empty');
      return;
    }

    const currentLayout =
      layout || wrapper.data('currentLayout') || getDefaultLayout();
    wrapper.data('currentLayout', currentLayout);

    const container = wrapper.find('.group-genmap-chart');
    container.empty();
    container
      .css({
        overflow: 'auto',
        width: '100%',
      })
      .removeClass('group-genmap-chart--ready');

    const nodeTemplate = function (data) {
      return `
        <div class="title" data-item-id="${window.lodash.escape(data.id)}" title="${window.lodash.escape(data.name || '')}">${window.lodash.escape(data.name || '')}</div>
        <div class="content" style="padding: 2px;">${window.lodash.escape(data.content || '')}</div>
      `;
    };

    const orgchart = container.orgchart({
      data: genmap,
      nodeContent: 'content',
      direction: currentLayout,
      nodeTemplate: nodeTemplate,
      createNode: function ($node, data) {
        const sharedFlag = String(data.shared ?? '1');
        $node.attr('data-shared', sharedFlag);

        if (data.statusColor) {
          $node.css('background-color', data.statusColor);
          $node
            .find('.title, .content')
            .css('background-color', data.statusColor);
          $node.find('.content').css('border', '0');
        }

        if (data.isNonShared) {
          $node.addClass('group-genmap-node-private');
        }
      },
    });

    // Bind click handler for node selection.
    container.off('click', '.node');
    container.on('click', '.node', function () {
      const node = $(this);
      if (String(node.data('shared')) === '0') {
        return;
      }
      const nodeId = node.attr('id');
      const parentId = node.data('parent') || 0;
      if (!nodeId) {
        return;
      }

      // Remove previous selection
      container.find('.node').removeClass('group-genmap-node-selected');
      // Add selection to current node
      node.addClass('group-genmap-node-selected');

      openGenmapDetails(wrapper, nodeId, parentId, 'groups');
    });

    // Keep a handle for potential future use/debugging.
    wrapper.data('orgchartInstance', orgchart);
    adjustCanvasSize(container);
    container.addClass('group-genmap-chart--ready');
    setMessage(wrapper, 'ready');

    // Update layout toggle icon
    updateLayoutToggleIcon(currentLayout);

    // Update section width based on layout
    updateSectionWidth(currentLayout);

    // Apply dynamic styles for connection lines after chart renders
    applyConnectionLineStyles(container, currentLayout);

    // Recalculate Masonry grid after chart has fully rendered and we have final dimensions
    // This ensures the grid positions tiles correctly with the final chart size
    setTimeout(() => {
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          recalculateMasonryGrid();
        });
      });
    }, 300); // Delay to ensure chart is fully rendered and dimensions are final
  }

  function applyConnectionLineStyles(container, layout) {
    // Use setTimeout to ensure chart is fully rendered before applying styles
    setTimeout(() => {
      // Remove any existing dynamic style tag
      const existingStyleId = 'group-genmap-dynamic-connection-styles';
      const existingStyle = document.getElementById(existingStyleId);
      if (existingStyle) {
        existingStyle.remove();
      }

      // Create style tag with high specificity targeting the specific chart
      const style = document.createElement('style');
      style.id = existingStyleId;

      if (layout === 't2b') {
        // Vertical mode: Apply specific pixel values with maximum specificity
        style.textContent = `
          #group-genmap-tile .group-genmap-chart .orgchart.t2b .hierarchy::before {
            left: 40px !important;
            width: 100% !important;
          }
          #group-genmap-tile .group-genmap-chart .orgchart.t2b .hierarchy:first-child::before,
          #group-genmap-tile .group-genmap-chart .orgchart.t2b .hierarchy.isSiblingsCollapsed.left-sibs::before {
            left: 65px !important;
            width: calc(50% + 1px) !important;
          }
          #group-genmap-tile .group-genmap-chart .orgchart.t2b .hierarchy:last-child::before,
          #group-genmap-tile .group-genmap-chart .orgchart.t2b .hierarchy.isSiblingsCollapsed.right-sibs::before {
            left: 0 !important;
            width: calc(50% + 1px) !important;
          }
          /* styles of link lines */
          .orgchart .hierarchy::before {
              content: "";
              position: absolute;
              top: -11px; /* -(background square size + half width of line) */
              left: 65px !important;
              border-top: 2px solid var(--dt-blue-o);
              box-sizing: border-box;
          }
          .orgchart .hierarchy:last-child::before, .orgchart .hierarchy.isSiblingsCollapsed.right-sibs::before {
              width: 0 !important;
          }
        `;
      } else {
        // Horizontal mode: Revert to defaults
        style.textContent = `
          #group-genmap-tile .group-genmap-chart .orgchart.l2r .hierarchy::before {
            left: 0 !important;
          }
          #group-genmap-tile .group-genmap-chart .orgchart.l2r .hierarchy:first-child::before,
          #group-genmap-tile .group-genmap-chart .orgchart.l2r .hierarchy.isSiblingsCollapsed.left-sibs::before {
            left: 25px !important;
          }
        `;
      }

      document.head.appendChild(style);
    }, 100); // Small delay to ensure DOM is ready
  }

  function adjustCanvasSize(container) {
    const orgchartEl = container.find('.orgchart');
    if (!orgchartEl.length) {
      return;
    }

    const chartHeight = orgchartEl.outerHeight(true) || MIN_CANVAS_HEIGHT;
    const computedHeight = Math.min(
      Math.max(chartHeight + 32, MIN_CANVAS_HEIGHT),
      MAX_CANVAS_HEIGHT,
    );
    container.height(computedHeight);

    const chartWidth = orgchartEl.outerWidth(true);
    if (chartWidth > container.innerWidth()) {
      const scrollLeft = (chartWidth - container.innerWidth()) / 2;
      container.scrollLeft(scrollLeft);
    }
  }

  function sanitizeNode(node) {
    if (!node || typeof node !== 'object') {
      return null;
    }

    const sanitized = {
      ...node,
    };

    sanitized.children = Array.isArray(node.children)
      ? node.children
          .map((child) => sanitizeNode(child))
          .filter((child) => !!child)
      : [];

    // Ensure name field is set correctly - use fallback if empty
    if (!sanitized.name || sanitized.name.trim() === '') {
      sanitized.name =
        sanitized.title || sanitized.post_title || sanitized.id || '';
    }

    const sharedFlag = String(sanitized.shared ?? '1');
    if (sharedFlag === '0') {
      sanitized.name = '.......';
      sanitized.content = '';
      sanitized.isNonShared = true;
    }

    // Map status to statusColor - matching legacy flow behavior
    // The API returns only 'status' property (e.g., "active"), we need to map it to statusColor
    // using dtGroupGenmap.statusField.colors object
    const archivedKey = window.dtGroupGenmap?.statusField?.archived_key || '';
    const colors = window.dtGroupGenmap?.statusField?.colors || {};

    // Always compute statusColor from status property (matching legacy flow)
    // This ensures statusColor is set before data flows to D3 visualization
    if (sanitized.status) {
      // Direct lookup in colors object - status value should match the key
      if (colors[sanitized.status]) {
        sanitized.statusColor = colors[sanitized.status];
      } else if (archivedKey && sanitized.status === archivedKey) {
        // Handle archived/inactive status with grey color
        sanitized.statusColor = '#808080';
      } else {
        // Fallback to getStatusColor helper which handles defaults
        sanitized.statusColor = getStatusColor(sanitized.status);
      }
    } else {
      // No status provided - use default color
      sanitized.statusColor = getStatusColor(null);
    }

    return sanitized;
  }

  /**
   * Phase 2: Data Transformation Functions for D3.js
   *
   * These functions transform the API response into D3 hierarchy format
   * and add computed properties for visualization.
   */

  /**
   * Ellipsize a name to fit within node display
   * @param {string} name - The full name
   * @param {number} maxLength - Maximum characters (default: 15)
   * @returns {string} - Ellipsized name
   */
  function ellipsizeName(name, maxLength = 15) {
    if (!name || typeof name !== 'string') {
      return '';
    }
    if (name.length <= maxLength) {
      return name;
    }
    return name.substring(0, maxLength - 3) + '...';
  }

  /**
   * Get group type icon path
   * @param {string} groupType - The group type key (e.g., 'church', 'group', 'pre-group', 'team')
   * @returns {string} - Icon path URL
   */
  function getGroupTypeIcon(groupType) {
    const icons = window.dtGroupGenmap?.groupTypeIcons || {};
    return icons[groupType] || icons['group'] || '';
  }

  /**
   * Get status color for a node
   * @param {string} status - The status key
   * @returns {string} - Color hex code
   */
  function getStatusColor(status) {
    const colors = window.dtGroupGenmap?.statusField?.colors || {};
    const archivedKey = window.dtGroupGenmap?.statusField?.archived_key || '';

    if (status && colors[status]) {
      return colors[status];
    } else if (status && archivedKey && status === archivedKey) {
      return '#808080';
    }
    return '#3f729b'; // Default blue
  }

  /**
   * Enhance node data with computed properties for D3 visualization
   * @param {Object} node - The node data from API
   * @param {number} generation - Current generation level (0 = root)
   * @returns {Object} - Enhanced node data
   */
  function enhanceNodeData(node, generation = 0) {
    if (!node || typeof node !== 'object') {
      return null;
    }

    const enhanced = {
      ...node,
      // Preserve original data
      originalName: node.name || '',
      originalStatus: node.status || '',
      originalShared: node.shared ?? 1,

      // Computed properties
      generation: generation,
      collapsed: false, // Default: expanded
      _children: null, // For collapse/expand functionality

      // Visual properties
      nodeSize: {
        width: 60,
        height: 30,
      },

      // Display properties
      displayName: ellipsizeName(node.name || '', 15),

      // Status and color - ensure statusColor is computed from status
      statusColor:
        node.statusColor ||
        (() => {
          const colors = window.dtGroupGenmap?.statusField?.colors || {};
          const archivedKey =
            window.dtGroupGenmap?.statusField?.archived_key || '';
          if (node.status && colors[node.status]) {
            return colors[node.status];
          } else if (
            node.status &&
            archivedKey &&
            node.status === archivedKey
          ) {
            return '#808080';
          }
          return getStatusColor(node.status);
        })(),

      // Note: iconPath removed - icons will be in popover (Phase 5)

      // Shared/private flag
      isNonShared: String(node.shared ?? '1') === '0',

      // Recursively enhance children
      children: Array.isArray(node.children)
        ? node.children
            .map((child) => enhanceNodeData(child, generation + 1))
            .filter((child) => !!child)
        : [],
    };

    // If node is non-shared, update display name
    if (enhanced.isNonShared) {
      enhanced.displayName = '.......';
      enhanced.iconPath = '';
    }

    return enhanced;
  }

  /**
   * Transform genmap data to D3 hierarchy format
   * @param {Object} genmapData - The genmap data from API
   * @returns {Object|null} - D3 hierarchy root node or null
   */
  function transformToD3Hierarchy(genmapData) {
    // Check if D3.js is available
    if (typeof d3 === 'undefined' || !d3.hierarchy) {
      console.warn('D3.js is not loaded. Cannot transform to D3 hierarchy.');
      return null;
    }

    if (!genmapData || typeof genmapData !== 'object') {
      return null;
    }

    // Enhance the data with computed properties
    const enhancedData = enhanceNodeData(genmapData, 0);

    if (!enhancedData) {
      return null;
    }

    // Create D3 hierarchy
    // Note: D3.hierarchy expects children to be in a 'children' property
    // Our data already has this structure, so we can use it directly
    const root = d3.hierarchy(enhancedData, (d) => {
      // Return children array, or null if no children (D3 expects null for leaf nodes)
      return d.children && d.children.length > 0 ? d.children : null;
    });

    // Add computed properties to each node in the hierarchy
    root.each((node) => {
      // Ensure each node has the enhanced properties
      if (!node.data.nodeSize) {
        node.data.nodeSize = { width: 60, height: 30 };
      }
      if (!node.data.displayName) {
        node.data.displayName = ellipsizeName(node.data.name || '', 15);
      }
      // Always ensure statusColor is set - compute from status if missing
      // This ensures status colors are properly applied even if sanitizeNode didn't set it
      if (!node.data.statusColor) {
        if (node.data.status) {
          const colors = window.dtGroupGenmap?.statusField?.colors || {};
          const archivedKey =
            window.dtGroupGenmap?.statusField?.archived_key || '';

          if (colors[node.data.status]) {
            node.data.statusColor = colors[node.data.status];
          } else if (archivedKey && node.data.status === archivedKey) {
            node.data.statusColor = '#808080';
          } else {
            node.data.statusColor = getStatusColor(node.data.status);
          }
        } else {
          // No status - use default
          node.data.statusColor = getStatusColor(null);
        }
      }
      // Note: iconPath removed - icons will be in popover (Phase 5)
      if (node.data.isNonShared) {
        node.data.displayName = '.......';
      }

      // Initialize collapse state
      if (node.children && node.children.length > 0) {
        node.data.collapsed = false;
        node.data._children = null;
      }
    });

    return root;
  }

  /**
   * Phase 3: D3 Tree Visualization Core
   *
   * Functions for creating and rendering the D3.js tree visualization
   */

  /**
   * Phase 4: Collapse/Expand Functionality
   *
   * Functions for collapsing and expanding tree branches
   */

  /**
   * Phase 5: Popover Implementation
   *
   * Functions for displaying lightweight popover with group details, icons, and actions
   */

  /**
   * Show popover for a selected node
   * @param {jQuery} wrapper - jQuery wrapper element
   * @param {Object} nodeData - D3 node data
   * @param {Event} event - Click event
   */
  function showGenmapPopover(wrapper, nodeData, event) {
    // Remove any existing popover
    hideGenmapPopover(wrapper);

    // Get container and SVG for positioning
    const container = wrapper.find('.group-genmap-chart');
    const svg = wrapper.data('d3Svg');
    if (!container.length || !svg || !svg.node()) {
      return;
    }

    // Check if we're in a modal context (for z-index adjustment)
    const isInModal = wrapper.closest('#group-genmap-full-modal').length > 0;

    // Get node position in SVG coordinates
    const nodeGroup = d3.select(event.currentTarget);
    const transform = nodeGroup.attr('transform');
    const match = transform.match(/translate\(([^,]+),\s*([^)]+)\)/);
    if (!match) {
      return;
    }

    const nodeX = parseFloat(match[2]); // Vertical position
    const nodeY = parseFloat(match[1]); // Horizontal position

    // Get SVG bounding box
    const svgRect = svg.node().getBoundingClientRect();

    // Get current zoom transform
    const zoomTransform = d3.zoomTransform(svg.node());

    // Calculate node position in screen coordinates (accounting for zoom/pan)
    const screenX = nodeY * zoomTransform.k + zoomTransform.x + svgRect.left;
    const screenY = nodeX * zoomTransform.k + zoomTransform.y + svgRect.top;

    // Create popover element
    const popover = jQuery('<div class="genmap-popover"></div>');

    // Build popover content
    const content = buildPopoverContent(nodeData);
    popover.html(content);

    // Append to body for better positioning (relative to viewport)
    jQuery('body').append(popover);

    // Get popover dimensions
    const popoverWidth = popover.outerWidth() || 250;
    const popoverHeight = popover.outerHeight() || 200;

    // Position popover above the node (centered horizontally)
    let left = screenX - popoverWidth / 2;
    let top = screenY - popoverHeight - 15; // 15px above node

    // Ensure popover stays within viewport bounds
    const viewportWidth = jQuery(window).width();
    const viewportHeight = jQuery(window).height();
    const padding = 10;

    // Adjust horizontal position if needed
    if (left < padding) {
      left = padding;
    } else if (left + popoverWidth > viewportWidth - padding) {
      left = viewportWidth - popoverWidth - padding;
    }

    // Adjust vertical position if needed (show below if not enough space above)
    if (top < padding) {
      top = screenY + 50; // Show below node instead
    } else if (top + popoverHeight > viewportHeight - padding) {
      top = viewportHeight - popoverHeight - padding;
    }

    // Set z-index higher than modal (Foundation reveal modals use ~1005)
    const popoverZIndex = isInModal ? 10010 : 1000;

    popover.css({
      left: left + 'px',
      top: top + 'px',
      position: 'fixed', // Use fixed positioning relative to viewport
      zIndex: popoverZIndex, // Ensure popover appears above modal
    });

    // Store popover reference
    wrapper.data('genmapPopover', popover);

    // Bind event handlers
    bindPopoverEvents(wrapper, nodeData, popover);
  }

  /**
   * Build popover HTML content
   * @param {Object} nodeData - D3 node data
   * @returns {string} HTML content
   */
  function buildPopoverContent(nodeData) {
    const data = nodeData.data;
    const strings = window.dtGroupGenmap?.strings || {};
    const detailsStrings = strings.details || {};
    const groupTypes = window.dtGroupGenmap?.groupTypes || {};
    const groupTypeIcons = window.dtGroupGenmap?.groupTypeIcons || {};
    const recordUrlBase = window.dtGroupGenmap?.recordUrlBase || '';
    const postType = window.dtGroupGenmap?.postType || 'groups';

    // Get group type label and icon
    const groupType = data.group_type || '';
    const groupTypeLabel = groupTypes[groupType] || groupType || '';
    const groupTypeIcon = groupTypeIcons[groupType] || '';

    // Get status label (we have status key, but need label - for now use key)
    const status = data.status || '';

    // Build HTML with header containing title and close button
    let html = '<div class="popover-header">';
    html += `<h4>${window.lodash.escape(data.name || '')}</h4>`;
    html += '<button class="popover-close" aria-label="Close">&times;</button>';
    html += '</div>';

    // Group type with icon
    if (groupTypeLabel) {
      html += '<div class="popover-field">';
      html += '<strong>Type:</strong>';
      if (groupTypeIcon) {
        html += `<span><img src="${window.lodash.escape(groupTypeIcon)}" style="width: 14px; height: 14px; vertical-align: middle; margin-right: 4px;" />${window.lodash.escape(groupTypeLabel)}</span>`;
      } else {
        html += `<span>${window.lodash.escape(groupTypeLabel)}</span>`;
      }
      html += '</div>';
    }

    // Status with color indicator
    if (status) {
      const statusColor = data.statusColor || '#3f729b';
      html += '<div class="popover-field">';
      html += '<strong>Status:</strong>';
      html += `<span><span style="display: inline-block; width: 12px; height: 12px; background-color: ${statusColor}; border-radius: 2px; margin-right: 6px; vertical-align: middle;"></span>${window.lodash.escape(status)}</span>`;
      html += '</div>';
    }

    // Generation
    if (data.content) {
      html += '<div class="popover-field">';
      html += '<strong>Generation:</strong>';
      html += `<span>${window.lodash.escape(data.content)}</span>`;
      html += '</div>';
    }

    // Actions
    html += '<div class="popover-actions">';

    // Open button
    const openUrl = `${recordUrlBase}${postType}/${data.id}`;
    html += `<a href="${openUrl}" target="_blank" class="popover-button">${window.lodash.escape(detailsStrings.open || 'Open')}</a>`;

    // Add Child button (only if node has children capability)
    html += `<button class="popover-button secondary genmap-popover-add-child" data-post-type="${postType}" data-post-id="${data.id}" data-post-name="${window.lodash.escape(data.name || '')}">${window.lodash.escape(detailsStrings.add || 'Add')}</button>`;

    // Collapse/Expand button (only if node has children)
    if (
      (nodeData.children && nodeData.children.length > 0) ||
      (nodeData._children && nodeData._children.length > 0)
    ) {
      const isCollapsed = nodeData.data.collapsed || false;
      const collapseText = isCollapsed ? 'Expand' : 'Collapse';
      html += `<button class="popover-button secondary genmap-popover-collapse" data-node-id="${data.id}">${collapseText}</button>`;
    }

    html += '</div>';

    return html;
  }

  /**
   * Bind event handlers for popover
   * @param {jQuery} wrapper - jQuery wrapper element
   * @param {Object} nodeData - D3 node data
   * @param {jQuery} popover - Popover jQuery element
   */
  function bindPopoverEvents(wrapper, nodeData, popover) {
    // Close button - use direct click handler to ensure it works
    popover.find('.popover-close').on('click', function (e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      hideGenmapPopover(wrapper);
      return false;
    });

    // Also bind using event delegation as fallback
    popover.on('click', '.popover-close', function (e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      hideGenmapPopover(wrapper);
      return false;
    });

    // Add Child button
    popover.on('click', '.genmap-popover-add-child', function (e) {
      e.preventDefault();
      e.stopPropagation();
      e.stopImmediatePropagation();
      // Store button data before closing popover
      const button = jQuery(this);
      const postType = button.data('post-type');
      const postId = button.data('post-id');
      const postName = button.data('post-name');
      // Close popover first
      hideGenmapPopover(wrapper);
      // Small delay to ensure popover is removed before modal opens
      setTimeout(function () {
        displayAddChildModal(postType, postId, postName);
      }, 50);
      return false;
    });

    // Collapse/Expand button
    popover.on('click', '.genmap-popover-collapse', function (e) {
      e.preventDefault();
      e.stopPropagation();
      // Use the wrapper passed to this function (works for both modal and regular tile)
      const treeData = wrapper.data('d3Root');
      const linksGroup = wrapper.data('d3LinksGroup');
      const nodesGroup = wrapper.data('d3NodesGroup');
      const svg = wrapper.data('d3Svg');
      const zoomBehavior = wrapper.data('d3Zoom');
      const container = wrapper.find('.group-genmap-chart');
      const containerWidth = container.width() || 800;
      const containerHeight = parseInt(container.css('height')) || 400;

      if (treeData && linksGroup && nodesGroup && svg && zoomBehavior) {
        toggleNodeCollapse(
          nodeData,
          wrapper,
          treeData,
          linksGroup,
          nodesGroup,
          svg,
          zoomBehavior,
          containerWidth,
          containerHeight,
        );
        hideGenmapPopover(wrapper);
      }
    });

    // Close popover when clicking outside (use setTimeout to avoid immediate closure)
    // Store timeout ID so we can clear it if popover is closed via button
    const clickOutsideTimeout = setTimeout(function () {
      jQuery(document).one('click.genmap-popover', function (e) {
        // Don't close if clicking on popover or its children
        if (popover.length && !popover[0].contains(e.target)) {
          // Check if click is not on a node
          const clickedNode = jQuery(e.target).closest('.node');
          if (!clickedNode.length) {
            hideGenmapPopover(wrapper);
          }
        }
      });
    }, 100);

    // Store timeout ID in popover data so we can clear it if needed
    popover.data('clickOutsideTimeout', clickOutsideTimeout);
  }

  /**
   * Hide popover
   * @param {jQuery} wrapper - jQuery wrapper element
   */
  function hideGenmapPopover(wrapper) {
    const popover = wrapper.data('genmapPopover');
    if (popover) {
      // Clear any pending click-outside timeout
      const timeoutId = popover.data('clickOutsideTimeout');
      if (timeoutId) {
        clearTimeout(timeoutId);
        popover.removeData('clickOutsideTimeout');
      }
      // Remove click-outside handler
      jQuery(document).off('click.genmap-popover');
      // Remove popover
      popover.remove();
      wrapper.data('genmapPopover', null);
    }
  }

  /**
   * Toggle node collapse/expand state
   * @param {Object} node - D3 hierarchy node
   * @param {jQuery} wrapper - jQuery wrapper element
   * @param {Object} treeData - Current tree data
   * @param {Object} linksGroup - D3 links group selection
   * @param {Object} nodesGroup - D3 nodes group selection
   * @param {Object} svg - D3 SVG selection
   * @param {Object} zoomBehavior - D3 zoom behavior
   * @param {number} containerWidth - Container width
   * @param {number} containerHeight - Container height
   */
  function toggleNodeCollapse(
    node,
    wrapper,
    treeData,
    linksGroup,
    nodesGroup,
    svg,
    zoomBehavior,
    containerWidth,
    containerHeight,
  ) {
    if (!node.children && !node._children) {
      return; // No children to collapse/expand
    }

    // Toggle collapse state
    if (node.children) {
      // Collapse: move children to _children
      node._children = node.children;
      node.children = null;
      node.data.collapsed = true;
    } else {
      // Expand: restore children from _children
      node.children = node._children;
      node._children = null;
      node.data.collapsed = false;
    }

    // Update tree layout with new structure
    updateD3Tree(
      wrapper,
      treeData,
      linksGroup,
      nodesGroup,
      svg,
      zoomBehavior,
      containerWidth,
      containerHeight,
    );
  }

  /**
   * Add or update collapse indicator icon on a node
   * @param {Object} nodeSelection - D3 selection of node group
   * @param {Object} nodeData - D3 node data
   * @param {jQuery} wrapper - jQuery wrapper element
   * @param {Object} treeData - Current tree data
   * @param {Object} linksGroup - D3 links group selection
   * @param {Object} nodesGroup - D3 nodes group selection
   * @param {Object} svg - D3 SVG selection
   * @param {Object} zoomBehavior - D3 zoom behavior
   * @param {number} containerWidth - Container width
   * @param {number} containerHeight - Container height
   */
  function addCollapseIndicatorIcon(
    nodeSelection,
    nodeData,
    wrapper,
    treeData,
    linksGroup,
    nodesGroup,
    svg,
    zoomBehavior,
    containerWidth,
    containerHeight,
  ) {
    // Check if node has collapsed children
    const hasCollapsedChildren =
      nodeData._children && nodeData._children.length > 0;

    // Get graph orientation for dynamic positioning (pass wrapper for modal context)
    const graphOrientation = getGraphOrientationFromSection(wrapper);

    // Select or create icon group
    let iconGroup = nodeSelection.select('.node-collapse-indicator-group');

    if (hasCollapsedChildren) {
      // Create icon group if it doesn't exist
      if (iconGroup.empty()) {
        iconGroup = nodeSelection
          .append('g')
          .attr('class', 'node-collapse-indicator-group')
          .style('cursor', 'pointer')
          .style('pointer-events', 'all');

        // Determine position based on graph orientation
        // Inverted: l2r (horizontal) = bottom, t2b (vertical) = right
        let bgX, bgY, iconX, iconY;
        if (graphOrientation === 'l2r') {
          // Horizontal mode: icon on bottom center
          // Node is 30px tall (from y=-15 to y=+15), bottom edge at y=15
          // Background is 20px tall, icon is 16px tall
          // Position background to overlap node bottom border (extend below y=15)
          // Then center icon on background
          bgY = 8; // Background: top y=8, bottom y=28 (overlaps node bottom at y=15)
          bgX = -10; // Background centered behind icon
          iconY = 9; // Icon center at y=9 (centered on background)
          iconX = -8; // Horizontally centered (slight left offset for better visual alignment)
        } else {
          // Vertical mode: icon on right side
          bgX = 20; // Background centered behind icon (22 - 2 = 20)
          bgY = -10; // Background centered behind icon (-8 - 2 = -10)
          iconX = 22; // Right side of 60px node (leaving 8px margin from right edge)
          iconY = -8; // Vertically centered in 30px node
        }

        // Add background rectangle
        iconGroup
          .append('rect')
          .attr('x', bgX)
          .attr('y', bgY)
          .attr('width', 20)
          .attr('height', 20)
          .attr('rx', 3)
          .attr('fill', 'rgba(255, 255, 255, 0.95)')
          .attr('stroke', '#ccc')
          .attr('stroke-width', 1)
          .style('pointer-events', 'none'); // Background doesn't capture clicks

        // Get template directory URI from existing group type icons
        const groupTypeIcons = window.dtGroupGenmap?.groupTypeIcons || {};
        const templateDirUri = Object.values(groupTypeIcons)[0]
          ? Object.values(groupTypeIcons)[0].replace(/\/[^/]+\.svg.*$/, '')
          : '';
        const streamIconPath = templateDirUri
          ? `${templateDirUri}/stream.svg`
          : '/wp-content/themes/disciple-tools-theme/dt-assets/images/stream.svg';

        // Determine rotation angle based on graph orientation
        // l2r (horizontal): no rotation (0°)
        // t2b (vertical): rotate 90° anticlockwise
        const rotationAngle = graphOrientation === 'l2r' ? 0 : -90;
        const iconCenterX = iconX + 8; // Center of 16px icon
        const iconCenterY = iconY + 8; // Center of 16px icon

        // Add SVG image element with rotation transform
        const transform =
          rotationAngle !== 0
            ? `rotate(${rotationAngle}, ${iconCenterX}, ${iconCenterY})`
            : '';

        // Add SVG image element with transform
        iconGroup
          .append('image')
          .attr('x', iconX)
          .attr('y', iconY)
          .attr('width', 16)
          .attr('height', 16)
          .attr('href', streamIconPath)
          .attr('transform', transform)
          .style('pointer-events', 'none'); // Icon doesn't capture clicks, group does

        // Add click handler to the group
        iconGroup.on('click', function (event) {
          event.stopPropagation(); // Prevent node click
          toggleNodeCollapse(
            nodeData,
            wrapper,
            treeData,
            linksGroup,
            nodesGroup,
            svg,
            zoomBehavior,
            containerWidth,
            containerHeight,
          );
        });
      } else {
        // Update position and rotation if icon group already exists (for orientation changes)
        const bgRect = iconGroup.select('rect');
        const iconImage = iconGroup.select('image');

        let bgX, bgY, iconX, iconY;
        if (graphOrientation === 'l2r') {
          // Horizontal mode: icon on bottom center
          bgY = 8;
          bgX = -10;
          iconY = 9;
          iconX = -8;
        } else {
          // Vertical mode: icon on right side
          bgX = 20;
          bgY = -10;
          iconX = 22;
          iconY = -8;
        }

        bgRect.attr('x', bgX).attr('y', bgY);
        if (!iconImage.empty()) {
          iconImage.attr('x', iconX).attr('y', iconY);
          // Update rotation based on orientation
          const rotationAngle = graphOrientation === 'l2r' ? 0 : -90;
          const iconCenterX = iconX + 8;
          const iconCenterY = iconY + 8;
          const transform =
            rotationAngle !== 0
              ? `rotate(${rotationAngle}, ${iconCenterX}, ${iconCenterY})`
              : '';
          iconImage.attr('transform', transform);
        }
      }

      // Show icon
      iconGroup.style('display', 'block');
    } else {
      // Hide icon if node doesn't have collapsed children
      if (!iconGroup.empty()) {
        iconGroup.style('display', 'none');
      }
    }
  }

  /**
   * Update D3 tree after collapse/expand
   * @param {jQuery} wrapper - jQuery wrapper element
   * @param {Object} root - D3 hierarchy root node
   * @param {Object} linksGroup - D3 links group selection
   * @param {Object} nodesGroup - D3 nodes group selection
   * @param {Object} svg - D3 SVG selection
   * @param {Object} zoomBehavior - D3 zoom behavior
   * @param {number} containerWidth - Container width
   * @param {number} containerHeight - Container height
   */
  function updateD3Tree(
    wrapper,
    root,
    linksGroup,
    nodesGroup,
    svg,
    zoomBehavior,
    containerWidth,
    containerHeight,
  ) {
    // Determine graph orientation
    // NOTE: currentLayout represents SECTION layout (tile width), which matches graph orientation
    // Section layout 't2b' = half width (xlarge-6) → Graph orientation 't2b' (vertical graph)
    // Section layout 'l2r' = full width (small-12) → Graph orientation 'l2r' (horizontal graph)
    const currentLayout = wrapper.data('currentLayout') || getDefaultLayout();

    // Check if we're in modal context (modal stores graphOrientation separately)
    const isInModal = wrapper.closest('#group-genmap-full-modal').length > 0;

    // Map section layout to graph orientation (they are the SAME, not inverted!)
    // For modal: use stored graphOrientation; otherwise use currentLayout directly
    const graphOrientation = isInModal
      ? wrapper.data('graphOrientation') || currentLayout
      : currentLayout;

    // Recreate tree layout with updated structure and current orientation
    const tree = createD3TreeLayout(100, 80, graphOrientation);
    const treeData = tree(root);

    // Update links based on graph orientation
    const links = treeData.links();
    const NODE_HALF_WIDTH = 30; // 60px / 2
    const NODE_HALF_HEIGHT = 15; // 30px / 2

    // Create link generator with Bézier curves connecting to node edges
    // DEBUG: Set to true to log coordinate calculations to console
    const DEBUG_LINKS = false;

    const linkPath = (link) => {
      // Calculate source and target coordinates directly
      let sourceX, sourceY, targetX, targetY;

      if (graphOrientation === 'l2r') {
        // Horizontal layout: Nodes positioned with translate(d.x, d.y)
        // Node rectangle: 60px wide × 30px tall, centered at (0,0) relative to node group
        // In SVG: node center is at (d.x, d.y)
        // Detect actual node alignment: horizontal (different x) or vertical (different y)
        const deltaX = Math.abs(link.target.x - link.source.x);
        const deltaY = Math.abs(link.target.y - link.source.y);
        const isActuallyHorizontal = deltaX > deltaY;

        if (isActuallyHorizontal) {
          // Nodes are horizontally aligned: connect right edge of parent to left edge of child
          sourceX = link.source.x + NODE_HALF_WIDTH; // Right edge
          sourceY = link.source.y; // Vertical center
          targetX = link.target.x - NODE_HALF_WIDTH; // Left edge
          targetY = link.target.y; // Vertical center
        } else {
          // Nodes are vertically aligned: connect bottom edge of parent to top edge of child
          sourceX = link.source.x; // Horizontal center
          sourceY = link.source.y + NODE_HALF_HEIGHT; // Bottom edge
          targetX = link.target.x; // Horizontal center
          targetY = link.target.y - NODE_HALF_HEIGHT; // Top edge
        }

        // Use straight line instead of Bézier curve for cleaner connections
        // Direct path: M (sourceX, sourceY) L (targetX, targetY)
        const pathData = `M${sourceX},${sourceY}L${targetX},${targetY}`;

        if (DEBUG_LINKS) {
          console.log('l2r link:', {
            link: link, // Full link object for reference
            linkId: `${link.source.data?.id || 'unknown'}-${link.target.data?.id || 'unknown'}`,
            isActuallyHorizontal: isActuallyHorizontal,
            deltaX: deltaX,
            deltaY: deltaY,
            sourceNode: {
              id: link.source.data?.id,
              name: link.source.data?.displayName || link.source.data?.name,
              x: link.source.x,
              y: link.source.y,
            },
            targetNode: {
              id: link.target.data?.id,
              name: link.target.data?.displayName || link.target.data?.name,
              x: link.target.x,
              y: link.target.y,
            },
            sourceEdge: { x: sourceX, y: sourceY },
            targetEdge: { x: targetX, y: targetY },
            pathData: pathData, // Generated path string
          });
        }

        return pathData;
      } else {
        // Vertical layout: Nodes positioned with translate(d.y, d.x)
        // Node rectangle: 60px wide × 30px tall, centered at (0,0) relative to node group
        // In SVG: node center is at (d.y, d.x) - coordinates are swapped!
        // Detect actual node alignment in SVG coordinates
        // In SVG: x = d.y, y = d.x
        const svgSourceX = link.source.y;
        const svgSourceY = link.source.x;
        const svgTargetX = link.target.y;
        const svgTargetY = link.target.x;
        const deltaX = Math.abs(svgTargetX - svgSourceX);
        const deltaY = Math.abs(svgTargetY - svgSourceY);
        const isActuallyHorizontal = deltaX > deltaY;

        if (isActuallyHorizontal) {
          // Nodes are horizontally aligned in SVG: connect right edge of parent to left edge of child
          sourceX = svgSourceX + NODE_HALF_WIDTH; // Right edge
          sourceY = svgSourceY; // Vertical center
          targetX = svgTargetX - NODE_HALF_WIDTH; // Left edge
          targetY = svgTargetY; // Vertical center
        } else {
          // Nodes are vertically aligned in SVG: connect bottom edge of parent to top edge of child
          sourceX = svgSourceX; // Horizontal center
          sourceY = svgSourceY + NODE_HALF_HEIGHT; // Bottom edge
          targetX = svgTargetX; // Horizontal center
          targetY = svgTargetY - NODE_HALF_HEIGHT; // Top edge
        }

        // Use straight line instead of Bézier curve for cleaner connections
        // Direct path: M (sourceX, sourceY) L (targetX, targetY)
        const pathData = `M${sourceX},${sourceY}L${targetX},${targetY}`;

        if (DEBUG_LINKS) {
          console.log('t2b link:', {
            link: link, // Full link object for reference
            linkId: `${link.source.data?.id || 'unknown'}-${link.target.data?.id || 'unknown'}`,
            isActuallyHorizontal: isActuallyHorizontal,
            deltaX: deltaX,
            deltaY: deltaY,
            svgSource: { x: svgSourceX, y: svgSourceY },
            svgTarget: { x: svgTargetX, y: svgTargetY },
            sourceNode: {
              id: link.source.data?.id,
              name: link.source.data?.displayName || link.source.data?.name,
              x: link.source.x,
              y: link.source.y,
              svgX: link.source.y,
              svgY: link.source.x,
            },
            targetNode: {
              id: link.target.data?.id,
              name: link.target.data?.displayName || link.target.data?.name,
              x: link.target.x,
              y: link.target.y,
              svgX: link.target.y,
              svgY: link.target.x,
            },
            sourceEdge: { x: sourceX, y: sourceY },
            targetEdge: { x: targetX, y: targetY },
            nodeTransform: `translate(${link.source.y},${link.source.x}) -> translate(${link.target.y},${link.target.x})`,
            pathData: pathData, // Generated path string
          });
        }

        return pathData;
      }
    };

    // Update existing links and add new ones
    const linkUpdate = linksGroup.selectAll('.link').data(links, (d) => {
      return d.source.data.id + '-' + d.target.data.id;
    });

    // Remove old links
    linkUpdate.exit().remove();

    // Add new links
    const linkEnter = linkUpdate
      .enter()
      .append('path')
      .attr('class', 'link')
      .attr('fill', 'none')
      .attr('stroke', (d) => d.source.data.statusColor || '#999')
      .attr('stroke-width', 2)
      .attr('opacity', 0.6)
      .style('transition', 'opacity 0.2s');

    // Update all links
    linkUpdate.merge(linkEnter).attr('d', linkPath);

    // Update nodes
    const nodes = treeData.descendants();
    const nodeUpdate = nodesGroup
      .selectAll('.node')
      .data(nodes, (d) => d.data.id);

    // Remove old nodes (and their children)
    const nodeExit = nodeUpdate.exit();
    nodeExit.selectAll('*').remove();
    nodeExit.remove();

    // Add new nodes with layout-aware transform
    // (graphOrientation already determined at top of function)
    const nodeEnter = nodeUpdate
      .enter()
      .append('g')
      .attr('class', 'node')
      .attr(
        'transform',
        graphOrientation === 'l2r'
          ? (d) => `translate(${d.x},${d.y})` // Horizontal: normal x/y (left-to-right)
          : (d) => `translate(${d.y},${d.x})`, // Vertical: swap x/y (top-to-bottom)
      );

    // Add rectangle for new nodes
    nodeEnter
      .append('rect')
      .attr('width', 60)
      .attr('height', 30)
      .attr('x', -30)
      .attr('y', -15)
      .attr('rx', 4)
      .attr('fill', (d) => {
        // Ensure statusColor is computed from status property (matching legacy flow)
        if (!d.data.statusColor && d.data.status) {
          const colors = window.dtGroupGenmap?.statusField?.colors || {};
          const archivedKey =
            window.dtGroupGenmap?.statusField?.archived_key || '';

          if (colors[d.data.status]) {
            d.data.statusColor = colors[d.data.status];
          } else if (archivedKey && d.data.status === archivedKey) {
            d.data.statusColor = '#808080';
          } else {
            d.data.statusColor = getStatusColor(d.data.status);
          }
        } else if (!d.data.statusColor) {
          d.data.statusColor = getStatusColor(null);
        }
        return d.data.statusColor || '#3f729b';
      })
      .style('fill', (d) => {
        // Use style() to ensure it overrides CSS - ensure statusColor is set
        if (!d.data.statusColor && d.data.status) {
          const colors = window.dtGroupGenmap?.statusField?.colors || {};
          const archivedKey =
            window.dtGroupGenmap?.statusField?.archived_key || '';

          if (colors[d.data.status]) {
            d.data.statusColor = colors[d.data.status];
          } else if (archivedKey && d.data.status === archivedKey) {
            d.data.statusColor = '#808080';
          } else {
            d.data.statusColor = getStatusColor(d.data.status);
          }
        } else if (!d.data.statusColor) {
          d.data.statusColor = getStatusColor(null);
        }
        return d.data.statusColor || '#3f729b';
      })
      .attr('stroke', (d) => {
        // Ensure statusColor is set
        if (!d.data.statusColor && d.data.status) {
          d.data.statusColor = getStatusColor(d.data.status);
        }
        return d.data.status ===
          (window.dtGroupGenmap?.statusField?.archived_key || 'inactive')
          ? '#666'
          : '#2a4d6b';
      })
      .attr('stroke-width', 1);

    // Note: Group type icons and collapse/expand buttons removed - will be shown in popover (Phase 5)

    // Add text group for new nodes
    const textGroupEnter = nodeEnter
      .append('g')
      .attr('class', 'node-text-group')
      .attr('clip-path', 'url(#node-text-clip)');

    textGroupEnter
      .append('text')
      .attr('x', 0)
      .attr('y', 0)
      .attr('text-anchor', 'middle')
      .attr('dominant-baseline', 'middle')
      .attr('font-size', '10px')
      .attr('font-weight', '500')
      .attr('fill', '#333')
      .attr('class', 'node-title')
      .text((d) => {
        const name = d.data.displayName || '';
        return name.length > 10 ? name.substring(0, 7) + '...' : name;
      });

    // Add collapse indicator icon for new nodes with collapsed children
    nodeEnter.each(function (d) {
      addCollapseIndicatorIcon(
        d3.select(this),
        d,
        wrapper,
        treeData,
        linksGroup,
        nodesGroup,
        svg,
        zoomBehavior,
        containerWidth,
        containerHeight,
      );
    });

    // Update existing nodes (position and collapse indicator)
    const nodeUpdateMerged = nodeUpdate.merge(nodeEnter);

    // Update node positions with transition (layout-aware)
    // Use graph orientation from section classes (already determined above)
    nodeUpdateMerged
      .transition()
      .duration(300)
      .attr(
        'transform',
        graphOrientation === 'l2r'
          ? (d) => `translate(${d.x},${d.y})` // Horizontal: normal x/y (left-to-right)
          : (d) => `translate(${d.y},${d.x})`, // Vertical: swap x/y (top-to-bottom)
      );

    // Update collapse indicator icon visibility for existing nodes
    nodeUpdateMerged.each(function (d) {
      addCollapseIndicatorIcon(
        d3.select(this),
        d,
        wrapper,
        treeData,
        linksGroup,
        nodesGroup,
        svg,
        zoomBehavior,
        containerWidth,
        containerHeight,
      );
    });

    // Update link positions with transition
    linkUpdate.merge(linkEnter).transition().duration(300).attr('d', linkPath);

    // Re-bind click handlers for node selection and popover
    nodeUpdateMerged.on('click', function (event, d) {
      if (d.data.isNonShared) {
        return;
      }

      nodesGroup.selectAll('.node').classed('selected', false);
      d3.select(this).classed('selected', true);
      wrapper.data('selectedNode', d);

      // Show popover
      showGenmapPopover(wrapper, d, event);
    });

    // Store updated tree data
    wrapper.data('d3Root', treeData);
  }

  /**
   * Setup SVG container for D3 visualization
   * @param {jQuery} container - jQuery element for the chart container
   * @param {string} layout - Current layout ('t2b' for vertical/smaller tile, 'l2r' for horizontal/larger tile)
   * @returns {Object} - Object with svg, zoomContainer, linksGroup, nodesGroup
   */
  function setupSVGContainer(container, layout = 'l2r') {
    // Check if container is in modal (fullscreen context)
    const isInModal = container.closest('#group-genmap-full-modal').length > 0;

    // Determine height based on context
    let baseHeight;
    if (isInModal) {
      // In modal: use full available height (modal content is calc(100vh - 180px))
      baseHeight = window.innerHeight - 180;
    } else {
      // In tile: use layout-based height
      // l2r (horizontal graph): smaller height (needs more horizontal space, less vertical)
      // t2b (vertical graph): larger height (needs more vertical space)
      baseHeight = layout === 't2b' ? 600 : 400;
    }

    // Ensure container has proper styling
    container.css({
      width: '100%',
      minHeight: baseHeight + 'px',
      height: isInModal ? '100%' : baseHeight + 'px',
      position: 'relative',
      overflow: 'hidden',
      background: '#f9f9f9',
    });

    // Get container dimensions - use parent width if container doesn't have explicit width
    const parentWidth = container.parent().width() || window.innerWidth;
    const containerWidth = container.width() || parentWidth;
    const containerHeight = isInModal
      ? container.height() || baseHeight
      : baseHeight;

    // Clear container
    container.empty();
    container.addClass('group-genmap-chart--d3');

    // Create SVG element
    const svg = d3
      .select(container[0])
      .append('svg')
      .attr('width', containerWidth)
      .attr('height', containerHeight)
      .attr('class', 'genmap-svg')
      .style('display', 'block')
      .style('cursor', 'move');

    // Create zoom container (g element that will be transformed)
    const zoomContainer = svg.append('g').attr('class', 'zoom-container');

    // Create groups for links and nodes (order matters - links should be behind nodes)
    const linksGroup = zoomContainer.append('g').attr('class', 'links');
    const nodesGroup = zoomContainer.append('g').attr('class', 'nodes');

    return {
      svg,
      zoomContainer,
      linksGroup,
      nodesGroup,
      containerWidth,
      containerHeight,
    };
  }

  /**
   * Setup zoom and pan functionality
   * @param {Object} svg - D3 SVG selection
   * @param {Object} zoomContainer - D3 zoom container selection
   * @param {Function} onZoom - Callback function when zoom/pan occurs
   * @returns {Object} - D3 zoom behavior
   */
  function setupZoom(svg, zoomContainer, onZoom) {
    const zoom = d3
      .zoom()
      .scaleExtent([0.1, 3]) // Min zoom: 10%, Max zoom: 300%
      .filter((event) => {
        // Allow zoom/pan on mouse wheel, drag, and touch
        // Prevent zoom on double-click
        if (event.type === 'dblclick') {
          return false;
        }
        // Allow all other interactions
        return true;
      })
      .on('zoom', (event) => {
        // Apply transform to zoom container
        zoomContainer.attr('transform', event.transform);

        // Close popover on zoom/pan
        const wrapper = jQuery(TILE_SELECTOR);
        if (wrapper.length) {
          hideGenmapPopover(wrapper);
        }

        if (onZoom) {
          onZoom(event);
        }
      });

    // Apply zoom behavior to SVG
    // D3-zoom automatically handles:
    // - Mouse wheel for zoom
    // - Mouse drag for pan
    // - Touch pinch for zoom (mobile)
    // - Touch drag for pan (mobile)
    svg.call(zoom);

    return zoom;
  }

  /**
   * Auto-fit tree to viewport
   * @param {Object} svg - D3 SVG selection
   * @param {Object} zoom - D3 zoom behavior
   * @param {Object} root - D3 hierarchy root node
   * @param {number} containerWidth - Container width
   * @param {number} containerHeight - Container height
   * @param {number} nodeWidth - Horizontal spacing between nodes
   * @param {number} nodeHeight - Vertical spacing between nodes
   */
  function fitToViewport(
    svg,
    zoom,
    root,
    containerWidth,
    containerHeight,
    nodeWidth = 100,
    nodeHeight = 80,
    layout = 't2b',
  ) {
    if (!root || !root.descendants().length) {
      return;
    }

    // Calculate tree bounds
    let minX = Infinity;
    let maxX = -Infinity;
    let minY = Infinity;
    let maxY = -Infinity;

    root.descendants().forEach((d) => {
      if (d.x < minX) minX = d.x;
      if (d.x > maxX) maxX = d.x;
      if (d.y < minY) minY = d.y;
      if (d.y > maxY) maxY = d.y;
    });

    // Calculate tree dimensions based on layout orientation
    // For vertical (t2b): tree uses swapped coordinates (x=vertical, y=horizontal)
    // For horizontal (l2r): tree uses normal coordinates (x=horizontal, y=vertical)
    const treeWidth = layout === 't2b' ? maxY - minY : maxX - minX; // Horizontal extent
    const treeHeight = layout === 't2b' ? maxX - minX : maxY - minY; // Vertical extent

    // Add padding
    const padding = 40;
    const targetWidth = containerWidth - padding * 2;
    const targetHeight = containerHeight - padding * 2;

    // Calculate scale to fit
    const scaleX = targetWidth / treeWidth;
    const scaleY = targetHeight / treeHeight;
    const scale = Math.min(scaleX, scaleY, 1); // Don't zoom in beyond 100%

    // Calculate translation to center based on layout orientation
    let translateX, translateY;
    if (layout === 't2b') {
      // Vertical layout: coordinates are swapped (x=vertical, y=horizontal)
      translateX = (containerWidth - treeWidth * scale) / 2 - minY * scale;
      translateY = padding - minX * scale;
    } else {
      // Horizontal layout: coordinates are normal (x=horizontal, y=vertical)
      translateX = (containerWidth - treeWidth * scale) / 2 - minX * scale;
      translateY = padding - minY * scale;
    }

    // Apply transform
    svg.call(
      zoom.transform,
      d3.zoomIdentity.translate(translateX, translateY).scale(scale),
    );
  }

  /**
   * Create D3 tree layout
   * @param {number} nodeWidth - Horizontal spacing between nodes (default: 100)
   * @param {number} nodeHeight - Vertical spacing between nodes (default: 80)
   * @param {string} layout - Layout orientation: 't2b' (vertical) or 'l2r' (horizontal)
   * @returns {Object} - D3 tree layout
   */
  function createD3TreeLayout(
    nodeWidth = 100,
    nodeHeight = 80,
    layout = 't2b',
  ) {
    const tree = d3
      .tree()
      .nodeSize(
        layout === 't2b'
          ? [nodeHeight, nodeWidth] // [height, width] for vertical layout (top-to-bottom)
          : [nodeWidth, nodeHeight], // [width, height] for horizontal layout (left-to-right)
      )
      .separation((a, b) => {
        // Separation function: siblings get 1, different parents get more space
        return a.parent === b.parent ? 1 : 1.2;
      });

    return tree;
  }

  /**
   * Render D3 tree visualization
   * @param {jQuery} wrapper - jQuery wrapper element
   * @param {Object} genmapData - Genmap data from API
   * @param {string} layout - Layout orientation: 't2b' (vertical) or 'l2r' (horizontal)
   */
  function renderD3Chart(wrapper, genmapData, layout = null) {
    // Check if D3.js is available
    if (typeof d3 === 'undefined') {
      console.error('D3.js is not loaded. Cannot render D3 chart.');
      setMessage(wrapper, 'error');
      return;
    }

    if (!genmapData || typeof genmapData !== 'object') {
      setMessage(wrapper, 'empty');
      return;
    }

    // Determine graph orientation
    // Store currentLayout for toggle button and other UI elements
    const currentLayout =
      layout || wrapper.data('currentLayout') || getDefaultLayout();
    wrapper.data('currentLayout', currentLayout);

    // Check if we're in modal context (modal stores graphOrientation separately)
    const isInModal = wrapper.closest('#group-genmap-full-modal').length > 0;

    // Determine graph orientation from actual section CSS classes (source of truth)
    // For modal: use stored graphOrientation; otherwise check section classes
    let graphOrientation;
    if (isInModal) {
      graphOrientation =
        wrapper.data('graphOrientation') ||
        getGraphOrientationFromSection(wrapper);
    } else {
      // Use section classes to determine actual graph orientation
      const genmapSection = jQuery('#genmap');
      const sectionClasses = genmapSection.attr('class') || '';
      const hasSmall12 = genmapSection.hasClass('small-12');
      const hasXlarge6 = genmapSection.hasClass('xlarge-6');
      const hasLarge12 = genmapSection.hasClass('large-12');
      const hasMedium6 = genmapSection.hasClass('medium-6');

      graphOrientation = getGraphOrientationFromSection(wrapper);
    }

    // DEBUG: Uncomment to log orientation detection
    // console.log('renderD3Chart orientation:', { currentLayout, graphOrientation, sectionClasses: jQuery('#genmap').attr('class') });

    // Transform data to D3 hierarchy
    const root = transformToD3Hierarchy(genmapData);
    if (!root) {
      setMessage(wrapper, 'error');
      return;
    }

    // Get container
    const container = wrapper.find('.group-genmap-chart');
    if (!container.length) {
      setMessage(wrapper, 'error');
      return;
    }

    // Setup SVG container with layout-aware height
    const {
      svg,
      zoomContainer,
      linksGroup,
      nodesGroup,
      containerWidth,
      containerHeight,
    } = setupSVGContainer(container, graphOrientation);

    // Create tree layout with orientation based on section classes
    const tree = createD3TreeLayout(100, 80, graphOrientation); // 100px horizontal, 80px vertical spacing
    const treeData = tree(root);

    // Setup zoom and pan
    const zoomBehavior = setupZoom(svg, zoomContainer);

    // Add clipping path for text overflow protection (create once per SVG)
    const defs = svg.append('defs');
    defs
      .append('clipPath')
      .attr('id', 'node-text-clip')
      .append('rect')
      .attr('x', -28) // Leave 2px padding on each side (60px width - 4px = 56px)
      .attr('y', -13) // Leave 2px padding on top/bottom (30px height - 4px = 26px)
      .attr('width', 56)
      .attr('height', 26);

    // Store references for later use (collapse/expand, popover, etc.)
    wrapper.data('d3Svg', svg);
    wrapper.data('d3Zoom', zoomBehavior);
    wrapper.data('d3Root', treeData);
    wrapper.data('d3LinksGroup', linksGroup);
    wrapper.data('d3NodesGroup', nodesGroup);
    wrapper.data('d3ZoomContainer', zoomContainer);

    // Render links (edges) connecting to center of side facing parent
    const links = treeData.links();
    const NODE_HALF_WIDTH = 30; // 60px / 2
    const NODE_HALF_HEIGHT = 15; // 30px / 2

    // Create link generator with Bézier curves connecting to node edges
    // DEBUG: Set to true to log coordinate calculations to console
    const DEBUG_LINKS = false;

    const linkPath = (link) => {
      // Calculate source and target coordinates directly
      let sourceX, sourceY, targetX, targetY;

      if (graphOrientation === 'l2r') {
        // Horizontal layout: Nodes positioned with translate(d.x, d.y)
        // Node rectangle: 60px wide × 30px tall, centered at (0,0) relative to node group
        // In SVG: node center is at (d.x, d.y)
        // Detect actual node alignment: horizontal (different x) or vertical (different y)
        const deltaX = Math.abs(link.target.x - link.source.x);
        const deltaY = Math.abs(link.target.y - link.source.y);
        const isActuallyHorizontal = deltaX > deltaY;

        if (isActuallyHorizontal) {
          // Nodes are horizontally aligned: connect right edge of parent to left edge of child
          sourceX = link.source.x + NODE_HALF_WIDTH; // Right edge
          sourceY = link.source.y; // Vertical center
          targetX = link.target.x - NODE_HALF_WIDTH; // Left edge
          targetY = link.target.y; // Vertical center
        } else {
          // Nodes are vertically aligned: connect bottom edge of parent to top edge of child
          sourceX = link.source.x; // Horizontal center
          sourceY = link.source.y + NODE_HALF_HEIGHT; // Bottom edge
          targetX = link.target.x; // Horizontal center
          targetY = link.target.y - NODE_HALF_HEIGHT; // Top edge
        }

        // Use straight line instead of Bézier curve for cleaner connections
        // Direct path: M (sourceX, sourceY) L (targetX, targetY)
        const pathData = `M${sourceX},${sourceY}L${targetX},${targetY}`;

        if (DEBUG_LINKS) {
          console.log('l2r link:', {
            link: link, // Full link object for reference
            linkId: `${link.source.data?.id || 'unknown'}-${link.target.data?.id || 'unknown'}`,
            isActuallyHorizontal: isActuallyHorizontal,
            deltaX: deltaX,
            deltaY: deltaY,
            sourceNode: {
              id: link.source.data?.id,
              name: link.source.data?.displayName || link.source.data?.name,
              x: link.source.x,
              y: link.source.y,
            },
            targetNode: {
              id: link.target.data?.id,
              name: link.target.data?.displayName || link.target.data?.name,
              x: link.target.x,
              y: link.target.y,
            },
            sourceEdge: { x: sourceX, y: sourceY },
            targetEdge: { x: targetX, y: targetY },
            pathData: pathData, // Generated path string
          });
        }

        return pathData;
      } else {
        // Vertical layout: Nodes positioned with translate(d.y, d.x)
        // Node rectangle: 60px wide × 30px tall, centered at (0,0) relative to node group
        // In SVG: node center is at (d.y, d.x) - coordinates are swapped!
        // Detect actual node alignment in SVG coordinates
        // In SVG: x = d.y, y = d.x
        const svgSourceX = link.source.y;
        const svgSourceY = link.source.x;
        const svgTargetX = link.target.y;
        const svgTargetY = link.target.x;
        const deltaX = Math.abs(svgTargetX - svgSourceX);
        const deltaY = Math.abs(svgTargetY - svgSourceY);
        const isActuallyHorizontal = deltaX > deltaY;

        if (isActuallyHorizontal) {
          // Nodes are horizontally aligned in SVG: connect right edge of parent to left edge of child
          sourceX = svgSourceX + NODE_HALF_WIDTH; // Right edge
          sourceY = svgSourceY; // Vertical center
          targetX = svgTargetX - NODE_HALF_WIDTH; // Left edge
          targetY = svgTargetY; // Vertical center
        } else {
          // Nodes are vertically aligned in SVG: connect bottom edge of parent to top edge of child
          sourceX = svgSourceX; // Horizontal center
          sourceY = svgSourceY + NODE_HALF_HEIGHT; // Bottom edge
          targetX = svgTargetX; // Horizontal center
          targetY = svgTargetY - NODE_HALF_HEIGHT; // Top edge
        }

        // Use straight line instead of Bézier curve for cleaner connections
        // Direct path: M (sourceX, sourceY) L (targetX, targetY)
        const pathData = `M${sourceX},${sourceY}L${targetX},${targetY}`;

        if (DEBUG_LINKS) {
          console.log('t2b link:', {
            link: link, // Full link object for reference
            linkId: `${link.source.data?.id || 'unknown'}-${link.target.data?.id || 'unknown'}`,
            isActuallyHorizontal: isActuallyHorizontal,
            deltaX: deltaX,
            deltaY: deltaY,
            svgSource: { x: svgSourceX, y: svgSourceY },
            svgTarget: { x: svgTargetX, y: svgTargetY },
            sourceNode: {
              id: link.source.data?.id,
              name: link.source.data?.displayName || link.source.data?.name,
              x: link.source.x,
              y: link.source.y,
              svgX: link.source.y,
              svgY: link.source.x,
            },
            targetNode: {
              id: link.target.data?.id,
              name: link.target.data?.displayName || link.target.data?.name,
              x: link.target.x,
              y: link.target.y,
              svgX: link.target.y,
              svgY: link.target.x,
            },
            sourceEdge: { x: sourceX, y: sourceY },
            targetEdge: { x: targetX, y: targetY },
            nodeTransform: `translate(${link.source.y},${link.source.x}) -> translate(${link.target.y},${link.target.x})`,
            pathData: pathData, // Generated path string
          });
        }

        return pathData;
      }
    };

    // Render links with enhanced styling
    linksGroup
      .selectAll('.link')
      .data(links)
      .enter()
      .append('path')
      .attr('class', 'link')
      .attr('d', linkPath)
      .attr('fill', 'none')
      .attr('stroke', (d) => {
        // Use parent node's status color for link, or default gray
        return d.source.data.statusColor || '#999';
      })
      .attr('stroke-width', 2)
      .attr('opacity', 0.6)
      .style('transition', 'opacity 0.2s');

    // Render nodes - Phase 4 will implement full node rendering
    // For now, we'll create basic nodes
    const nodes = treeData.descendants();
    const nodeGroup = nodesGroup
      .selectAll('.node')
      .data(nodes, (d) => d.data.id)
      .enter()
      .append('g')
      .attr('class', 'node')
      .attr(
        'transform',
        graphOrientation === 'l2r'
          ? (d) => `translate(${d.x},${d.y})` // Horizontal: normal x/y (left-to-right)
          : (d) => `translate(${d.y},${d.x})`, // Vertical: swap x/y (top-to-bottom)
      );

    // Add node rectangle with status color
    nodeGroup
      .append('rect')
      .attr('width', 60)
      .attr('height', 30)
      .attr('x', -30) // Center horizontally
      .attr('y', -15) // Center vertically
      .attr('rx', 4)
      .attr('fill', (d) => {
        // statusColor should already be set by sanitizeNode, but ensure it's computed if missing
        // This is a safety check - statusColor should be set during data sanitization
        if (!d.data.statusColor) {
          if (d.data.status) {
            const colors = window.dtGroupGenmap?.statusField?.colors || {};
            const archivedKey =
              window.dtGroupGenmap?.statusField?.archived_key || '';

            if (colors[d.data.status]) {
              d.data.statusColor = colors[d.data.status];
            } else if (archivedKey && d.data.status === archivedKey) {
              d.data.statusColor = '#808080';
            } else {
              d.data.statusColor = getStatusColor(d.data.status);
            }
          } else {
            d.data.statusColor = getStatusColor(null);
          }
        }

        // Use computed status color, fallback to default blue
        return d.data.statusColor || '#3f729b';
      })
      .style('fill', (d) => {
        // Use style() instead of attr() to ensure it overrides CSS
        // statusColor should already be set, but ensure it's computed if missing
        if (!d.data.statusColor && d.data.status) {
          const colors = window.dtGroupGenmap?.statusField?.colors || {};
          const archivedKey =
            window.dtGroupGenmap?.statusField?.archived_key || '';

          if (colors[d.data.status]) {
            d.data.statusColor = colors[d.data.status];
          } else if (archivedKey && d.data.status === archivedKey) {
            d.data.statusColor = '#808080';
          } else {
            d.data.statusColor = getStatusColor(d.data.status);
          }
        }
        return d.data.statusColor || '#3f729b';
      })
      .attr('stroke', (d) => {
        // Ensure statusColor is set
        if (!d.data.statusColor && d.data.status) {
          d.data.statusColor = getStatusColor(d.data.status);
        }
        const color = d.data.statusColor || '#3f729b';
        // Lighten stroke for archived/inactive
        return d.data.status ===
          (window.dtGroupGenmap?.statusField?.archived_key || 'inactive')
          ? '#666'
          : '#2a4d6b';
      })
      .attr('stroke-width', 1);

    // Note: Group type icons and collapse/expand buttons removed - will be shown in popover (Phase 5)

    // Add node text container with clipping for proper text truncation
    const textGroup = nodeGroup
      .append('g')
      .attr('class', 'node-text-group')
      .attr('clip-path', 'url(#node-text-clip)');

    // Add node title (ellipsized name) - centered vertically
    textGroup
      .append('text')
      .attr('x', 0)
      .attr('y', 0)
      .attr('text-anchor', 'middle')
      .attr('dominant-baseline', 'middle')
      .attr('font-size', '10px') // Slightly smaller to ensure fit
      .attr('font-weight', '500')
      .attr('fill', '#333')
      .attr('class', 'node-title')
      .text((d) => {
        // Use displayName which is already ellipsized, but ensure it fits with padding
        const name = d.data.displayName || '';
        // Truncate to 10 chars max to ensure fit with 2px padding on each side
        if (name.length > 10) {
          return name.substring(0, 7) + '...';
        }
        return name;
      });

    // Add collapse indicator icon for nodes with collapsed children
    nodeGroup.each(function (d) {
      addCollapseIndicatorIcon(
        d3.select(this),
        d,
        wrapper,
        treeData,
        linksGroup,
        nodesGroup,
        svg,
        zoomBehavior,
        containerWidth,
        containerHeight,
      );
    });

    // Add click handler for node selection and popover display
    nodeGroup.on('click', function (event, d) {
      // Prevent clicks on non-shared nodes
      if (d.data.isNonShared) {
        return;
      }

      // Regular node click for selection/popover
      // Remove previous selection
      nodesGroup.selectAll('.node').classed('selected', false);

      // Add selection to current node
      d3.select(this).classed('selected', true);

      // Store selected node for popover
      wrapper.data('selectedNode', d);

      // Show popover
      showGenmapPopover(wrapper, d, event);
    });

    // Auto-fit to viewport
    fitToViewport(
      svg,
      zoomBehavior,
      treeData,
      containerWidth,
      containerHeight,
      100,
      80,
      graphOrientation,
    );

    // Show layout toggle button and update icon based on current layout
    const layoutToggle = $('#group-genmap-layout-toggle');
    if (layoutToggle.length && !isMobileView()) {
      layoutToggle.show();
      const currentLayout = wrapper.data('currentLayout') || getDefaultLayout();
      updateLayoutToggleIcon(currentLayout);
      // Ensure section width matches layout
      updateSectionWidth(currentLayout);
    }

    // Show maximize button on desktop
    const maximizeButton = $('#group-genmap-maximize');
    if (maximizeButton.length && !isMobileView()) {
      maximizeButton.show();
    }

    // Mark as ready
    container.addClass('group-genmap-chart--ready');
    setMessage(wrapper, 'ready');
  }

  function setMessage(wrapper, state) {
    const messageEl = wrapper.find('.group-genmap-message');
    if (!messageEl.length) {
      return;
    }

    switch (state) {
      case 'loading':
        messageEl.text(getString('loading')).show();
        break;
      case 'error':
        messageEl.text(getString('error')).show();
        break;
      case 'empty':
        messageEl.text(getString('empty')).show();
        break;
      case 'ready':
      default:
        messageEl.text('').hide();
        break;
    }
  }

  function getString(key) {
    return window.dtGroupGenmap?.strings?.[key] || '';
  }

  function switchLayout(wrapper, newLayout) {
    const currentGenmap = wrapper.data('currentGenmapData');
    if (!currentGenmap) {
      return;
    }

    wrapper.data('currentLayout', newLayout);
    saveLayoutPreference(newLayout);
    updateLayoutToggleIcon(newLayout);
    updateSectionWidth(newLayout);

    // Re-render chart with new layout
    renderChart(wrapper, currentGenmap, newLayout);
  }

  function updateSectionWidth(layout) {
    const genmapSection = jQuery('#genmap');
    if (!genmapSection.length) {
      // Retry after a short delay if section doesn't exist yet
      setTimeout(() => {
        updateSectionWidth(layout);
      }, 200);
      return;
    }

    // Remove existing width classes (but keep custom-tile-section, cell, grid-item)
    genmapSection.removeClass('small-12 xlarge-6 large-12 medium-6');

    if (layout === 't2b') {
      // Vertical mode: Use same width as Member List/Church Health
      genmapSection.addClass('xlarge-6 large-12 medium-6');
    } else {
      // Horizontal mode: Full width (spans entire grid row)
      genmapSection.addClass('small-12');
    }

    // Wait for browser to apply CSS changes, then recalculate Masonry grid
    requestAnimationFrame(() => {
      requestAnimationFrame(() => {
        // Double RAF ensures browser has painted the changes
        recalculateMasonryGrid();
      });
    });
  }

  function recalculateMasonryGrid() {
    // Use the global Masonry grid instance if available, otherwise fallback to selector
    if (window.masonGrid && typeof window.masonGrid.masonry === 'function') {
      // Force Masonry to recalculate layout
      window.masonGrid.masonry('layout');
    } else {
      // Fallback: get the Masonry grid instance
      const grid = jQuery('.grid');
      if (grid.length && typeof grid.masonry === 'function') {
        grid.masonry('layout');
      }
    }
  }

  function updateLayoutToggleIcon(layout) {
    const toggle = $('#group-genmap-layout-toggle');
    if (!toggle.length) {
      return;
    }
    const switchToVerticalIcon = toggle.find(
      '.group-genmap-layout-icon-switch-to-vertical',
    );
    const switchToHorizontalIcon = toggle.find(
      '.group-genmap-layout-icon-switch-to-horizontal',
    );

    // Show icon for the layout we'll switch TO, not the current layout
    if (layout === 'l2r') {
      // Currently horizontal, show icon to switch to vertical
      switchToVerticalIcon.show();
      switchToHorizontalIcon.hide();
    } else {
      // Currently vertical (t2b), show icon to switch to horizontal
      switchToVerticalIcon.hide();
      switchToHorizontalIcon.show();
    }
  }

  /**
   * Render genmap in modal container
   * @param {jQuery} modalWrapper - jQuery wrapper for modal genmap container
   * @param {Object} genmapData - Genmap data from API
   * @param {string} layout - Layout orientation: 't2b' (vertical) or 'l2r' (horizontal)
   * @param {string} graphOrientation - Graph orientation from section classes
   */
  function renderGenmapInModal(
    modalWrapper,
    genmapData,
    layout,
    graphOrientation,
  ) {
    // Check if D3 visualization should be used
    const useD3Visualization = typeof d3 !== 'undefined';
    modalWrapper.data('useD3Visualization', useD3Visualization);
    modalWrapper.data('currentLayout', layout);
    modalWrapper.data('currentGenmapData', genmapData);
    // Store graph orientation for modal (since there's no section to check)
    modalWrapper.data('graphOrientation', graphOrientation);

    if (useD3Visualization) {
      renderD3Chart(modalWrapper, genmapData, layout);
    } else {
      renderChart(modalWrapper, genmapData, layout);
    }
  }

  /**
   * Open genmap in fullscreen modal
   */
  function openGenmapFullModal() {
    const wrapper = jQuery(TILE_SELECTOR);
    if (!wrapper.length) {
      return;
    }

    // Get current genmap data and layout
    const genmapData = wrapper.data('currentGenmapData');
    if (!genmapData) {
      console.warn('No genmap data available to display in modal');
      return;
    }

    const currentLayout = wrapper.data('currentLayout') || getDefaultLayout();
    // Map section layout to graph orientation (they are the SAME!)
    // Section layout 't2b' = half width (xlarge-6) → Graph orientation 't2b' (vertical graph)
    // Section layout 'l2r' = full width (small-12) → Graph orientation 'l2r' (horizontal graph)
    const graphOrientation = currentLayout;

    // Get modal and content container
    const modal = jQuery('#group-genmap-full-modal');
    const modalContent = jQuery('#group-genmap-full-modal-content');
    if (!modal.length || !modalContent.length) {
      console.error('Genmap full modal not found');
      return;
    }

    // Close any open popovers from the original genmap
    hideGenmapPopover(wrapper);

    // Create modal genmap container structure
    const modalGenmapHtml = `
      <div id="group-genmap-tile-modal" class="group-genmap-tile" 
           data-post-id="${wrapper.data('postId') || ''}" 
           data-post-type="${wrapper.data('postType') || 'groups'}"
           style="width: 100%; height: 100%;">
        <div class="group-genmap-message" aria-live="polite" style="display: none;"></div>
        <div class="group-genmap-chart" role="region"
             aria-label="${window.dtGroupGenmap?.strings?.loading || 'Group generational map'}"
             style="width: 100%; height: 100%;"></div>
      </div>
    `;

    // Clear and set modal content
    modalContent.empty().html(modalGenmapHtml);

    // Create wrapper for modal genmap
    const modalWrapper = jQuery('#group-genmap-tile-modal');
    if (!modalWrapper.length) {
      console.error('Failed to create modal genmap container');
      return;
    }

    // Open Foundation reveal modal first
    modal.foundation('open');

    // Wait for modal to be fully opened before rendering (Foundation animation)
    setTimeout(() => {
      // Render genmap in modal after modal is opened
      renderGenmapInModal(
        modalWrapper,
        genmapData,
        currentLayout,
        graphOrientation,
      );
    }, 300);

    // Handle modal close - cleanup
    modal
      .off('closed.zf.reveal.genmap')
      .on('closed.zf.reveal.genmap', function () {
        // Clean up modal genmap
        const modalGenmap = jQuery('#group-genmap-tile-modal');
        if (modalGenmap.length) {
          // Remove D3 references
          const svg = modalGenmap.data('d3Svg');
          if (svg && svg.node()) {
            svg.remove();
          }
          // Clear data
          modalGenmap.removeData();
          // Remove container
          modalGenmap.remove();
        }
        // Clear modal content
        modalContent.empty();
      });
  }

  function openGenmapDetails(wrapper, nodeId, parentId, postType) {
    const modal = $('#group-genmap-details-modal');
    if (!modal.length) {
      return;
    }

    const spinner = ' <span class="loading-spinner active"></span> ';
    $('#group-genmap-details-modal-content').html(spinner);
    modal.foundation('open');

    const request = window.makeRequest
      ? window.makeRequest('GET', postType + '/' + nodeId, null, 'dt-posts/v2/')
      : $.ajax({
          type: 'GET',
          url: `${window.wpApiShare?.root || ''}dt-posts/v2/${postType}/${nodeId}`,
          beforeSend: (xhr) => {
            if (window.wpApiShare?.nonce) {
              xhr.setRequestHeader('X-WP-Nonce', window.wpApiShare.nonce);
            }
          },
        });

    const deferred =
      typeof request.promise === 'function' ? request.promise() : request;

    deferred
      .then((data) => {
        if (data) {
          renderGenmapDetails(parentId, postType, data);
        } else {
          $('#group-genmap-details-modal-content').html(
            '<p>' + getString('error') + '</p>',
          );
        }
      })
      .fail(() => {
        $('#group-genmap-details-modal-content').html(
          '<p>' + getString('error') + '</p>',
        );
      });
  }

  function renderGenmapDetails(parentId, postType, data) {
    const strings = window.dtGroupGenmap?.strings?.details || {};
    const urlBase = (window.dtGroupGenmap?.recordUrlBase || '').replace(
      /\/$/,
      '',
    );
    const postTypeSlug = window.dtGroupGenmap?.postType || 'groups';
    const modalContent = $('#group-genmap-details-modal-content');
    const modalTitle = $('#group-genmap-details-modal-title');

    // Set modal title
    modalTitle.html(window.lodash.escape(data.title || ''));

    // Build details content
    let detailsHtml = '<div class="grid-x grid-padding-x">';
    detailsHtml += '<div class="cell">';

    if (data.group_status && data.group_status.label) {
      detailsHtml +=
        '<p><strong>Status:</strong> ' +
        window.lodash.escape(data.group_status.label) +
        '</p>';
    }
    if (data.group_type && data.group_type.label) {
      detailsHtml +=
        '<p><strong>Type:</strong> ' +
        window.lodash.escape(data.group_type.label) +
        '</p>';
    }
    if (data.member_count !== undefined) {
      detailsHtml +=
        '<p><strong>Members:</strong> ' +
        window.lodash.escape(data.member_count) +
        '</p>';
    }
    if (data.assigned_to && data.assigned_to.display) {
      detailsHtml +=
        '<p><strong>Assigned:</strong> ' +
        window.lodash.escape(data.assigned_to.display) +
        '</p>';
    }

    detailsHtml += '</div></div>';

    // Build buttons line
    const openUrl = `${urlBase}/${postTypeSlug}/${encodeURIComponent(data.ID)}`;
    detailsHtml += '<hr>';
    detailsHtml += '<div style="display: flex; gap: 10px; padding: 10px 0;">';
    detailsHtml +=
      '<a href="' +
      openUrl +
      '" target="_blank" class="button genmap-details-open" data-post_id="' +
      window.lodash.escape(data.ID) +
      '" data-post_type="' +
      window.lodash.escape(postType) +
      '">';
    detailsHtml += '<i class="mdi mdi-id-card" style="font-size: 20px;"></i>';
    detailsHtml +=
      '<span style="display: flex">' +
      window.lodash.escape(strings.open || 'Open') +
      '</span>';
    detailsHtml += '</a>';

    detailsHtml +=
      '<a href="#" class="button genmap-details-add-child" data-post_type="' +
      window.lodash.escape(postType) +
      '" data-post_id="' +
      window.lodash.escape(data.ID) +
      '" data-post_name="' +
      window.lodash.escape(data.title || '') +
      '">';
    detailsHtml +=
      '<i class="mdi mdi-account-multiple-plus-outline" style="font-size: 20px;"></i>';
    detailsHtml +=
      '<span style="display: flex">' +
      window.lodash.escape(strings.add || 'Add') +
      '</span>';
    detailsHtml += '</a>';
    detailsHtml += '</div>';

    modalContent.html(detailsHtml);
  }

  function displayAddChildModal(postType, postId, postName) {
    // Close details modal if open
    const detailsModal = jQuery('#group-genmap-details-modal');
    if (detailsModal.length && detailsModal.hasClass('is-open')) {
      detailsModal.foundation('close');
    }

    const modalStrings = window.dtGroupGenmap?.strings?.modal || {};
    const listHtml = `
      <input id="group_genmap_add_child_post_type" type="hidden" value="${window.lodash.escape(
        postType,
      )}" />
      <input id="group_genmap_add_child_post_id" type="hidden" value="${window.lodash.escape(
        postId,
      )}" />
      <label>
        ${window.lodash.escape(modalStrings.add_child_name_title || 'Name')}
        <input id="group_genmap_add_child_name" type="text" />
      </label>`;

    const buttonsHtml = `<button id="group_genmap_add_child_but" class="button" type="button">${window.lodash.escape(
      modalStrings.add_child_but || 'Add Child',
    )}</button>`;

    const modal = jQuery('#template_metrics_modal');
    const modalButtons = jQuery('#template_metrics_modal_buttons');
    const title =
      (modalStrings.add_child_title || 'Add Child To') +
      ` [ ${window.lodash.escape(postName)} ]`;
    const content = jQuery('#template_metrics_modal_content');

    if (!modal.length) {
      console.error('Template metrics modal not found');
      return;
    }

    jQuery(modalButtons).empty().html(buttonsHtml);

    jQuery('#template_metrics_modal_title')
      .empty()
      .html(window.lodash.escape(title));
    jQuery(content).css('max-height', '300px');
    jQuery(content).css('overflow', 'auto');
    jQuery(content).empty().html(listHtml);
    jQuery(modal).foundation('open');
  }

  function handleAddChild() {
    const postType = jQuery('#group_genmap_add_child_post_type').val();
    const parentId = jQuery('#group_genmap_add_child_post_id').val();
    const childTitle = jQuery('#group_genmap_add_child_name').val();

    if (!postType || !parentId || !childTitle) {
      return;
    }

    if (window.API && window.API.create_post) {
      window.API.create_post(postType, {
        title: childTitle,
        additional_meta: {
          created_from: parentId,
          add_connection: 'child_groups',
        },
      })
        .then((newPost) => {
          jQuery('#template_metrics_modal').foundation('close');
          // Refresh the page to show the new child
          window.location.reload();
        })
        .catch(function (error) {
          console.error(error);
          alert(
            'Error creating child group: ' + (error.message || 'Unknown error'),
          );
        });
    } else {
      console.error('window.API.create_post is not available');
    }
  }

  // Event handlers for details buttons
  jQuery(document).on('click', '.genmap-details-open', function (e) {
    // Link will handle navigation naturally, but we ensure it opens in new tab
    // (already handled by target="_blank" in renderGenmapDetails)
  });

  jQuery(document).on('click', '.genmap-details-add-child', function (e) {
    e.preventDefault();
    const control = jQuery(e.currentTarget);
    displayAddChildModal(
      control.data('post_type'),
      control.data('post_id'),
      control.data('post_name'),
    );
  });

  // Clear node selection when details modal closes
  jQuery(document).on(
    'closed.zf.reveal',
    '#group-genmap-details-modal',
    function () {
      const wrapper = jQuery(TILE_SELECTOR);
      const container = wrapper.find('.group-genmap-chart');
      container.find('.node').removeClass('group-genmap-node-selected');
    },
  );

  jQuery(document).on('click', '#group_genmap_add_child_but', function (e) {
    e.preventDefault();
    handleAddChild();
  });

  jQuery(document).on(
    'open.zf.reveal',
    '#template_metrics_modal[data-reveal]',
    function () {
      jQuery('#group_genmap_add_child_name').focus();
    },
  );

  // Maximize button handler - opens genmap in fullscreen modal
  jQuery(document).on('click', '#group-genmap-maximize', function (e) {
    e.preventDefault();
    e.stopPropagation();
    openGenmapFullModal();
  });

  // Layout toggle handler - toggles tile container size and layout
  jQuery(document).on('click', '#group-genmap-layout-toggle', function () {
    const wrapper = jQuery(TILE_SELECTOR);
    if (!wrapper.length) {
      return;
    }

    // Check if D3 visualization is active
    const useD3Visualization = wrapper.data('useD3Visualization');

    if (useD3Visualization && typeof d3 !== 'undefined') {
      // D3 visualization: Toggle tile container size and re-render with new height
      const currentLayout = wrapper.data('currentLayout') || getDefaultLayout();
      const newLayout = currentLayout === 'l2r' ? 't2b' : 'l2r';

      wrapper.data('currentLayout', newLayout);
      saveLayoutPreference(newLayout);
      updateLayoutToggleIcon(newLayout);
      updateSectionWidth(newLayout);

      // Re-render D3 chart with new layout orientation
      const genmapData = wrapper.data('currentGenmapData');
      if (genmapData) {
        // Small delay to ensure CSS changes are applied before re-rendering
        setTimeout(() => {
          renderD3Chart(wrapper, genmapData, newLayout);
        }, 100);
      }
    } else {
      // Legacy orgchart: Toggle layout and tile container size
      const currentLayout = wrapper.data('currentLayout') || getDefaultLayout();
      const newLayout = currentLayout === 'l2r' ? 't2b' : 'l2r';
      switchLayout(wrapper, newLayout);
    }
  });
})(jQuery);

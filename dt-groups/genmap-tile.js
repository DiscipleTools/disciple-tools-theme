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
    // Default to horizontal for desktop
    return 'l2r';
  }

  function saveLayoutPreference(layout) {
    localStorage.setItem(LAYOUT_STORAGE_KEY, layout);
  }

  function isMobileView() {
    return window.innerWidth < 768;
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
          .group-genmap-layout-toggle {
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

    // Show toggle only on desktop
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
        renderChart(wrapper, sanitizedGenmap, currentLayout);
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

    const archivedKey = window.dtGroupGenmap?.statusField?.archived_key || '';
    const colors = window.dtGroupGenmap?.statusField?.colors || {};
    if (sanitized.status && colors[sanitized.status]) {
      sanitized.statusColor = colors[sanitized.status];
    } else if (
      sanitized.status &&
      archivedKey &&
      sanitized.status === archivedKey
    ) {
      sanitized.statusColor = '#808080';
    }

    return sanitized;
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
      // Currently vertical, show icon to switch to horizontal
      switchToVerticalIcon.hide();
      switchToHorizontalIcon.show();
    }
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

  // Layout toggle handler
  jQuery(document).on('click', '#group-genmap-layout-toggle', function () {
    const wrapper = jQuery(TILE_SELECTOR);
    if (!wrapper.length) {
      return;
    }
    const currentLayout = wrapper.data('currentLayout') || getDefaultLayout();
    const newLayout = currentLayout === 'l2r' ? 't2b' : 'l2r';
    switchLayout(wrapper, newLayout);
  });
})(jQuery);

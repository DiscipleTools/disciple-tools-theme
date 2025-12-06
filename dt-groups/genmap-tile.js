/**
 * Generational Map Tile for Group Records
 * Provides a compact ancestry view and modal-based full tree visualization
 */
jQuery(document).ready(function ($) {
  'use strict';

  const config = window.dtGroupsGenmap || {};
  const postId = config.post_id;
  const translations = config.translations || {};
  const statusColors = config.status_colors || {};

  let orgchartInstance = null;
  let currentZoom = 1;
  let isVerticalLayout = false;

  // Initialize modal
  const $modal = $('#genmap-modal');

  // View Tree button click handler
  $(document).on('click', '.genmap-view-tree-btn', function () {
    const id = $(this).data('post-id') || postId;
    openGenMapModal(id);
  });

  // Zoom controls
  $(document).on('click', '.genmap-zoom-in', function () {
    zoomTree(0.1);
  });

  $(document).on('click', '.genmap-zoom-out', function () {
    zoomTree(-0.1);
  });

  $(document).on('click', '.genmap-zoom-reset', function () {
    resetZoom();
  });

  // Layout toggle
  $(document).on('change', '#genmap-layout-toggle', function () {
    isVerticalLayout = $(this).is(':checked');
    localStorage.setItem('genmap_vertical_layout', isVerticalLayout);
    if (orgchartInstance) {
      reloadTree(postId);
    }
  });

  // Initialize layout preference from localStorage
  const savedLayout = localStorage.getItem('genmap_vertical_layout');
  if (savedLayout === 'true') {
    isVerticalLayout = true;
    $('#genmap-layout-toggle').prop('checked', true);
  }

  /**
   * Open the generational map modal and load tree data
   */
  function openGenMapModal(focusId) {
    $modal.foundation('open');
    showLoading();
    loadGenMapData(focusId);
  }

  /**
   * Show loading spinner
   */
  function showLoading() {
    $('#genmap-tree-container').html(`
      <div class="genmap-loading">
        <span class="loading-spinner active"></span>
        <p>${window.SHAREDFUNCTIONS.escapeHTML(translations.loading)}</p>
      </div>
    `);
    $('#genmap-details-panel').empty();
  }

  /**
   * Load genmap data from the API
   */
  function loadGenMapData(focusId) {
    const payload = {
      p2p_type: 'groups_to_groups',
      p2p_direction: 'from',
      post_type: 'groups',
      gen_depth_limit: 100,
      show_archived: false,
      data_layers: { color: 'default-node-color', layers: [] },
      slug: 'records',
      focus_id: focusId || 0,
    };

    window
      .makeRequest('POST', 'metrics/records/genmap', payload)
      .promise()
      .then((response) => {
        renderTree(response.genmap, focusId);
      })
      .catch((error) => {
        console.error('Error loading genmap data:', error);
        $('#genmap-tree-container').html(`
          <div class="genmap-error">
            <p>${window.SHAREDFUNCTIONS.escapeHTML(translations.no_data)}</p>
          </div>
        `);
      });
  }

  /**
   * Render the organizational chart tree
   */
  function renderTree(data, focusId) {
    const $container = $('#genmap-tree-container');
    $container.empty();

    if (!data || (typeof data === 'object' && Object.keys(data).length === 0)) {
      $container.html(`
        <div class="genmap-no-data">
          <p>${window.SHAREDFUNCTIONS.escapeHTML(translations.no_data)}</p>
        </div>
      `);
      return;
    }

    // Create a wrapper for zoom/pan functionality
    $container.html('<div id="genmap-chart"></div>');

    // Custom node template with compact design
    const nodeTemplate = function (nodeData) {
      const name = nodeData.shared === 1 ? nodeData.name : '......';
      const isFocused = String(nodeData.id) === String(focusId);
      const focusClass = isFocused ? 'node-focused' : '';

      return `
        <div class="title ${focusClass}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(String(nodeData.id))}">
          ${window.SHAREDFUNCTIONS.escapeHTML(name)}
        </div>
        <div class="content ${focusClass}">
          ${window.SHAREDFUNCTIONS.escapeHTML(nodeData.content)}
        </div>
      `;
    };

    // Determine direction based on layout preference
    const direction = isVerticalLayout ? 't2b' : 'l2r';

    try {
      orgchartInstance = $('#genmap-chart').orgchart({
        data: data,
        nodeContent: 'content',
        nodeTitle: 'name',
        direction: direction,
        pan: true,
        zoom: true,
        zoominLimit: 3,
        zoomoutLimit: 0.3,
        nodeTemplate: nodeTemplate,
        createNode: function ($node, nodeData) {
          // Apply status color if available
          if (nodeData.status && statusColors[nodeData.status]) {
            $node.css('background-color', statusColors[nodeData.status]);
            $node.find('.title').css('background-color', statusColors[nodeData.status]);
          }

          // Mark non-shared nodes
          if (nodeData.shared !== 1) {
            $node.addClass('node-hidden');
            $node.css('background-color', '#808080');
            $node.find('.title').css('background-color', '#808080');
          }

          // Highlight focused node
          if (String(nodeData.id) === String(focusId)) {
            $node.addClass('node-focused');
          }

          // Make nodes smaller for better overview
          if (direction === 'l2r') {
            $node.css({
              width: '100px',
              height: '100px',
            });
          } else {
            $node.css({
              width: '100px',
            });
          }
        },
      });

      // Node click handler
      $container.off('click', '.node');
      $container.on('click', '.node', function () {
        const $node = $(this);
        const nodeId = $node.attr('id');
        const isShared = !$node.hasClass('node-hidden');

        if (isShared && nodeId) {
          showNodeDetails(nodeId);
        }
      });

      // Reset zoom on initial load
      currentZoom = 1;
    } catch (error) {
      console.error('Error rendering orgchart:', error);
      $container.html(`
        <div class="genmap-error">
          <p>${window.SHAREDFUNCTIONS.escapeHTML(translations.no_data)}</p>
        </div>
      `);
    }
  }

  /**
   * Reload tree with current settings
   */
  function reloadTree(focusId) {
    showLoading();
    loadGenMapData(focusId);
  }

  /**
   * Zoom the tree
   */
  function zoomTree(delta) {
    const $chart = $('#genmap-chart .orgchart');
    if ($chart.length === 0) return;

    currentZoom = Math.max(0.3, Math.min(3, currentZoom + delta));

    const currentTransform = $chart.css('transform');
    let baseTransform = '';

    // Preserve base rotation for l2r layout
    if (!isVerticalLayout) {
      baseTransform = 'rotate(-90deg) rotateY(180deg)';
    }

    $chart.css('transform', `${baseTransform} scale(${currentZoom})`);
  }

  /**
   * Reset zoom to default
   */
  function resetZoom() {
    const $chart = $('#genmap-chart .orgchart');
    if ($chart.length === 0) return;

    currentZoom = 1;

    if (!isVerticalLayout) {
      $chart.css('transform', 'rotate(-90deg) rotateY(180deg) scale(1)');
    } else {
      $chart.css('transform', 'scale(1)');
    }
  }

  /**
   * Show details panel for a selected node
   */
  function showNodeDetails(nodeId) {
    const $panel = $('#genmap-details-panel');
    $panel.html('<span class="loading-spinner active"></span>');

    window
      .makeRequest('GET', `groups/${nodeId}`, null, 'dt-posts/v2/')
      .promise()
      .then((data) => {
        const status = data.group_status?.label || '';
        const type = data.group_type?.label || '';
        const memberCount = data.member_count || 0;

        $panel.html(`
          <div class="genmap-details-content">
            <h4>${window.SHAREDFUNCTIONS.escapeHTML(data.title)}</h4>
            <div class="genmap-details-grid">
              <div class="genmap-detail-item">
                <span class="detail-label">${window.SHAREDFUNCTIONS.escapeHTML(translations.status)}:</span>
                <span class="detail-value">${window.SHAREDFUNCTIONS.escapeHTML(status)}</span>
              </div>
              <div class="genmap-detail-item">
                <span class="detail-label">${window.SHAREDFUNCTIONS.escapeHTML(translations.type)}:</span>
                <span class="detail-value">${window.SHAREDFUNCTIONS.escapeHTML(type)}</span>
              </div>
              <div class="genmap-detail-item">
                <span class="detail-label">${window.SHAREDFUNCTIONS.escapeHTML(translations.members)}:</span>
                <span class="detail-value">${window.SHAREDFUNCTIONS.escapeHTML(String(memberCount))}</span>
              </div>
            </div>
            <div class="genmap-details-actions">
              <a href="${window.SHAREDFUNCTIONS.escapeHTML(config.site_url)}/groups/${window.SHAREDFUNCTIONS.escapeHTML(String(data.ID))}" class="button small" target="_blank">
                <i class="mdi mdi-open-in-new"></i>
                ${window.SHAREDFUNCTIONS.escapeHTML(translations.view_record)}
              </a>
            </div>
          </div>
        `);
      })
      .catch((error) => {
        console.error('Error loading node details:', error);
        $panel.empty();
      });
  }
});

(function ($) {
  'use strict';

  const TILE_SELECTOR = '#group-genmap-tile';
  const ENDPOINT = 'metrics/personal/genmap';
  const MAX_CANVAS_HEIGHT = 320;
  const MIN_CANVAS_HEIGHT = 220;
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

  jQuery(document).ready(() => {
    // Add CSS for selected node border
    if (!document.getElementById('group-genmap-tile-styles')) {
      const style = document.createElement('style');
      style.id = 'group-genmap-tile-styles';
      style.textContent = `
        .group-genmap-chart .node.group-genmap-node-selected {
          border: 2px dashed rgba(238, 217, 54, 0.8) !important;
          box-shadow: 0 0 0 2px rgba(238, 217, 54, 0.3);
        }
      `;
      document.head.appendChild(style);
    }

    const wrapper = $(TILE_SELECTOR);
    if (!wrapper.length) {
      return;
    }

    const postId = parseInt(wrapper.data('postId'), 10);
    if (!postId) {
      setMessage(wrapper, 'empty');
      return;
    }

    fetchGenmap(wrapper, postId);
  });

  function fetchGenmap(wrapper, focusId) {
    setMessage(wrapper, 'loading');
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
        renderChart(wrapper, sanitizeNode(genmap));
      })
      .fail(() => {
        setMessage(wrapper, 'error');
      })
      .always(() => {
        wrapper.addClass('group-genmap-loaded');
      });
  }

  function renderChart(wrapper, genmap) {
    if (!genmap) {
      setMessage(wrapper, 'empty');
      return;
    }

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
        <div class="title" data-item-id="${window.lodash.escape(data.id)}">${window.lodash.escape(data.name || '')}</div>
        <div class="content" style="padding-left: 5px; padding-right: 5px;">${window.lodash.escape(data.content || '')}</div>
      `;
    };

    const orgchart = container.orgchart({
      data: genmap,
      nodeContent: 'content',
      direction: 'l2r',
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

  function openGenmapDetails(wrapper, nodeId, parentId, postType) {
    const detailsContainer = wrapper.find('#group-genmap-details');
    if (!detailsContainer.length) {
      return;
    }

    const spinner = ' <span class="loading-spinner active"></span> ';
    detailsContainer.html(spinner).show();

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
          renderGenmapDetails(detailsContainer, parentId, postType, data);
        } else {
          detailsContainer.empty().hide();
        }
      })
      .fail(() => {
        detailsContainer.html('<p>' + getString('error') + '</p>').show();
      });
  }

  function renderGenmapDetails(container, parentId, postType, data) {
    const strings = window.dtGroupGenmap?.strings?.details || {};
    const urlBase = (window.dtGroupGenmap?.recordUrlBase || '').replace(
      /\/$/,
      '',
    );
    const postTypeSlug = window.dtGroupGenmap?.postType || 'groups';

    // Build details line (compact) with close button
    let detailsHtml =
      '<div class="group-genmap-details-info" style="display: flex; flex-wrap: wrap; gap: 15px; padding: 10px 0; border-top: 1px solid #e0e0e0; margin-top: 10px; position: relative;">';
    detailsHtml +=
      '<strong>' + window.lodash.escape(data.title || '') + '</strong>';

    if (data.group_status && data.group_status.label) {
      detailsHtml +=
        '<span>Status: ' +
        window.lodash.escape(data.group_status.label) +
        '</span>';
    }
    if (data.group_type && data.group_type.label) {
      detailsHtml +=
        '<span>Type: ' +
        window.lodash.escape(data.group_type.label) +
        '</span>';
    }
    if (data.member_count !== undefined) {
      detailsHtml +=
        '<span>Members: ' + window.lodash.escape(data.member_count) + '</span>';
    }
    if (data.assigned_to && data.assigned_to.display) {
      detailsHtml +=
        '<span>Assigned: ' +
        window.lodash.escape(data.assigned_to.display) +
        '</span>';
    }
    // Add close button
    detailsHtml +=
      '<button type="button" class="group-genmap-details-close button clear" style="position: absolute; top: 5px; right: 5px; padding: 5px 10px; margin: 0;" aria-label="Close details">';
    detailsHtml += '<span aria-hidden="true">&times;</span>';
    detailsHtml += '</button>';
    detailsHtml += '</div>';

    // Build buttons line
    const openUrl = `${urlBase}/${postTypeSlug}/${encodeURIComponent(data.ID)}`;
    detailsHtml +=
      '<div class="group-genmap-details-actions" style="display: flex; gap: 10px; padding: 10px 0;">';
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

    container.html(detailsHtml);
  }

  function displayAddChildModal(postType, postId, postName) {
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

  function closeGenmapDetails(wrapper) {
    const detailsContainer = wrapper.find('#group-genmap-details');
    detailsContainer.empty().hide();

    // Remove selection from all nodes
    const container = wrapper.find('.group-genmap-chart');
    container.find('.node').removeClass('group-genmap-node-selected');
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

  jQuery(document).on('click', '.group-genmap-details-close', function (e) {
    e.preventDefault();
    const wrapper = jQuery(TILE_SELECTOR);
    closeGenmapDetails(wrapper);
  });

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
})(jQuery);

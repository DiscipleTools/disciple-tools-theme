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

    const orgchart = container.orgchart({
      data: genmap,
      nodeContent: 'content',
      direction: 'l2r',
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

    // Bind click handler for drill-down navigation.
    container.off('click', '.node');
    container.on('click', '.node', function () {
      const node = $(this);
      if (String(node.data('shared')) === '0') {
        return;
      }
      const nodeId = node.attr('id');
      if (!nodeId) {
        return;
      }
      const urlBase = (window.dtGroupGenmap?.recordUrlBase || '').replace(
        /\/$/,
        '',
      );
      const postType = window.dtGroupGenmap?.postType || 'groups';
      const targetUrl = `${urlBase}/${postType}/${encodeURIComponent(nodeId)}`;
      window.open(targetUrl, '_blank', 'noopener');
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
})(jQuery);

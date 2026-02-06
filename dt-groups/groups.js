'use strict';
jQuery(document).ready(function ($) {
  let post_id = window.detailsSettings.post_id;
  let post_type = window.detailsSettings.post_type;
  let post = window.detailsSettings.post_fields;

  let { template_dir } = window.wpApiShare;

  /* Member List*/
  let memberList = $('.member-list');
  let memberCountInput = $('#member_count');
  let leaderCountInput = $('#leader_count');
  let populateMembersList = () => {
    memberList.empty();

    post.members.forEach((m) => {
      if (window.lodash.find(post.leaders || [], { ID: m.ID })) {
        m.leader = true;
      }
    });
    post.members = window.lodash.sortBy(post.members, [
      'leader',
      (member) => member.post_title.toLowerCase(),
    ]);
    post.members.forEach((member) => {
      let leaderHTML = '';
      let leaderStatus = 'not-leader';
      let leaderStyle = '';
      if (member.leader) {
        leaderStatus = 'leader';
        leaderStyle = 'color:black;';
      }

      const contactStatusHTML =
        member.data && member.data.overall_status
          ? `<i class="fi-torso small" style="color: ${window.SHAREDFUNCTIONS.escapeHTML(member.data.overall_status.color)}" title="${window.SHAREDFUNCTIONS.escapeHTML(member.data.overall_status.label)}"></i>`
          : '<i class="fi-torso small"></i>';

      const milestonesHTML = member.data.milestones.reduce(
        (htmlString, milestone) => {
          return milestone.icon
            ? htmlString +
                `<img class="dt-icon" src="${window.SHAREDFUNCTIONS.escapeHTML(milestone.icon)}" alt="${window.SHAREDFUNCTIONS.escapeHTML(milestone.label)}" title="${window.SHAREDFUNCTIONS.escapeHTML(milestone.label)}">`
            : htmlString;
        },
        '',
      );
      let memberHTML = `<div class="member-row" style="" data-id="${window.SHAREDFUNCTIONS.escapeHTML(member.ID)}">
          <div style="flex-grow: 1" class="member-status">
              ${contactStatusHTML}
              <a href="${window.SHAREDFUNCTIONS.escapeHTML(window.wpApiShare.site_url)}/contacts/${window.SHAREDFUNCTIONS.escapeHTML(member.ID)}">${window.SHAREDFUNCTIONS.escapeHTML(member.post_title)}</a>
              ${leaderHTML}
              ${milestonesHTML}
          </div>
          <button class="button clear make-leader member-row-actions ${leaderStatus}" style="${leaderStyle}" data-id="${window.SHAREDFUNCTIONS.escapeHTML(member.ID)}">
            <i class="fi-foot small"></i>
          </button>
          <button class="button clear delete-member member-row-actions" data-id="${window.SHAREDFUNCTIONS.escapeHTML(member.ID)}">
            <i class="fi-x small"></i>
          </button>
        </div>`;
      memberList.append(memberHTML);
    });
    if (post.members.length === 0) {
      $('#empty-members-list-message').show();
    } else {
      $('#empty-members-list-message').hide();
    }
    memberCountInput.val(post.member_count);
    leaderCountInput.val(post.leader_count);
    window.masonGrid.masonry('layout');
    document.dispatchEvent(
      new CustomEvent('dt-member-list-populated', { detail: post }),
    );
  };
  populateMembersList();

  $(document).on(
    'dt-post-connection-created',
    function (e, new_post, field_key) {
      if (field_key === 'members') {
        post = new_post;
        populateMembersList();
      }
    },
  );
  $(document).on('click', '.delete-member', function () {
    let id = $(this).data('id');
    $(`.member-row[data-id="${id}"]`).remove();
    window.API.update_post(post_type, post_id, {
      members: { values: [{ value: id, delete: true }] },
    }).then((groupRes) => {
      post = groupRes;
      populateMembersList();
      window.masonGrid.masonry('layout');
    });
    if (window.lodash.find(post.leaders || [], { ID: id })) {
      window.API.update_post(post_type, post_id, {
        leaders: { values: [{ value: id, delete: true }] },
      });
    }
  });
  $(document).on('click', '.make-leader', function () {
    $(this).children('i').attr('class', 'small');
    let spinner = `<img src="${template_dir}/dt-assets/images/ajax-loader.gif" width="15px">`;
    $(this).append(spinner);
    let id = $(this).data('id');
    let remove = false;
    let existingLeaderIcon = $(`.member-row[data-id="${id}"] .leader`);
    if (
      window.lodash.find(post.leaders || [], { ID: id }) ||
      existingLeaderIcon.length !== 0
    ) {
      remove = true;
    }
    window.API.update_post(post_type, post_id, {
      leaders: { values: [{ value: id, delete: remove }] },
    }).then((groupRes) => {
      post = groupRes;
      populateMembersList();
      window.masonGrid.masonry('layout');
    });
  });
  $('.add-new-member').on('click', function () {
    window.DTFoundation.plugin(() => {
      window.DTFoundation.callMethod('#add-new-group-member-modal', 'open');
    });
    window.Typeahead[`.js-typeahead-members`].adjustInputSize();
  });
  $(document).on('dt-post-connection-added', function (e, new_post, field_key) {
    post = new_post;
    if (field_key === 'members') {
      populateMembersList();
    }
  });

  /* end Member List */

  /* Four Fields */
  let loadFourFields = () => {
    if ($('#four-fields').length) {
      $('#four_fields_unbelievers').val(post.four_fields_unbelievers);
      $('#four_fields_believers').val(post.four_fields_believers);
      $('#four_fields_accountable').val(post.four_fields_accountable);
      $('#four_fields_church_commitment').val(
        post.four_fields_church_commitment,
      );
      $('#four_fields_multiplying').val(post.four_fields_multiplying);
    }
  };

  let ffInputs = `
    <label style="margin-left:33.3%;">
        <span></span>${window.SHAREDFUNCTIONS.escapeHTML(window.detailsSettings.post_settings.fields.four_fields_unbelievers.name)}
        <input class="four_fields" style="width: 60%;height: 25%;border: 1px solid #000;text-align: center;font-size: 24px;" type="text" name="four_fields_unbelievers" id="four_fields_unbelievers">
    </label>
    <div></div>
    <label style="margin-right:33.3%;">
        ${window.SHAREDFUNCTIONS.escapeHTML(window.detailsSettings.post_settings.fields.four_fields_believers.name)}
        <input class="four_fields" style="width: 60%;height: 25%;border: 1px solid #000;text-align: center;font-size: 24px;" type="text" name="four_fields_believers" id="four_fields_believers">
    </label>
    <div></div>
    <label style="text-align: center">
        ${window.SHAREDFUNCTIONS.escapeHTML(window.detailsSettings.post_settings.fields.four_fields_multiplying.name)}
        <input class="four_fields" style="width: 60%;height: 25%;border: 1px solid #000;text-align: center;font-size: 24px;margin:auto" type="text" name="four_fields_multiplying" id="four_fields_multiplying">
    </label>
    <div></div>
    <label style="margin-left:33.3%;">
        <input class="four_fields" style="width: 60%;height: 25%;border: 1px solid #000;text-align: center;font-size: 24px;margin-bottom:0" type="text" name="four_fields_accountable" id="four_fields_accountable">
        ${window.SHAREDFUNCTIONS.escapeHTML(window.detailsSettings.post_settings.fields.four_fields_accountable.name)}
    </label>
    <div></div>
    <label style="margin-right:33.3%;">
        <input class="four_fields" style="width: 60%;height: 25%;border: 1px solid #000;text-align: center;font-size: 24px;margin-bottom:0" type="text" name="four_fields_church_commitment" id="four_fields_church_commitment">
        ${window.SHAREDFUNCTIONS.escapeHTML(window.detailsSettings.post_settings.fields.four_fields_church_commitment.name)}
    </label>
  `;
  $('#four-fields-inputs').append(ffInputs);
  loadFourFields();

  $('input.four_fields').on('blur', function () {
    const id = $(this).attr('id');
    const val = $(this).val();

    window.API.update_post(post_type, post_id, { [id]: val })
      .then((resp) => {
        $(document).trigger('text-input-updated', [resp, id, val]);
      })
      .catch(window.handleAjaxError);
  });
  /* End Four Fields */

  //update the end date input when group is closed.
  $(document).on(
    'select-field-updated',
    function (e, new_group, field_key, val) {
      if (field_key === 'group_status' && new_group.end_date) {
        $('#end_date').val(
          window.SHAREDFUNCTIONS.formatDate(new_group.end_date.timestamp),
        );
      }
    },
  );
});
